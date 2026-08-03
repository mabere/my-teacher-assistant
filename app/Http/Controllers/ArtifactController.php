<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateArtifactRequest;
use App\Jobs\GenerateArtifactDraft;
use App\Models\GeneratedContent;
use App\Models\TeachingProject;
use App\Services\AI\RppGenerationContextBuilder;
use App\Services\Workflow\StaleProcessRecovery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ArtifactController extends Controller
{
    public function recover(
        TeachingProject $teachingProject,
        GeneratedContent $generatedContent,
        StaleProcessRecovery $recovery,
    ): RedirectResponse {
        Gate::authorize('view', $teachingProject);
        abort_unless($generatedContent->teaching_project_id === $teachingProject->id, 404);
        abort_unless($generatedContent->content_type === 'lkpd', 404);
        abort_unless(
            $teachingProject->generatedContents()
                ->where('content_type', 'lkpd')
                ->latest('id')
                ->value('id') === $generatedContent->id,
            404,
        );

        $recovered = $recovery->recoverGeneratedContent($generatedContent);

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=artifacts')
            ->with(
                $recovered instanceof GeneratedContent ? 'success' : 'info',
                $recovered instanceof GeneratedContent
                    ? 'Proses LKPD yang macet dijadwalkan ulang.'
                    : 'Proses LKPD belum melewati batas waktu pemulihan.',
            );
    }

    public function retry(TeachingProject $teachingProject, GeneratedContent $generatedContent): RedirectResponse
    {
        Gate::authorize('view', $teachingProject);
        abort_unless($generatedContent->teaching_project_id === $teachingProject->id, 404);
        abort_unless($generatedContent->content_type === 'lkpd', 404);
        abort_unless(
            $teachingProject->generatedContents()
                ->where('content_type', 'lkpd')
                ->latest('id')
                ->value('id') === $generatedContent->id,
            404,
        );

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
            return redirect()->to(route('teaching-projects.show', $teachingProject).'?tab=artifacts')
                ->with('info', 'Artifact tidak berada pada status gagal sehingga belum perlu dicoba ulang.');
        }

        GenerateArtifactDraft::dispatch($retried);

        return redirect()->to(route('teaching-projects.show', $teachingProject).'?tab=artifacts')
            ->with('success', 'Draft LKPD dijadwalkan ulang.');
    }

    public function store(
        GenerateArtifactRequest $request,
        TeachingProject $teachingProject,
        RppGenerationContextBuilder $contextBuilder,
    ): RedirectResponse {
        Gate::authorize('view', $teachingProject);

        $draft = $teachingProject->latestReadyRppDraft();
        $version = $draft?->versions()->latest('version_number')->first();

        if (! $draft instanceof GeneratedContent || $version === null) {
            return redirect()
                ->to(route('teaching-projects.show', $teachingProject).'?tab=artifacts')
                ->with('error', 'Buat draft RPP yang siap sebelum menghasilkan artifact.');
        }

        $artifactType = (string) $request->validated('artifact_type');
        $active = $teachingProject->generatedContents()
            ->where('content_type', $artifactType)
            ->whereIn('status', ['queued', 'processing'])
            ->exists();

        if ($active) {
            return redirect()
                ->to(route('teaching-projects.show', $teachingProject).'?tab=artifacts')
                ->with('info', 'Artifact sedang diproses. Tunggu sampai statusnya selesai.');
        }

        $context = $contextBuilder->fromProject($teachingProject);
        $content = $teachingProject->user()->firstOrFail()->generatedContents()->create([
            'teaching_project_id' => $teachingProject->id,
            'content_type' => $artifactType,
            'status' => 'queued',
            'input_snapshot' => [
                'artifact_type' => $artifactType,
                'rpp_version_id' => $version->id,
                'context' => $context->toArray(),
            ],
            'provider' => config('ai.provider', 'gemini'),
            'model' => config('ai.gemini.model'),
        ]);

        GenerateArtifactDraft::dispatch($content);

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=artifacts')
            ->with('success', 'Draft LKPD masuk antrean.');
    }
}
