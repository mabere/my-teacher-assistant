<?php

namespace Tests\Feature;

use App\Contracts\ArtifactGenerator;
use App\Contracts\RppGenerator;
use App\Data\Generation\ArtifactGenerationOutput;
use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Jobs\GenerateArtifactDraft;
use App\Jobs\GenerateRppDraft;
use App\Models\GeneratedContent;
use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;
use App\Models\User;
use App\Services\AI\RppGenerationContextBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Phase8FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_alignment_checker_persists_findings_for_the_latest_rpp_version(): void
    {
        [$user, $project, $draft, $version] = $this->rppFixture($this->payloadWithGaps());

        $this->actingAs($user)
            ->post(route('teaching-projects.alignment.run', $project))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=alignment');

        $this->assertDatabaseHas('alignment_findings', [
            'teaching_project_id' => $project->id,
            'generated_content_version_id' => $version->id,
            'code' => 'objective.uncovered.1',
            'severity' => 'warning',
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=alignment')
            ->assertOk()
            ->assertSee('Perlu perhatian')
            ->assertSee('Tujuan proyek belum terlihat di RPP');
    }

    public function test_approval_is_bound_to_version_and_enables_real_exports(): void
    {
        [$user, $project, $draft, $version] = $this->rppFixture($this->completePayload());

        $this->actingAs($user)
            ->post(route('teaching-projects.alignment.run', $project))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('teaching-projects.approvals.store', [$project, $draft, $version]), [
                'context_verified' => '1',
                'alignment_reviewed' => '1',
                'content_reviewed' => '1',
                'notes' => 'Sudah diperiksa.',
            ])
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=approval');

        $this->assertDatabaseHas('content_approvals', [
            'generated_content_id' => $draft->id,
            'generated_content_version_id' => $version->id,
            'approved_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=approval')
            ->assertOk()
            ->assertSee('Disetujui')
            ->assertSee('Unduh DOCX')
            ->assertSee('Unduh PDF');

        $this->actingAs($user)
            ->get(route('teaching-projects.exports.download', [$project, $draft, $version, 'docx']))
            ->assertOk()
            ->assertDownload('menulis-teks-deskriptif-rpp-v1.docx');

        $pdf = $this->actingAs($user)
            ->get(route('teaching-projects.exports.download', [$project, $draft, $version, 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $versionTwo = $draft->versions()->create([
            'created_by' => $user->id,
            'version_number' => 2,
            'payload' => $this->completePayload(),
            'change_summary' => 'Revisi manual guru',
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.exports.download', [$project, $draft, $version, 'docx']))
            ->assertNotFound();

        $this->assertDatabaseMissing('content_approvals', [
            'generated_content_version_id' => $versionTwo->id,
        ]);
    }

    public function test_approval_requires_alignment_findings(): void
    {
        [$user, $project, $draft, $version] = $this->rppFixture($this->completePayload());

        $this->actingAs($user)
            ->post(route('teaching-projects.approvals.store', [$project, $draft, $version]), [
                'context_verified' => '1',
                'alignment_reviewed' => '1',
                'content_reviewed' => '1',
            ])
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=approval')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('content_approvals', 0);
    }

    public function test_failed_generations_can_be_retried_without_creating_new_records(): void
    {
        Queue::fake();
        [$user, $project, $draft, $version] = $this->rppFixture($this->completePayload());

        $draft->update([
            'status' => 'failed',
            'output_payload' => null,
            'error_message' => 'Provider sementara tidak tersedia.',
            'completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('teaching-projects.rpp-drafts.retry', [$project, $draft]))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertSessionHas('success');

        $draft->refresh();
        $this->assertSame('queued', $draft->status);
        $this->assertNull($draft->error_message);
        $this->assertNull($draft->output_payload);
        Queue::assertPushed(GenerateRppDraft::class, 1);

        $this->actingAs($user)
            ->post(route('teaching-projects.rpp-drafts.retry', [$project, $draft]))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertSessionHas('info');

        $artifact = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'lkpd',
            'status' => 'failed',
            'input_snapshot' => [
                'rpp_version_id' => $version->id,
                'context' => [],
            ],
            'error_message' => 'Provider sementara tidak tersedia.',
            'completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('teaching-projects.artifacts.retry', [$project, $artifact]))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=artifacts')
            ->assertSessionHas('success');

        $artifact->refresh();
        $this->assertSame('queued', $artifact->status);
        $this->assertNull($artifact->error_message);
        Queue::assertPushed(GenerateArtifactDraft::class, 1);

        $this->assertDatabaseCount('generated_contents', 2);
    }

    public function test_stale_generations_are_visible_and_can_be_recovered_in_place(): void
    {
        Queue::fake();
        [$user, $project, $draft, $version] = $this->rppFixture($this->completePayload());
        $draft->update([
            'status' => 'processing',
            'started_at' => now()->subMinutes(20),
            'completed_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertOk()
            ->assertSee('Macet')
            ->assertSee('Proses tampak macet.')
            ->assertSee('Pulihkan proses RPP');

        $this->actingAs($user)
            ->post(route('teaching-projects.rpp-drafts.recover', [$project, $draft]))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=rpp')
            ->assertSessionHas('success');

        $draft->refresh();
        $this->assertSame('queued', $draft->status);
        $this->assertNull($draft->started_at);
        Queue::assertPushed(GenerateRppDraft::class, 1);

        $artifact = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'lkpd',
            'status' => 'processing',
            'input_snapshot' => [
                'rpp_version_id' => $version->id,
                'context' => [],
            ],
            'started_at' => now()->subMinutes(20),
            'completed_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=artifacts')
            ->assertOk()
            ->assertSee('Pulihkan proses LKPD');

        $this->actingAs($user)
            ->post(route('teaching-projects.artifacts.recover', [$project, $artifact]))
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=artifacts')
            ->assertSessionHas('success');

        $this->assertSame('queued', $artifact->fresh()->status);
        Queue::assertPushed(GenerateArtifactDraft::class, 1);
    }

    public function test_only_the_latest_rpp_draft_can_continue_the_workflow(): void
    {
        Queue::fake();
        [$user, $project, $draft, $version] = $this->rppFixture($this->completePayload());

        $this->actingAs($user)
            ->post(route('teaching-projects.alignment.run', $project));

        $this->actingAs($user)
            ->post(route('teaching-projects.approvals.store', [$project, $draft, $version]), [
                'context_verified' => '1',
                'alignment_reviewed' => '1',
                'content_reviewed' => '1',
            ]);

        $newDraft = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'queued',
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.rpp-drafts.edit', [$project, $draft]))
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('teaching-projects.approvals.store', [$project, $draft, $version]), [
                'context_verified' => '1',
                'alignment_reviewed' => '1',
                'content_reviewed' => '1',
            ])
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('teaching-projects.exports.download', [$project, $draft, $version, 'docx']))
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('teaching-projects.artifacts.store', $project), [
                'artifact_type' => 'lkpd',
            ])
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=artifacts')
            ->assertSessionHas('error');

        $this->assertSame('queued', $newDraft->status);
        $this->assertDatabaseCount('generated_contents', 2);
        $this->assertDatabaseMissing('generated_contents', [
            'teaching_project_id' => $project->id,
            'content_type' => 'lkpd',
        ]);
    }

    public function test_completed_generation_jobs_are_idempotent(): void
    {
        [$user, $project, $draft] = $this->rppFixture($this->completePayload());

        $rppGenerator = \Mockery::mock(RppGenerator::class);
        $rppGenerator->shouldNotReceive('generate');

        (new GenerateRppDraft($draft))->handle(
            $rppGenerator,
            app(RppGenerationContextBuilder::class),
        );

        $artifact = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'lkpd',
            'status' => 'ready',
            'input_snapshot' => ['rpp_version_id' => $draft->versions()->value('id'), 'context' => []],
            'output_payload' => ['title' => 'LKPD tersimpan', 'sections' => []],
        ]);
        $artifact->versions()->create([
            'created_by' => $user->id,
            'version_number' => 1,
            'payload' => ['title' => 'LKPD tersimpan', 'sections' => []],
        ]);

        $artifactGenerator = \Mockery::mock(ArtifactGenerator::class);
        $artifactGenerator->shouldNotReceive('generate');

        (new GenerateArtifactDraft($artifact))->handle($artifactGenerator);

        $this->assertSame(1, $draft->versions()->count());
        $this->assertSame(1, $artifact->versions()->count());
        $this->assertSame('ready', $draft->fresh()->status);
        $this->assertSame('ready', $artifact->fresh()->status);
    }

    public function test_owner_can_queue_and_process_an_lkpd_artifact(): void
    {
        Queue::fake();
        [$user, $project, $draft, $version] = $this->rppFixture($this->completePayload());

        $this->actingAs($user)
            ->post(route('teaching-projects.artifacts.store', $project), [
                'artifact_type' => 'lkpd',
            ])
            ->assertRedirect(route('teaching-projects.show', $project).'?tab=artifacts');

        $artifact = GeneratedContent::query()
            ->where('teaching_project_id', $project->id)
            ->where('content_type', 'lkpd')
            ->firstOrFail();

        $this->assertSame('queued', $artifact->status);
        $this->assertSame($version->id, data_get($artifact->input_snapshot, 'rpp_version_id'));
        Queue::assertPushed(GenerateArtifactDraft::class);

        $this->app->bind(ArtifactGenerator::class, fn (): ArtifactGenerator => new class implements ArtifactGenerator
        {
            public function generate(string $artifactType, array $context, array $rppPayload): ArtifactGenerationOutput
            {
                return new ArtifactGenerationOutput('LKPD Teks Deskriptif', [
                    'petunjuk' => ['Baca instruksi.'],
                    'kegiatan' => ['Tulis deskripsi objek.'],
                ]);
            }
        });

        (new GenerateArtifactDraft($artifact))->handle(app(ArtifactGenerator::class));

        $artifact->refresh();
        $this->assertSame('ready', $artifact->status);
        $this->assertSame('LKPD Teks Deskriptif', data_get($artifact->output_payload, 'title'));
        $this->assertDatabaseHas('generated_content_versions', [
            'generated_content_id' => $artifact->id,
            'version_number' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('teaching-projects.show', $project).'?tab=artifacts')
            ->assertOk()
            ->assertSee('LKPD Teks Deskriptif')
            ->assertSee('Tulis deskripsi objek.');
    }

    public function test_other_user_cannot_run_alignment_for_a_project(): void
    {
        [$owner, $project] = $this->projectFixture();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->post(route('teaching-projects.alignment.run', $project))
            ->assertForbidden();

        $this->assertDatabaseCount('alignment_findings', 0);
        $this->assertNotSame($owner->id, $other->id);
    }

    /**
     * @return array{0: User, 1: TeachingProject, 2: GeneratedContent, 3: GeneratedContentVersion}
     */
    private function rppFixture(array $payload): array
    {
        [$user, $project] = $this->projectFixture();
        $draft = GeneratedContent::query()->create([
            'user_id' => $user->id,
            'teaching_project_id' => $project->id,
            'content_type' => 'rpp',
            'status' => 'ready',
            'output_payload' => $payload,
            'completed_at' => now(),
        ]);
        $version = $draft->versions()->create([
            'created_by' => $user->id,
            'version_number' => 1,
            'payload' => $payload,
            'change_summary' => 'Draft awal dari Gemini',
        ]);

        return [$user, $project, $draft, $version];
    }

    /** @return array{0: User, 1: TeachingProject} */
    private function projectFixture(): array
    {
        $user = User::factory()->create();
        $profile = $user->classProfiles()->create([
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

        return [$user, $user->teachingProjects()->create([
            'class_profile_id' => $profile->id,
            'title' => 'Menulis Teks Deskriptif',
            'topic' => 'Menulis teks deskriptif',
            'learning_outcomes' => 'Siswa memahami struktur teks deskriptif.',
            'learning_objectives' => 'Siswa dapat menulis teks deskriptif.',
            'duration_minutes' => 80,
            'teaching_model' => 'Problem Based Learning',
            'status' => 'draft',
        ])];
    }

    /** @return array<string, mixed> */
    private function completePayload(): array
    {
        return [
            'identity' => ['subject' => 'Bahasa Indonesia', 'grade' => 'X', 'topic' => 'Menulis teks deskriptif'],
            'learning_objectives' => ['Siswa dapat menulis teks deskriptif.'],
            'learning_activities' => ['opening' => ['Apersepsi'], 'core' => ['Menulis deskripsi'], 'closing' => ['Refleksi']],
            'assessment' => ['diagnostic' => [], 'formative' => ['Tanya jawab'], 'summative' => ['Tugas menulis']],
            'differentiation' => ['Pilihan objek'],
            'remedial' => ['Bimbingan terarah'],
            'enrichment' => ['Deskripsi tambahan'],
            'references' => ['Buku teks'],
        ];
    }

    /** @return array<string, mixed> */
    private function payloadWithGaps(): array
    {
        return [
            'identity' => ['subject' => 'Bahasa Indonesia', 'grade' => 'X', 'topic' => 'Topik berbeda'],
            'learning_objectives' => ['Siswa dapat membaca teks.'],
            'learning_activities' => ['opening' => ['Apersepsi'], 'core' => [], 'closing' => ['Refleksi']],
            'assessment' => ['diagnostic' => [], 'formative' => [], 'summative' => []],
        ];
    }
}
