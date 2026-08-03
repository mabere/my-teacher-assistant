<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\DocumentProcessingStatus;
use App\Enums\EducationLevel;
use App\Models\ClassProfile;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use App\Services\Documents\DocumentTextExtractorRegistry;
use App\Services\Documents\DocxTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

class DocumentTextExtractorTest extends TestCase
{
    use RefreshDatabase;

    public function test_docx_extractor_reads_paragraphs_and_table_cells(): void
    {
        Storage::fake('private');

        $word = new PhpWord;
        $section = $word->addSection();
        $section->addText('Sumber pembelajaran Bahasa Inggris.');
        $table = $section->addTable();
        $table->addRow();
        $table->addCell()->addText('Tujuan');
        $table->addCell()->addText('Siswa mampu mendeskripsikan objek.');

        $temporaryPath = tempnam(sys_get_temp_dir(), 'teacher-assistant-docx-');
        IOFactory::createWriter($word, 'Word2007')->save($temporaryPath);
        Storage::disk('private')->put('projects/1/sources/source.docx', file_get_contents($temporaryPath));
        unlink($temporaryPath);

        $document = $this->documentFixture('source.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $extracted = app(DocxTextExtractor::class)->extract($document);

        $this->assertStringContainsString('Sumber pembelajaran Bahasa Inggris.', $extracted->text);
        $this->assertStringContainsString('Siswa mampu mendeskripsikan objek.', $extracted->text);
        $this->assertSame('docx', $extracted->metadata['format']);
        $this->assertGreaterThan(0, $extracted->metadata['word_count']);
    }

    public function test_registry_resolves_docx_extractor_by_extension(): void
    {
        $document = $this->documentFixture('source.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->assertTrue(app(DocumentTextExtractorRegistry::class)->supports($document));
    }

    private function documentFixture(string $filename, string $extension, string $mimeType): ProjectDocument
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
            'title' => 'Extraction project',
            'topic' => 'Descriptive Text',
            'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            'duration_minutes' => 80,
        ]);

        return ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => $filename,
            'storage_path' => 'projects/1/sources/'.$filename,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size_bytes' => 10,
            'checksum' => str_repeat('b', 64),
            'processing_status' => DocumentProcessingStatus::Pending,
        ]);
    }
}
