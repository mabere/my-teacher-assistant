<?php

namespace App\Jobs;

use App\Contracts\RppGenerator;
use App\Models\AiProviderSetting;
use App\Models\GeneratedContent;
use App\Models\TeachingProject;
use App\Services\AI\RppGenerationContextBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenerateRppDraft implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 180;

    public function __construct(public GeneratedContent $generatedContent) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('rpp-draft:'.$this->generatedContent->getKey()))->expireAfter($this->timeout),
        ];
    }

    public function handle(RppGenerator $generator, RppGenerationContextBuilder $contextBuilder): void
    {
        $content = $this->generatedContent->fresh(['teachingProject']);

        if ($content === null) {
            return;
        }

        if ((string) $content->getRawOriginal('status') !== 'queued') {
            return;
        }

        $project = $content->teachingProject;

        if (! $project instanceof TeachingProject) {
            throw new \RuntimeException('Proyek pembelajaran untuk draft RPP tidak ditemukan.');
        }

        $content->update([
            'status' => 'processing',
            'error_message' => null,
            'started_at' => now(),
            'completed_at' => null,
            'provider' => config('ai.provider', 'gemini'),
        ]);

        try {
            $input = $contextBuilder->fromProject($project);
            $output = $generator->generate($input);
            $setting = AiProviderSetting::query()->where('provider', 'gemini')->where('is_active', true)->first();
            $payload = $output->toArray();

            DB::transaction(function () use ($content, $input, $payload, $setting): void {
                $content->update([
                    'status' => 'ready',
                    'input_snapshot' => $input->toArray(),
                    'output_payload' => $payload,
                    'model' => $setting?->model ?: config('ai.gemini.model'),
                    'error_message' => null,
                    'completed_at' => now(),
                ]);

                $content->versions()->create([
                    'created_by' => $content->user_id,
                    'version_number' => 1,
                    'payload' => $payload,
                    'change_summary' => 'Draft awal dari Gemini',
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
                'error_message' => $exception?->getMessage() ?: 'Pemrosesan RPP gagal.',
                'completed_at' => now(),
            ]);
    }
}
