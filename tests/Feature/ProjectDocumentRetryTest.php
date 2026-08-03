<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\DocumentProcessingStatus;
use App\Enums\EducationLevel;
use App\Jobs\GenerateRppDraft;
use App\Jobs\ProcessProjectDocument;
use App\Models\ClassProfile;
use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDocumentRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_retry_a_failed_docx_document(): void
    {
        Storage::fake('private');
        Queue::fake();
        [$user, $project] = $this->projectFixture();
        $document = ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'source.docx',
            'storage_path' => 'projects/1/sources/source.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 10,
            'checksum' => str_repeat('d', 64),
            'processing_status' => DocumentProcessingStatus::Failed,
            'processing_error' => 'Temporary failure',
            'processed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('teaching-projects.documents.retry', [$project, $document]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $document->refresh();
        $this->assertSame(DocumentProcessingStatus::Pending, $document->processing_status);
        $this->assertNull($document->processing_error);
        $this->assertNull($document->processed_at);
        Queue::assertPushed(ProcessProjectDocument::class);
    }

    public function test_owner_can_recover_a_stale_document_without_creating_a_new_record(): void
    {
        Queue::fake();
        [$user, $project] = $this->projectFixture();
        $document = ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'stale-source.docx',
            'storage_path' => 'projects/1/sources/stale-source.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 10,
            'checksum' => str_repeat('s', 64),
            'processing_status' => DocumentProcessingStatus::Processing,
            'processing_started_at' => now()->subMinutes(20),
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=sources')
            ->assertOk()
            ->assertSee('Proses tampak macet.')
            ->assertSee('Pulihkan');

        $this->actingAs($user)
            ->post(route('teaching-projects.documents.recover', [$project, $document]))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=sources')
            ->assertSessionHas('success');

        $document->refresh();
        $this->assertSame(DocumentProcessingStatus::Pending, $document->processing_status);
        $this->assertNull($document->processing_started_at);
        Queue::assertPushed(ProcessProjectDocument::class, 1);
        $this->assertDatabaseCount('project_documents', 1);
    }

    public function test_scheduler_command_recovers_stale_generated_content_and_documents(): void
    {
        Queue::fake();
        [$user, $project] = $this->projectFixture();
        $generated = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'processing',
            'started_at' => now()->subMinutes(20),
        ]);
        $document = ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'scheduled-recovery.docx',
            'storage_path' => 'projects/1/sources/scheduled-recovery.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 10,
            'checksum' => str_repeat('c', 64),
            'processing_status' => DocumentProcessingStatus::Processing,
            'processing_started_at' => now()->subMinutes(20),
        ]);

        $this->artisan('workflow:recover-stale')
            ->expectsOutput('Pemulihan selesai: 1 generator dan 1 dokumen dijadwalkan ulang.')
            ->assertSuccessful();

        $this->assertSame('queued', $generated->fresh()->status);
        $this->assertSame(DocumentProcessingStatus::Pending, $document->fresh()->processing_status);
        Queue::assertPushed(GenerateRppDraft::class, 1);
        Queue::assertPushed(ProcessProjectDocument::class, 1);
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
            'title' => 'Retry project',
            'topic' => 'Descriptive Text',
            'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            'duration_minutes' => 80,
        ]);

        return [$user, $project];
    }
}
