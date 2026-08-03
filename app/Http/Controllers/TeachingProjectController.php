<?php

namespace App\Http\Controllers;

use App\Enums\DocumentProcessingStatus;
use App\Enums\TeachingProjectStatus;
use App\Http\Requests\StoreTeachingProjectRequest;
use App\Http\Requests\UpdateTeachingProjectRequest;
use App\Jobs\GenerateRppDraft;
use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use App\Services\AI\RppGenerationContextBuilder;
use App\Services\Workflow\StaleProcessRecovery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeachingProjectController extends Controller
{
    public function index(Request $request): View
    {
        return view('teaching-projects.index', [
            'teachingProjects' => $this->user($request)
                ->teachingProjects()
                ->with('classProfile')
                ->latest('updated_at')
                ->paginate(12),
        ]);
    }

    public function create(Request $request): View
    {
        return view('teaching-projects.create', [
            'classProfiles' => $this->user($request)->classProfiles()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTeachingProjectRequest $request): RedirectResponse
    {
        $teachingProject = $this->user($request)->teachingProjects()->create([
            ...$request->validated(),
            'status' => TeachingProjectStatus::Draft,
        ]);

        return redirect()
            ->route('teaching-projects.show', $teachingProject)
            ->with('success', 'Proyek pembelajaran berhasil dibuat.');
    }

    public function show(Request $request, TeachingProject $teachingProject, StaleProcessRecovery $recovery): View
    {
        Gate::authorize('view', $teachingProject);

        $teachingProject->load(['classProfile', 'documents']);
        $latestRppDraft = $teachingProject->latestRppDraft();
        $latestRppVersion = $latestRppDraft?->versions()->latest('version_number')->first();
        $alignmentFindings = $latestRppVersion === null
            ? collect()
            : $latestRppVersion->alignmentFindings()->latest('id')->get();
        $alignmentWarningCount = $alignmentFindings->where('severity', 'warning')->count();
        $alignmentSummary = match (true) {
            $latestRppVersion === null => ['label' => 'Belum diperiksa', 'classes' => 'bg-slate-100 text-slate-700', 'description' => 'Jalankan alignment setelah draft RPP siap.'],
            $alignmentWarningCount > 0 => ['label' => 'Perlu perhatian', 'classes' => 'bg-amber-50 text-amber-700', 'description' => $alignmentWarningCount.' temuan perlu diperiksa guru.'],
            $alignmentFindings->isNotEmpty() => ['label' => 'Struktur selaras', 'classes' => 'bg-emerald-50 text-emerald-700', 'description' => 'Pemeriksaan utama tidak menemukan temuan peringatan.'],
            default => ['label' => 'Belum diperiksa', 'classes' => 'bg-slate-100 text-slate-700', 'description' => 'Jalankan alignment untuk memeriksa versi ini.'],
        };
        $latestApproval = $latestRppVersion?->approval()->with('approver')->first();
        $artifactDrafts = $teachingProject->generatedContents()
            ->where('content_type', 'lkpd')
            ->latest('id')
            ->get()
            ->groupBy('content_type')
            ->map(static fn ($items) => $items->first());
        $tabs = [
            'overview' => ['label' => 'Ringkasan', 'available' => true],
            'rpp' => ['label' => 'RPP/Modul', 'available' => true],
            'sources' => ['label' => 'Sumber', 'available' => true],
            'artifacts' => ['label' => 'Perangkat', 'available' => true],
            'alignment' => ['label' => 'Keselarasan', 'available' => true],
            'approval' => ['label' => 'Tinjau & Ekspor', 'available' => true],
        ];
        $activeTab = (string) $request->query('tab', 'overview');

        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = 'overview';
        }

        $documents = $teachingProject->documents;
        $readyDocuments = $documents->filter(
            fn (ProjectDocument $document): bool => $document->getRawOriginal('processing_status') === DocumentProcessingStatus::Ready->value
        )->values();
        $processingDocumentCount = $documents->filter(
            fn (ProjectDocument $document): bool => in_array(
                $document->getRawOriginal('processing_status'),
                [DocumentProcessingStatus::Pending->value, DocumentProcessingStatus::Processing->value],
                true
            )
        )->count();
        $staleDocumentIds = $documents
            ->filter(fn (ProjectDocument $document): bool => $recovery->documentIsStale($document))
            ->pluck('id')
            ->all();
        $rppStale = $latestRppDraft !== null && $recovery->generatedContentIsStale($latestRppDraft);
        $latestArtifact = $artifactDrafts->get('lkpd');
        $artifactStale = $latestArtifact instanceof GeneratedContent
            && $recovery->generatedContentIsStale($latestArtifact);
        $rppState = match ($latestRppDraft?->status) {
            'ready' => ['label' => 'Siap ditinjau', 'classes' => 'bg-emerald-50 text-emerald-700', 'description' => 'Draft RPP dapat dibuka dan diedit.'],
            'failed' => ['label' => 'Gagal diproses', 'classes' => 'bg-red-50 text-red-700', 'description' => 'Periksa pesan kegagalan sebelum mencoba lagi.'],
            'processing' => ['label' => 'Sedang diproses', 'classes' => 'bg-amber-50 text-amber-700', 'description' => 'Queue worker sedang memproses draft.'],
            'queued' => ['label' => 'Menunggu antrean', 'classes' => 'bg-slate-100 text-slate-700', 'description' => 'Draft akan diproses oleh queue worker.'],
            default => ['label' => 'Belum dibuat', 'classes' => 'bg-slate-100 text-slate-700', 'description' => 'Generate draft RPP dari konteks proyek ini.'],
        };
        if ($rppStale) {
            $rppState = ['label' => 'Macet', 'classes' => 'bg-amber-100 text-amber-800', 'description' => 'Proses melewati batas waktu dan siap dipulihkan.'];
        }

        return view('teaching-projects.show', [
            'teachingProject' => $teachingProject,
            'latestRppDraft' => $latestRppDraft,
            'latestRppVersion' => $latestRppVersion,
            'latestApproval' => $latestApproval,
            'alignmentFindings' => $alignmentFindings,
            'alignmentSummary' => $alignmentSummary,
            'artifactDrafts' => $artifactDrafts,
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'readyDocuments' => $readyDocuments,
            'readyDocumentCount' => $readyDocuments->count(),
            'processingDocumentCount' => $processingDocumentCount,
            'staleDocumentIds' => $staleDocumentIds,
            'rppState' => $rppState,
            'rppStale' => $rppStale,
            'artifactStale' => $artifactStale,
            'rppGenerationActive' => in_array($latestRppDraft?->status, ['queued', 'processing'], true),
        ]);
    }

    public function generateRpp(TeachingProject $teachingProject, RppGenerationContextBuilder $contextBuilder): RedirectResponse
    {
        Gate::authorize('view', $teachingProject);

        $active = $teachingProject->generatedContents()
            ->where('content_type', 'rpp')
            ->whereIn('status', ['queued', 'processing'])
            ->exists();

        if ($active) {
            return back()->with('info', 'Draft RPP sedang diproses. Tunggu sampai statusnya selesai.');
        }

        $input = $contextBuilder->fromProject($teachingProject);
        $content = $teachingProject->user()->firstOrFail()->generatedContents()->create([
            'teaching_project_id' => $teachingProject->id,
            'content_type' => 'rpp',
            'status' => 'queued',
            'input_snapshot' => $input->toArray(),
            'provider' => config('ai.provider', 'gemini'),
            'model' => config('ai.gemini.model'),
        ]);

        GenerateRppDraft::dispatch($content);

        return back()->with('success', 'Draft RPP masuk antrean. Jalankan queue worker untuk memprosesnya.');
    }

    public function edit(Request $request, TeachingProject $teachingProject): View
    {
        Gate::authorize('update', $teachingProject);

        return view('teaching-projects.edit', [
            'teachingProject' => $teachingProject,
            'classProfiles' => $this->user($request)->classProfiles()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTeachingProjectRequest $request, TeachingProject $teachingProject): RedirectResponse
    {
        Gate::authorize('update', $teachingProject);

        $teachingProject->update($request->validated());

        return redirect()
            ->route('teaching-projects.show', $teachingProject)
            ->with('success', 'Proyek pembelajaran berhasil diperbarui.');
    }

    public function destroy(TeachingProject $teachingProject): RedirectResponse
    {
        Gate::authorize('delete', $teachingProject);
        $teachingProject->delete();

        return redirect()
            ->route('teaching-projects.index')
            ->with('success', 'Proyek pembelajaran berhasil dihapus.');
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
