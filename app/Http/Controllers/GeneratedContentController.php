<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGeneratedRppRequest;
use App\Jobs\GenerateRppDraft;
use App\Models\GeneratedContent;
use App\Models\TeachingProject;
use App\Services\Workflow\StaleProcessRecovery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GeneratedContentController extends Controller
{
    public function edit(TeachingProject $teachingProject, GeneratedContent $generatedContent): View
    {
        $this->authorizeDraft($teachingProject, $generatedContent);

        $teachingProject->load(['classProfile', 'documents']);
        $generatedContent->load([
            'versions' => fn ($query) => $query->with('creator')->orderByDesc('version_number'),
        ]);

        return view('teaching-projects.rpp-edit', [
            'teachingProject' => $teachingProject,
            'draft' => $generatedContent,
        ]);
    }

    public function update(UpdateGeneratedRppRequest $request, TeachingProject $teachingProject, GeneratedContent $generatedContent): RedirectResponse
    {
        $this->authorizeDraft($teachingProject, $generatedContent);

        $data = $request->validated();
        $payload = [
            'identity' => [
                'subject' => $data['identity_subject'],
                'grade' => $data['identity_grade'],
                'topic' => $data['identity_topic'],
            ],
            'learning_objectives' => $this->lines($data['learning_objectives']),
            'learning_activities' => [
                'opening' => $this->lines($data['opening']),
                'core' => $this->lines($data['core']),
                'closing' => $this->lines($data['closing']),
            ],
            'assessment' => [
                'diagnostic' => $this->lines($data['diagnostic'] ?? ''),
                'formative' => $this->lines($data['formative'] ?? ''),
                'summative' => $this->lines($data['summative'] ?? ''),
            ],
            'differentiation' => $this->lines($data['differentiation'] ?? ''),
            'remedial' => $this->lines($data['remedial'] ?? ''),
            'enrichment' => $this->lines($data['enrichment'] ?? ''),
            'references' => $this->lines($data['references'] ?? ''),
        ];

        DB::transaction(function () use ($generatedContent, $payload, $request): void {
            $nextVersion = ((int) $generatedContent->versions()->max('version_number')) + 1;

            $generatedContent->update([
                'status' => 'ready',
                'output_payload' => $payload,
                'error_message' => null,
            ]);

            $generatedContent->versions()->create([
                'created_by' => $request->user()->id,
                'version_number' => $nextVersion,
                'payload' => $payload,
                'change_summary' => 'Revisi manual guru',
            ]);
        });

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=rpp')
            ->with('success', 'Draft RPP versi baru berhasil disimpan.');
    }

    public function recover(
        TeachingProject $teachingProject,
        GeneratedContent $generatedContent,
        StaleProcessRecovery $recovery,
    ): RedirectResponse {
        abort_unless($generatedContent->teaching_project_id === $teachingProject->id, 404);
        Gate::authorize('view', $teachingProject);
        abort_unless($generatedContent->content_type === 'rpp', 404);
        abort_unless($teachingProject->latestRppDraft()?->id === $generatedContent->id, 404);

        $recovered = $recovery->recoverGeneratedContent($generatedContent);

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=rpp')
            ->with(
                $recovered instanceof GeneratedContent ? 'success' : 'info',
                $recovered instanceof GeneratedContent
                    ? 'Proses RPP yang macet dijadwalkan ulang.'
                    : 'Proses RPP belum melewati batas waktu pemulihan.',
            );
    }

    public function retry(TeachingProject $teachingProject, GeneratedContent $generatedContent): RedirectResponse
    {
        abort_unless($generatedContent->teaching_project_id === $teachingProject->id, 404);
        Gate::authorize('view', $teachingProject);
        abort_unless($generatedContent->content_type === 'rpp', 404);
        abort_unless($teachingProject->latestRppDraft()?->id === $generatedContent->id, 404);

        $retried = DB::transaction(function () use ($generatedContent): ?GeneratedContent {
            $content = GeneratedContent::query()->lockForUpdate()->find($generatedContent->id);

            if (! $content instanceof GeneratedContent || $content->getRawOriginal('status') !== 'failed') {
                return null;
            }

            $content->update([
                'status' => 'queued',
                'output_payload' => null,
                'error_message' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);

            return $content->fresh();
        });

        if (! $retried instanceof GeneratedContent) {
            return redirect()->to(route('teaching-projects.show', $teachingProject).'?tab=rpp')
                ->with('info', 'Draft RPP tidak berada pada status gagal sehingga belum perlu dicoba ulang.');
        }

        GenerateRppDraft::dispatch($retried);

        return redirect()->to(route('teaching-projects.show', $teachingProject).'?tab=rpp')
            ->with('success', 'Draft RPP dijadwalkan ulang.');
    }

    private function authorizeDraft(TeachingProject $project, GeneratedContent $draft): void
    {
        abort_unless($draft->teaching_project_id === $project->id, 404);
        Gate::authorize('view', $project);
        abort_unless($draft->content_type === 'rpp' && $draft->status === 'ready', 404);
        abort_unless($project->latestRppDraft()?->id === $draft->id, 404);
    }

    /** @return list<string> */
    private function lines(?string $value): array
    {
        $lines = preg_split('/\R/u', trim((string) $value)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }
}
