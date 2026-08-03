<?php

namespace Tests\Feature;

use App\Contracts\RppGenerator;
use App\Enums\AbilityProfile;
use App\Enums\DocumentProcessingStatus;
use App\Enums\EducationLevel;
use App\Jobs\GenerateRppDraft;
use App\Models\AiProviderSetting;
use App\Models\ClassProfile;
use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use App\Models\TeachingProject;
use App\Models\User;
use App\Services\AI\RppGenerationContextBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RppGenerationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_queue_an_rpp_draft_from_project_page(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $project = $this->project($user);

        $this->actingAs($user)
            ->post(route('teaching-projects.generate-rpp', $project))
            ->assertSessionHas('success');

        $draft = GeneratedContent::query()->firstOrFail();
        $this->assertSame('queued', $draft->status);
        $this->assertSame($project->id, $draft->teaching_project_id);
        $this->assertSame('rpp', $draft->content_type);
        $this->assertSame($project->learning_objectives, data_get($draft->input_snapshot, 'curriculum.learning_objectives'));
        Queue::assertPushed(GenerateRppDraft::class);
    }

    public function test_rpp_job_saves_validated_structured_output(): void
    {
        $user = User::factory()->create();
        $project = $this->project($user);
        ProjectDocument::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'original_filename' => 'kurikulum.docx',
            'storage_path' => 'projects/1/kurikulum.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 1024,
            'checksum' => hash('sha256', 'kurikulum'),
            'processing_status' => DocumentProcessingStatus::Ready,
            'extracted_text' => 'Capaian pembelajaran dan konteks dokumen.',
            'processed_at' => now(),
        ]);
        $setting = AiProviderSetting::query()->create([
            'provider' => 'gemini',
            'model' => 'gemini-3.6-flash',
            'base_url' => 'https://example.test/v1beta',
            'is_active' => true,
        ]);
        $setting->setApiKey('test-gemini-key');
        $setting->save();
        Http::fake(['https://example.test/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($this->payload())]]]]]])]);

        $draft = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'queued',
        ]);

        (new GenerateRppDraft($draft))->handle(app(RppGenerator::class), app(RppGenerationContextBuilder::class));

        $draft->refresh();
        $this->assertSame('ready', $draft->status);
        $this->assertSame(['Apersepsi'], data_get($draft->output_payload, 'learning_activities.opening'));
        $this->assertSame('kurikulum.docx', data_get($draft->input_snapshot, 'sources.0.filename'));
    }

    private function project(User $user): TeachingProject
    {
        $profile = ClassProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Kelas X Bahasa Indonesia',
            'education_level' => EducationLevel::SmaMa,
            'grade' => 'X',
            'subject' => 'Bahasa Indonesia',
            'student_count' => 28,
            'ability_profile' => AbilityProfile::Mixed,
            'facilities' => 'Papan tulis',
            'local_context' => 'Lingkungan sekolah',
            'differentiation_needs' => 'Contoh bertingkat',
        ]);

        return TeachingProject::query()->create([
            'user_id' => $user->id,
            'class_profile_id' => $profile->id,
            'title' => 'Teks Eksposisi',
            'topic' => 'Teks eksposisi',
            'learning_outcomes' => 'Memahami struktur teks eksposisi.',
            'learning_objectives' => 'Siswa dapat mengidentifikasi struktur teks eksposisi.',
            'duration_minutes' => 80,
            'teaching_model' => 'Problem Based Learning',
            'status' => 'draft',
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'identity' => ['subject' => 'Bahasa Indonesia', 'grade' => 'X', 'topic' => 'Teks eksposisi'],
            'learning_objectives' => ['Siswa dapat mengidentifikasi struktur teks eksposisi.'],
            'learning_activities' => ['opening' => ['Apersepsi'], 'core' => ['Analisis'], 'closing' => ['Refleksi']],
            'assessment' => ['diagnostic' => [], 'formative' => ['Tanya jawab'], 'summative' => ['Tugas']],
            'differentiation' => [],
            'remedial' => [],
            'enrichment' => [],
            'references' => ['Buku teks'],
        ];
    }
}
