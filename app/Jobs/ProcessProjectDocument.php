<?php

namespace App\Jobs;

use App\Contracts\DocumentTextExtractor;
use App\Enums\DocumentProcessingStatus;
use App\Models\ProjectDocument;
use App\Services\Documents\DocumentTextExtractorRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessProjectDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(public ProjectDocument $document) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('project-document:'.$this->document->getKey()))->expireAfter($this->timeout),
        ];
    }

    public function handle(): void
    {
        $document = $this->document->fresh();

        if (! $document instanceof ProjectDocument
            || $document->getRawOriginal('processing_status') !== DocumentProcessingStatus::Pending->value) {
            return;
        }

        $extractor = app()->bound(DocumentTextExtractor::class)
            ? app(DocumentTextExtractor::class)
            : app(DocumentTextExtractorRegistry::class);

        $document->update([
            'processing_status' => DocumentProcessingStatus::Processing,
            'processing_error' => null,
            'processing_started_at' => now(),
            'processed_at' => null,
        ]);

        try {
            if (! $extractor->supports($document)) {
                throw new \RuntimeException('Tidak ada extractor yang mendukung format dokumen ini.');
            }

            $extracted = $extractor->extract($document);

            $document->update([
                'extracted_text' => $extracted->text,
                'extraction_metadata' => $extracted->metadata,
                'processing_status' => DocumentProcessingStatus::Ready,
                'processing_error' => null,
                'processed_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $document->update([
                'processing_status' => DocumentProcessingStatus::Failed,
                'processing_error' => $exception->getMessage(),
                'processed_at' => now(),
            ]);

            throw $exception;
        }
    }
}
