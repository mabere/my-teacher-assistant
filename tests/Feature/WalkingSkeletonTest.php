<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Models\ClassProfile;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalkingSkeletonTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_persist_a_class_profile_and_teaching_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('class-profiles.store'), [
                'name' => 'Kelas VIII A',
                'education_level' => EducationLevel::SmpMts->value,
                'grade' => 'VIII',
                'subject' => 'Bahasa Inggris',
                'student_count' => 28,
                'ability_profile' => AbilityProfile::Mixed->value,
                'facilities' => 'Papan tulis dan proyektor',
                'local_context' => 'Konawe',
            ])
            ->assertSessionHasNoErrors();

        $profile = ClassProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('teaching-projects.store'), [
                'class_profile_id' => $profile->id,
                'title' => 'Descriptive Text - Pertemuan 1',
                'topic' => 'Descriptive Text',
                'learning_outcomes' => 'Siswa memahami teks deskriptif.',
                'learning_objectives' => 'Siswa dapat mengidentifikasi informasi utama.',
                'duration_minutes' => 80,
                'teaching_model' => 'Project Based Learning',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('teaching_projects', [
            'user_id' => $user->id,
            'class_profile_id' => $profile->id,
            'status' => 'draft',
            'topic' => 'Descriptive Text',
        ]);
    }

    public function test_users_cannot_access_another_users_class_profile_or_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $profile = ClassProfile::query()->create([
            'user_id' => $owner->id,
            'name' => 'Kelas IX A',
            'education_level' => EducationLevel::SmpMts,
            'grade' => 'IX',
            'subject' => 'IPA',
            'ability_profile' => AbilityProfile::Mixed,
        ]);
        $project = TeachingProject::query()->create([
            'user_id' => $owner->id,
            'class_profile_id' => $profile->id,
            'title' => 'Project owner',
            'topic' => 'Ekosistem',
            'learning_objectives' => 'Menjelaskan ekosistem.',
            'duration_minutes' => 80,
        ]);

        $this->actingAs($other)->get(route('class-profiles.edit', $profile))->assertForbidden();
        $this->actingAs($other)->get(route('teaching-projects.show', $project))->assertForbidden();
        $this->actingAs($other)->get(route('teaching-projects.edit', $project))->assertForbidden();
    }

    public function test_project_cannot_reference_another_users_class_profile(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $profile = ClassProfile::query()->create([
            'user_id' => $owner->id,
            'name' => 'Kelas VII A',
            'education_level' => EducationLevel::SmpMts,
            'grade' => 'VII',
            'subject' => 'Matematika',
            'ability_profile' => AbilityProfile::Mixed,
        ]);

        $this->actingAs($other)
            ->from(route('teaching-projects.create'))
            ->post(route('teaching-projects.store'), [
                'class_profile_id' => $profile->id,
                'title' => 'Tidak boleh',
                'topic' => 'Aljabar',
                'learning_objectives' => 'Siswa memahami aljabar.',
                'duration_minutes' => 80,
            ])
            ->assertRedirect(route('teaching-projects.create'))
            ->assertSessionHasErrors('class_profile_id');

        $this->assertDatabaseMissing('teaching_projects', ['title' => 'Tidak boleh']);
    }
}
