<?php

namespace Tests\Feature;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Models\ClassProfile;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingProjectWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_complete_the_project_wizard_and_persist_a_draft(): void
    {
        $user = User::factory()->create();
        $profile = $this->profileFixture($user);

        $response = $this->flushSession()
            ->actingAs($user)
            ->get(route('teaching-projects.create', ['step' => 1]));

        $response
            ->assertOk();

        $this->actingAs($user)
            ->post(route('teaching-projects.wizard.basics'), [
                'class_profile_id' => $profile->id,
                'title' => 'Descriptive Text - Pertemuan 1',
                'topic' => 'Descriptive Text',
                'learning_outcomes' => 'Siswa memahami teks deskriptif.',
                'learning_objectives' => 'Siswa dapat mendeskripsikan objek.',
            ])
            ->assertRedirect(route('teaching-projects.create', ['step' => 2]));

        $this->actingAs($user)
            ->get(route('teaching-projects.create', ['step' => 2]))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('teaching-projects.wizard.execution'), [
                'duration_minutes' => 80,
                'teaching_model' => 'Project Based Learning',
            ])
            ->assertRedirect(route('teaching-projects.create', ['step' => 3]));

        $this->actingAs($user)
            ->post(route('teaching-projects.wizard.context'), [
                'context_confirmed' => '1',
            ])
            ->assertRedirect(route('teaching-projects.create', ['step' => 4]));

        $response = $this->actingAs($user)
            ->post(route('teaching-projects.wizard.complete'), [
                'review_confirmed' => '1',
            ]);

        $project = TeachingProject::query()->where('title', 'Descriptive Text - Pertemuan 1')->firstOrFail();

        $response
            ->assertRedirect(route('teaching-projects.show', $project))
            ->assertSessionHas('success');

        $this->assertSame($user->id, $project->user_id);
        $this->assertSame($profile->id, $project->class_profile_id);
        $this->assertSame('draft', $project->status->value);
        $this->assertSame(80, $project->duration_minutes);
        $this->assertFalse($this->app['session']->has('teaching_project_wizard'));
    }

    public function test_wizard_rejects_a_class_profile_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $profile = $this->profileFixture($otherUser);

        $this->flushSession()
            ->actingAs($user)
            ->from(route('teaching-projects.create'))
            ->post(route('teaching-projects.wizard.basics'), [
                'class_profile_id' => $profile->id,
                'title' => 'Tidak boleh',
                'topic' => 'Aljabar',
                'learning_objectives' => 'Siswa memahami aljabar.',
            ])
            ->assertRedirect(route('teaching-projects.create'))
            ->assertSessionHasErrors('class_profile_id');

        $this->assertDatabaseMissing('teaching_projects', ['title' => 'Tidak boleh']);
    }

    public function test_wizard_cannot_skip_the_context_steps_without_session_state(): void
    {
        $user = User::factory()->create();

        $this->flushSession()
            ->actingAs($user)
            ->post(route('teaching-projects.wizard.execution'), [
                'duration_minutes' => 80,
            ])
            ->assertRedirect(route('teaching-projects.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('teaching_projects', 0);
    }

    private function profileFixture(User $user): ClassProfile
    {
        return ClassProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Kelas VIII A',
            'education_level' => EducationLevel::SmpMts,
            'grade' => 'VIII',
            'subject' => 'Bahasa Inggris',
            'student_count' => 28,
            'ability_profile' => AbilityProfile::Mixed,
            'facilities' => 'Proyektor',
            'differentiation_needs' => 'Contoh bertahap',
        ]);
    }
}
