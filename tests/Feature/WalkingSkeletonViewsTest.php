<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Models\ClassProfile;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalkingSkeletonViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_explains_the_teacher_workspace(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Satu konteks untuk seluruh persiapan pembelajaran.')
            ->assertSee('Cara kerja')
            ->assertSee(route('login'));
    }

    public function test_authenticated_shell_keeps_sidebar_footer_contract(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('lg:h-screen')
            ->assertSee('Keluar');
    }

    public function test_authenticated_user_can_browse_profile_and_project_pages(): void
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
            'title' => 'Descriptive Text',
            'topic' => 'Descriptive Text',
            'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            'duration_minutes' => 80,
        ]);

        $this->actingAs($user)->get(route('class-profiles.index'))->assertOk()->assertSee('Kelas VIII A');
        $this->actingAs($user)->get(route('class-profiles.edit', $profile))->assertOk()->assertSee('Edit profil kelas');
        $this->actingAs($user)->get(route('teaching-projects.index'))->assertOk()->assertSee('Descriptive Text');
        $this->actingAs($user)->get(route('teaching-projects.show', $project))->assertOk()->assertSee('Konteks proyek');
    }

    public function test_updating_a_project_preserves_ownership_and_changes_context(): void
    {
        $user = User::factory()->create();
        $profile = ClassProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Kelas VII A',
            'education_level' => EducationLevel::SmpMts,
            'grade' => 'VII',
            'subject' => 'IPA',
            'ability_profile' => AbilityProfile::Mixed,
        ]);
        $project = TeachingProject::query()->create([
            'user_id' => $user->id,
            'class_profile_id' => $profile->id,
            'title' => 'Ekosistem',
            'topic' => 'Ekosistem',
            'learning_objectives' => 'Tujuan awal.',
            'duration_minutes' => 80,
        ]);

        $this->actingAs($user)->put(route('teaching-projects.update', $project), [
            'class_profile_id' => $profile->id,
            'title' => 'Ekosistem - Revisi',
            'topic' => 'Ekosistem dan lingkungan',
            'learning_objectives' => 'Siswa dapat menjelaskan hubungan dalam ekosistem.',
            'duration_minutes' => 90,
        ])->assertRedirect(route('teaching-projects.show', $project));

        $this->assertDatabaseHas('teaching_projects', [
            'id' => $project->id,
            'user_id' => $user->id,
            'title' => 'Ekosistem - Revisi',
            'duration_minutes' => 90,
        ]);
    }
}
