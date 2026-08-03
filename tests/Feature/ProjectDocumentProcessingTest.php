<?php

namespace Tests\Feature;

use App\Contracts\DocumentTextExtractor;
use App\Data\ExtractedDocument;
use App\Enums\AbilityProfile;
use App\Enums\DocumentProcessingStatus;
use App\Enums\EducationLevel;
use App\Jobs\ProcessProjectDocument;
use App\Models\ClassProfile;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDocumentProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_processing_job_persists_extracted_text_and_metadata(): void
    {
        Storage::fake('private');
        $document = $this->documentFixture();
        $this->app->bind(DocumentTextExtractor::class, fn (): DocumentTextExtractor => new class implements DocumentTextExtractor
        {
            public function supports(ProjectDocument $document): bool
            {
                return true;
            }

            public function extract(ProjectDocument $document): ExtractedDocument
            {
                return new ExtractedDocument('Isi dokumen uji.', ['pages' => 1]);
            }
        });

        ProcessProjectDocument::dispatchSync($document);

        $document->refresh();
        $this->assertSame(DocumentProcessingStatus::Ready, $document->processing_status);
        $this->assertSame('Isi dokumen uji.', $document->extracted_text);
        $this->assertSame(['pages' => 1], $document->extraction_metadata);
        $this->assertNotNull($document->processed_at);
    }

    public function test_processing_failure_is_recorded_before_job_is_rethrown(): void
    {
        $document = $this->documentFixture();
        $this->app->bind(DocumentTextExtractor::class, fn (): DocumentTextExtractor => new class implements DocumentTextExtractor
        {
            public function supports(ProjectDocument $document): bool
            {
                return false;
            }

            public function extract(ProjectDocument $document): ExtractedDocument
            {
                return new ExtractedDocument('Tidak dipanggil.');
            }
        });

        try {
            ProcessProjectDocument::dispatchSync($document);
            $this->fail('Expected the processing job to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Tidak ada extractor', $exception->getMessage());
        }

        $this->assertSame(DocumentProcessingStatus::Failed, $document->fresh()->processing_status);
        $this->assertNotNull($document->fresh()->processing_error);
    }

    private function documentFixture(): ProjectDocument
    {
        $user = User::factory()->create();
        $profile = ClassProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Kelas VIII A',
            'education_level' => EducationLevel::SmpMts,
            'grade' => 'VIII',
            'subject' => 'Bahasa Inggris',
            'ability_profile' => AbilityProfile::Mixed,
        ]);
        $project = TeachingProject::query()->create([
            'user_id' => $user->id,
            'class_profile_id' => $profile->id,
            'title' => 'Processing project',
            'topic' => 'Descriptive Text',
            'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            'duration_minutes' => 80,
        ]);

        return ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'source.pdf',
            'storage_path' => 'projects/1/source.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 10,
            'checksum' => str_repeat('a', 64),
            'processing_status' => DocumentProcessingStatus::Pending,
        ]);
    }
}
