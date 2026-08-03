<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Jobs\ProcessProjectDocument;
use App\Models\ClassProfile;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_upload_a_pdf_to_private_storage(): void
    {
        Storage::fake('private');
        Queue::fake();
        [$user, $project] = $this->projectFixture();
        $file = UploadedFile::fake()->create('kurikulum.pdf', 120, 'application/pdf');

        $this->actingAs($user)
            ->post(route('teaching-projects.documents.store', $project), ['document' => $file])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $document = ProjectDocument::query()->firstOrFail();
        $this->assertSame('kurikulum.pdf', $document->original_filename);
        $this->assertSame('pending', $document->processing_status->value);
        Storage::disk('private')->assertExists($document->storage_path);
        Queue::assertPushed(ProcessProjectDocument::class);
    }

    public function test_upload_rejects_unsupported_file_types(): void
    {
        Storage::fake('private');
        [$user, $project] = $this->projectFixture();
        $file = UploadedFile::fake()->create('catatan.txt', 10, 'text/plain');

        $this->actingAs($user)
            ->from(route('teaching-projects.show', $project))
            ->post(route('teaching-projects.documents.store', $project), ['document' => $file])
            ->assertRedirect(route('teaching-projects.show', $project))
            ->assertSessionHasErrors('document');

        $this->assertDatabaseCount('project_documents', 0);
    }

    public function test_teacher_cannot_upload_to_another_users_project(): void
    {
        Storage::fake('private');
        [$owner, $project] = $this->projectFixture();
        $other = User::factory()->create();
        $file = UploadedFile::fake()->create('kurikulum.pdf', 20, 'application/pdf');

        $this->actingAs($other)
            ->post(route('teaching-projects.documents.store', $project), ['document' => $file])
            ->assertForbidden();
    }

    public function test_deleting_project_removes_private_source_files(): void
    {
        Storage::fake('private');
        [$user, $project] = $this->projectFixture();

        $document = ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'kurikulum.pdf',
            'storage_path' => 'projects/'.$project->id.'/kurikulum.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1200,
            'checksum' => hash('sha256', 'kurikulum.pdf'),
        ]);
        Storage::disk('private')->put($document->storage_path, 'source');

        $this->actingAs($user)
            ->delete(route('teaching-projects.destroy', $project))
            ->assertRedirect(route('teaching-projects.index'));

        Storage::disk('private')->assertMissing($document->storage_path);
        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
    }

    /** @return array{0: User, 1: TeachingProject} */
    private function projectFixture(): array
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
            'title' => 'Project sumber',
            'topic' => 'Descriptive Text',
            'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            'duration_minutes' => 80,
        ]);

        return [$user, $project];
    }
}
