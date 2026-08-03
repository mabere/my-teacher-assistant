<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\DocumentProcessingStatus;
use App\Enums\EducationLevel;
use App\Models\ClassProfile;
use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingProjectWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_the_workspace_and_deep_link_to_the_rpp_tab(): void
    {
        [$user, $project] = $this->projectFixture();

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project))
            ->assertOk()
            ->assertSee('Ruang kerja proyek')
            ->assertSee('Ringkasan')
            ->assertSee('Konteks proyek')
            ->assertSee('Belum dibuat');

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertOk()
            ->assertSee('RPP / Modul Ajar')
            ->assertSee('Draft RPP terstruktur')
            ->assertSee('Belum ada draft RPP');
    }

    public function test_workspace_uses_ready_source_and_real_rpp_processing_status(): void
    {
        [$user, $project] = $this->projectFixture();
        ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'buku-bahasa-indonesia.pdf',
            'storage_path' => 'projects/'.$project->id.'/buku-bahasa-indonesia.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 2048,
            'checksum' => hash('sha256', 'buku-bahasa-indonesia'),
            'processing_status' => DocumentProcessingStatus::Ready,
            'extracted_text' => 'Sumber siap.',
            'processed_at' => now(),
        ]);
        GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'queued',
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertOk()
            ->assertSee('1 sumber siap')
            ->assertSee('Menunggu antrean')
            ->assertSee('Draft sedang diproses');

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=sources')
            ->assertOk()
            ->assertSee('buku-bahasa-indonesia.pdf')
            ->assertSee('Siap digunakan');
    }

    public function test_unknown_workspace_tab_falls_back_to_the_overview(): void
    {
        [$user, $project] = $this->projectFixture();

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=not-a-real-tab')
            ->assertOk()
            ->assertSee('Paket pembelajaran')
            ->assertSee('Perangkat yang tersedia');
    }

    public function test_other_users_cannot_open_a_project_workspace(): void
    {
        [$owner, $project] = $this->projectFixture();
        $otherUser = User::factory()->create();

        $this->assertNotSame($owner->id, $otherUser->id);

        $this->actingAs($otherUser)
            ->get(route('teaching-projects.show', $project))
            ->assertForbidden();
    }

    /** @return array{0: User, 1: TeachingProject} */
    private function projectFixture(): array
    {
        $user = User::factory()->create();
        $profile = ClassProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Kelas X Bahasa Indonesia',
            'education_level' => EducationLevel::SmaMa,
            'grade' => 'X',
            'subject' => 'Bahasa Indonesia',
            'student_count' => 30,
            'ability_profile' => AbilityProfile::Mixed,
            'facilities' => 'Proyektor',
            'local_context' => 'Lingkungan sekolah',
            'differentiation_needs' => 'Contoh bertingkat',
        ]);

        return [$user, TeachingProject::query()->create([
            'user_id' => $user->id,
            'class_profile_id' => $profile->id,
            'title' => 'Menulis teks deskriptif',
            'topic' => 'Menulis teks deskriptif',
            'learning_outcomes' => 'Siswa memahami struktur teks deskriptif.',
            'learning_objectives' => 'Siswa dapat menulis teks deskriptif.',
            'duration_minutes' => 80,
            'teaching_model' => 'Problem Based Learning',
            'status' => 'draft',
        ])];
    }
}
