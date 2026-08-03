<?php

namespace Tests\Feature;

use App\Models\ClassProfile;
use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_real_user_scoped_project_document_and_rpp_data(): void
    {
        $user = User::factory()->create(['name' => 'Bu Rina']);
        $profile = ClassProfile::create([
            'user_id' => $user->id,
            'name' => 'Kelas VIII A',
            'education_level' => 'smp_mts',
            'grade' => 'VIII',
            'subject' => 'Bahasa Inggris',
            'student_count' => 32,
            'ability_profile' => 'mixed',
        ]);
        $project = TeachingProject::create([
            'user_id' => $user->id,
            'class_profile_id' => $profile->id,
            'title' => 'Descriptive Text',
            'topic' => 'Local Places',
            'learning_objectives' => 'Siswa mampu mendeskripsikan tempat lokal.',
            'duration_minutes' => 80,
            'status' => 'draft',
        ]);
        ProjectDocument::create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'kurikulum.docx',
            'storage_path' => 'projects/1/kurikulum.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 1024,
            'checksum' => hash('sha256', 'dashboard-test'),
            'processing_status' => 'ready',
        ]);
        GeneratedContent::create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'ready',
            'input_snapshot' => [],
            'output_payload' => ['identity' => ['topic' => 'Local Places']],
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Descriptive Text')
            ->assertSee('Siap ditinjau')
            ->assertSee('Dokumen siap');
    }
}
