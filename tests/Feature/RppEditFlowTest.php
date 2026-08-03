<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Models\ClassProfile;
use App\Models\GeneratedContent;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RppEditFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_rpp_and_create_new_version(): void
    {
        $user = User::factory()->create();
        $project = $this->projectFor($user);
        $payload = $this->payload();
        $draft = GeneratedContent::create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'ready',
            'input_snapshot' => ['topic' => $project->topic],
            'output_payload' => $payload,
            'completed_at' => now(),
        ]);
        $draft->versions()->create([
            'created_by' => $user->id,
            'version_number' => 1,
            'payload' => $payload,
            'change_summary' => 'Draft awal dari Gemini',
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.rpp-drafts.edit', [$project, $draft]))
            ->assertOk()
            ->assertSee('Kembali ke workspace proyek')
            ->assertSee('Context panel')
            ->assertSee('Versi aktif');

        $this->actingAs($user)
            ->put(route('teaching-projects.rpp-drafts.update', [$project, $draft]), [
                'identity_subject' => 'Bahasa Indonesia', 'identity_grade' => 'X', 'identity_topic' => 'Teks Eksposisi',
                'learning_objectives' => "Menganalisis struktur teks\nMenulis simpulan", 'opening' => 'Apersepsi',
                'core' => "Membaca teks\nDiskusi kelompok", 'closing' => 'Refleksi', 'diagnostic' => 'Pertanyaan pemantik',
                'formative' => 'Exit ticket', 'summative' => 'Tugas menulis', 'differentiation' => 'Pilihan sumber bacaan',
                'remedial' => 'Bimbingan terarah', 'enrichment' => 'Analisis teks tambahan', 'references' => 'Buku Bahasa Indonesia kelas X',
            ])
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertSessionHas('success');

        $draft->refresh();
        $this->assertSame(['Menganalisis struktur teks', 'Menulis simpulan'], $draft->output_payload['learning_objectives']);
        $this->assertSame(2, $draft->versions()->count());
        $this->assertSame('Revisi manual guru', $draft->versions()->latest('version_number')->value('change_summary'));
    }

    public function test_non_owner_cannot_edit_rpp(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = $this->projectFor($owner);
        $draft = GeneratedContent::create([
            'user_id' => $owner->id, 'teaching_project_id' => $project->id, 'content_type' => 'rpp', 'status' => 'ready',
            'input_snapshot' => [], 'output_payload' => $this->payload(),
        ]);

        $this->actingAs($other)->get(route('teaching-projects.rpp-drafts.edit', [$project, $draft]))->assertForbidden();
    }

    private function projectFor(User $user): TeachingProject
    {
        $profile = ClassProfile::create([
            'user_id' => $user->id, 'name' => 'Kelas X', 'education_level' => EducationLevel::SmaMa,
            'grade' => 'X', 'subject' => 'Bahasa Indonesia', 'student_count' => 30, 'ability_profile' => AbilityProfile::Mixed,
        ]);

        return TeachingProject::create([
            'user_id' => $user->id, 'class_profile_id' => $profile->id, 'title' => 'Proyek Bahasa Indonesia',
            'topic' => 'Teks Eksposisi', 'learning_outcomes' => 'Menganalisis teks', 'learning_objectives' => 'Menulis simpulan',
            'duration_minutes' => 90,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'identity' => ['subject' => 'Bahasa Indonesia', 'grade' => 'X', 'topic' => 'Teks Eksposisi'],
            'learning_objectives' => ['Menganalisis teks'],
            'learning_activities' => ['opening' => ['Apersepsi'], 'core' => ['Membaca teks'], 'closing' => ['Refleksi']],
            'assessment' => ['diagnostic' => [], 'formative' => ['Diskusi'], 'summative' => ['Tugas']],
            'differentiation' => [], 'remedial' => [], 'enrichment' => [], 'references' => [],
        ];
    }
}
