<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveGeneratedContentRequest;
use App\Models\ContentApproval;
use App\Models\GeneratedContent;
use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ContentApprovalController extends Controller
{
    public function store(
        ApproveGeneratedContentRequest $request,
        TeachingProject $teachingProject,
        GeneratedContent $generatedContent,
        GeneratedContentVersion $generatedContentVersion,
    ): RedirectResponse {
        Gate::authorize('view', $teachingProject);
        $this->authorizeVersion($teachingProject, $generatedContent, $generatedContentVersion);

        if (! $generatedContentVersion->alignmentFindings()->exists()) {
            return redirect()
                ->to(route('teaching-projects.show', $teachingProject).'?tab=approval')
                ->with('error', 'Jalankan pemeriksaan keselarasan sebelum menyetujui versi ini.');
        }

        $data = $request->validated();

        ContentApproval::query()->updateOrCreate(
            ['generated_content_version_id' => $generatedContentVersion->id],
            [
                'generated_content_id' => $generatedContent->id,
                'approved_by' => $request->user()->id,
                'checklist' => [
                    'context_verified' => true,
                    'alignment_reviewed' => true,
                    'content_reviewed' => true,
                ],
                'notes' => $data['notes'] ?? null,
                'approved_at' => now(),
            ],
        );

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=approval')
            ->with('success', 'Versi RPP disetujui. Export sekarang tersedia untuk versi ini.');
    }

    private function authorizeVersion(
        TeachingProject $project,
        GeneratedContent $draft,
        GeneratedContentVersion $version,
    ): void {
        abort_unless($draft->teaching_project_id === $project->id, 404);
        abort_unless($draft->content_type === 'rpp' && $draft->status === 'ready', 404);
        abort_unless($project->latestRppDraft()?->id === $draft->id, 404);
        abort_unless($version->generated_content_id === $draft->id, 404);
        abort_unless($draft->versions()->latest('version_number')->value('id') === $version->id, 404);
    }
}
