<?php

namespace App\Http\Controllers;

use App\Models\GeneratedContent;
use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;
use App\Services\Exports\RppExportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RppExportController extends Controller
{
    public function download(
        TeachingProject $teachingProject,
        GeneratedContent $generatedContent,
        GeneratedContentVersion $generatedContentVersion,
        string $format,
        RppExportService $exporter,
    ): Response|BinaryFileResponse {
        Gate::authorize('view', $teachingProject);
        $this->authorizeVersion($teachingProject, $generatedContent, $generatedContentVersion);

        abort_unless($generatedContentVersion->approval()->exists(), 403, 'Versi RPP harus disetujui sebelum export.');

        return $exporter->download($teachingProject, $generatedContentVersion, $format);
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
