<?php

namespace App\Http\Controllers;

use App\Enums\DocumentProcessingStatus;
use App\Http\Requests\StoreProjectDocumentRequest;
use App\Jobs\ProcessProjectDocument;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Services\Workflow\StaleProcessRecovery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectDocumentController extends Controller
{
    public function store(StoreProjectDocumentRequest $request, TeachingProject $teachingProject): RedirectResponse
    {
        Gate::authorize('view', $teachingProject);

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("projects/{$teachingProject->id}/sources", Str::uuid()->toString().'.'.$extension, 'private');
        $document = ProjectDocument::query()->create([
            'user_id' => $request->user()->id,
            'teaching_project_id' => $teachingProject->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'processing_status' => DocumentProcessingStatus::Pending,
        ]);

        if (in_array($extension, ['docx', 'pdf'], true)) {
            ProcessProjectDocument::dispatch($document);
        }

        return back()->with('success', 'Dokumen sumber berhasil diunggah dan menunggu diproses.');
    }

    public function retry(TeachingProject $teachingProject, ProjectDocument $projectDocument): RedirectResponse
    {
        Gate::authorize('view', $teachingProject);
        abort_unless($projectDocument->teaching_project_id === $teachingProject->id, 404);

        if ((string) $projectDocument->getRawOriginal('processing_status') !== DocumentProcessingStatus::Failed->value) {
            return back()->with('info', 'Dokumen ini belum berada pada status gagal sehingga belum perlu dicoba ulang.');
        }

        $projectDocument->update([
            'processing_status' => DocumentProcessingStatus::Pending,
            'processing_error' => null,
            'processing_started_at' => null,
            'processed_at' => null,
        ]);

        if (in_array($projectDocument->extension, ['docx', 'pdf'], true)) {
            ProcessProjectDocument::dispatch($projectDocument->fresh());
        }

        return back()->with('success', 'Dokumen dijadwalkan untuk diproses ulang.');
    }

    public function recover(
        TeachingProject $teachingProject,
        ProjectDocument $projectDocument,
        StaleProcessRecovery $recovery,
    ): RedirectResponse {
        Gate::authorize('view', $teachingProject);
        abort_unless($projectDocument->teaching_project_id === $teachingProject->id, 404);

        $recovered = $recovery->recoverDocument($projectDocument);

        return redirect()
            ->to(route('teaching-projects.show', $teachingProject).'?tab=sources')
            ->with(
                $recovered instanceof ProjectDocument ? 'success' : 'info',
                $recovered instanceof ProjectDocument
                    ? 'Dokumen yang macet dijadwalkan ulang.'
                    : 'Dokumen belum melewati batas waktu pemulihan.',
            );
    }

    public function destroy(TeachingProject $teachingProject, ProjectDocument $projectDocument): RedirectResponse
    {
        Gate::authorize('view', $teachingProject);
        abort_unless($projectDocument->teaching_project_id === $teachingProject->id, 404);

        Storage::disk('private')->delete($projectDocument->storage_path);
        $projectDocument->delete();

        return back()->with('success', 'Dokumen sumber berhasil dihapus.');
    }
}
