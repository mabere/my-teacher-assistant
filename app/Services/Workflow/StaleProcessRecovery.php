<?php

namespace App\Services\Workflow;

use App\Enums\DocumentProcessingStatus;
use App\Jobs\GenerateArtifactDraft;
use App\Jobs\GenerateRppDraft;
use App\Jobs\ProcessProjectDocument;
use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class StaleProcessRecovery
{
    public function generatedContentIsStale(GeneratedContent $content, ?CarbonImmutable $now = null): bool
    {
        $status = (string) $content->getRawOriginal('status');

        if (! in_array($status, ['queued', 'processing'], true)) {
            return false;
        }

        $reference = $content->started_at ?? $content->created_at;

        return $this->isOlderThanThreshold($reference, $now);
    }

    public function documentIsStale(ProjectDocument $document, ?CarbonImmutable $now = null): bool
    {
        $status = (string) $document->getRawOriginal('processing_status');

        if (! in_array($status, [
            DocumentProcessingStatus::Pending->value,
            DocumentProcessingStatus::Processing->value,
        ], true)) {
            return false;
        }

        $reference = $document->processing_started_at ?? $document->created_at;

        return $this->isOlderThanThreshold($reference, $now);
    }

    public function recoverGeneratedContent(GeneratedContent $content): ?GeneratedContent
    {
        $recovered = DB::transaction(function () use ($content): ?GeneratedContent {
            $locked = GeneratedContent::query()->lockForUpdate()->find($content->id);

            if (! $locked instanceof GeneratedContent || ! $this->generatedContentIsStale($locked)) {
                return null;
            }

            $locked->update([
                'status' => 'queued',
                'output_payload' => null,
                'error_message' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);

            return $locked->fresh();
        });

        if (! $recovered instanceof GeneratedContent) {
            return null;
        }

        match ($recovered->content_type) {
            'rpp' => GenerateRppDraft::dispatch($recovered),
            'lkpd' => GenerateArtifactDraft::dispatch($recovered),
            default => null,
        };

        return $recovered;
    }

    public function recoverDocument(ProjectDocument $document): ?ProjectDocument
    {
        $recovered = DB::transaction(function () use ($document): ?ProjectDocument {
            $locked = ProjectDocument::query()->lockForUpdate()->find($document->id);

            if (! $locked instanceof ProjectDocument || ! $this->documentIsStale($locked)) {
                return null;
            }

            $locked->update([
                'processing_status' => DocumentProcessingStatus::Pending,
                'processing_error' => null,
                'processing_started_at' => null,
                'processed_at' => null,
            ]);

            return $locked->fresh();
        });

        if (! $recovered instanceof ProjectDocument) {
            return null;
        }

        ProcessProjectDocument::dispatch($recovered);

        return $recovered;
    }

    private function isOlderThanThreshold(mixed $reference, ?CarbonImmutable $now): bool
    {
        if (! $reference instanceof CarbonInterface) {
            return false;
        }

        return $reference->lte(($now ?? CarbonImmutable::now())->subMinutes($this->thresholdMinutes()));
    }

    private function thresholdMinutes(): int
    {
        return (int) config('workflow.stale_after_minutes', 10);
    }
}
