<?php

namespace App\Jobs;

use App\Contracts\ArtifactGenerator;
use App\Models\AiProviderSetting;
use App\Models\GeneratedContent;
use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class GenerateArtifactDraft implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 180;

    public function __construct(public GeneratedContent $generatedContent) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('artifact:'.$this->generatedContent->getKey()))->expireAfter($this->timeout),
        ];
    }

    public function handle(ArtifactGenerator $generator): void
    {
        $content = $this->generatedContent->fresh(['teachingProject']);

        if (! $content instanceof GeneratedContent) {
            return;
        }

        if ((string) $content->getRawOriginal('status') !== 'queued') {
            return;
        }

        $project = $content->teachingProject;

        if (! $project instanceof TeachingProject) {
            throw new RuntimeException('Proyek pembelajaran untuk artifact tidak ditemukan.');
        }

        $snapshot = $content->input_snapshot ?? [];
        $versionId = data_get($snapshot, 'rpp_version_id');
        $context = data_get($snapshot, 'context', []);
        $version = GeneratedContentVersion::query()->find($versionId);

        if (! $version instanceof GeneratedContentVersion || ! is_array($context)) {
            throw new RuntimeException('Snapshot artifact tidak memiliki versi RPP atau konteks yang valid.');
        }

        $content->update([
            'status' => 'processing',
            'error_message' => null,
            'started_at' => now(),
            'completed_at' => null,
        ]);

        $rawPayload = $version->getAttribute('payload');
        $rppPayload = is_array($rawPayload) ? $rawPayload : [];

        try {
            $output = $generator->generate(
                (string) $content->content_type,
                $context,
                $rppPayload,
            );
            $setting = AiProviderSetting::query()
                ->where('provider', 'gemini')
                ->where('is_active', true)
                ->first();
            $payload = $output->toArray();

            DB::transaction(function () use ($content, $payload, $setting): void {
                $content->update([
                    'status' => 'ready',
                    'output_payload' => $payload,
                    'provider' => 'gemini',
                    'model' => $setting?->model ?: config('ai.gemini.model'),
                    'error_message' => null,
                    'completed_at' => now(),
                ]);

                $content->versions()->create([
                    'created_by' => $content->user_id,
                    'version_number' => 1,
                    'payload' => $payload,
                    'change_summary' => 'Draft awal LKPD dari Gemini',
                ]);
            });
        } catch (Throwable $exception) {
            $content->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->generatedContent->newQuery()->whereKey($this->generatedContent->getKey())
            ->whereIn('status', ['queued', 'processing'])->update([
                'status' => 'failed',
                'error_message' => $exception?->getMessage() ?: 'Pemrosesan artifact gagal.',
                'completed_at' => now(),
            ]);
    }
}
