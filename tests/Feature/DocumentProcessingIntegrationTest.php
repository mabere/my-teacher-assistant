<?php

namespace Tests\Feature;

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
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

class DocumentProcessingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_docx_job_uses_the_default_registry_and_marks_document_ready(): void
    {
        Storage::fake('private');
        $document = $this->documentFixture();

        $word = new PhpWord;
        $word->addSection()->addText('Dokumen untuk pipeline nyata.');
        $temporaryPath = tempnam(sys_get_temp_dir(), 'teacher-assistant-docx-');
        IOFactory::createWriter($word, 'Word2007')->save($temporaryPath);
        Storage::disk('private')->put($document->storage_path, file_get_contents($temporaryPath));
        unlink($temporaryPath);

        ProcessProjectDocument::dispatchSync($document);

        $document->refresh();
        $this->assertSame(DocumentProcessingStatus::Ready, $document->processing_status);
        $this->assertStringContainsString('Dokumen untuk pipeline nyata.', (string) $document->extracted_text);
        $this->assertSame('docx', $document->extraction_metadata['format']);
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
            'title' => 'Processing integration project',
            'topic' => 'Descriptive Text',
            'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            'duration_minutes' => 80,
        ]);

        return ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'source.docx',
            'storage_path' => 'projects/1/sources/source.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 10,
            'checksum' => str_repeat('c', 64),
            'processing_status' => DocumentProcessingStatus::Pending,
        ]);
    }
}
