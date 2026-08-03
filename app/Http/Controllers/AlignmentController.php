<?php

namespace App\Http\Controllers;

use App\Models\GeneratedContent;
use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;
use App\Services\Alignment\AlignmentChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AlignmentController extends Controller
{
    public function store(TeachingProject $teachingProject, AlignmentChecker $checker): RedirectResponse
    {
        Gate::authorize('view', $teachingProject);

        $draft = $this->latestRppDraft($teachingProject);
        $version = $draft?->versions()->latest('version_number')->first();

        if (! $draft instanceof GeneratedContent || ! $version instanceof GeneratedContentVersion) {
            return redirect()
                ->to(route('teaching-projects.show', $teachingProject).'?tab=alignment')
                ->with('error', 'Alignment belum dapat dijalankan karena belum ada draft RPP yang siap.');
        }

        $findings = $checker->check($teachingProject, $version);

        DB::transaction(function () use ($teachingProject, $version, $findings): void {
            $version->alignmentFindings()->delete();
            $version->alignmentFindings()->createMany(array_map(
                static fn (array $finding): array => [
                    ...$finding,
                    'teaching_project_id' => $teachingProject->id,
                ],
                $findings,
            ));
        });

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=alignment')
            ->with('success', 'Pemeriksaan keselarasan selesai. Periksa temuan sebelum approval.');
    }

    private function latestRppDraft(TeachingProject $project): ?GeneratedContent
    {
        return $project->latestReadyRppDraft();
    }
}
