<?php

namespace App\Http\Controllers;

use App\Enums\DocumentProcessingStatus;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $recentProjects = $user->teachingProjects()
            ->with('classProfile')
            ->withCount([
                'documents',
                'documents as ready_documents_count' => static fn ($query) => $query->where('processing_status', DocumentProcessingStatus::Ready->value),
                'generatedContents as ready_rpp_count' => static fn ($query) => $query
                    ->where('content_type', 'rpp')
                    ->where('status', 'ready'),
            ])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $latestRppDraft = $user->generatedContents()
            ->where('content_type', 'rpp')
            ->with('teachingProject')
            ->latest('id')
            ->first();

        return view('dashboard', [
            'classProfileCount' => $user->classProfiles()->count(),
            'teachingProjectCount' => $user->teachingProjects()->count(),
            'readyDocumentCount' => ProjectDocument::query()
                ->where('user_id', $user->id)
                ->where('processing_status', DocumentProcessingStatus::Ready->value)
                ->count(),
            'processingDocumentCount' => ProjectDocument::query()
                ->where('user_id', $user->id)
                ->whereIn('processing_status', [
                    DocumentProcessingStatus::Pending->value,
                    DocumentProcessingStatus::Processing->value,
                ])
                ->count(),
            'recentProjects' => $recentProjects,
            'activeProject' => $recentProjects->first(static fn ($project): bool => in_array($project->getRawOriginal('status'), ['draft', 'active'], true)),
            'latestRppDraft' => $latestRppDraft,
            'readyRppCount' => $user->generatedContents()
                ->where('content_type', 'rpp')
                ->where('status', 'ready')
                ->count(),
        ]);
    }
}
