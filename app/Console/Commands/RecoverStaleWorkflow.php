<?php

namespace App\Console\Commands;

use App\Models\GeneratedContent;
use App\Models\ProjectDocument;
use App\Services\Workflow\StaleProcessRecovery;
use Illuminate\Console\Command;

class RecoverStaleWorkflow extends Command
{
    protected $signature = 'workflow:recover-stale';

    protected $description = 'Pulihkan proses queue yang melewati batas waktu';

    public function handle(StaleProcessRecovery $recovery): int
    {
        $generatedCount = 0;
        GeneratedContent::query()
            ->whereIn('content_type', ['rpp', 'lkpd'])
            ->whereIn('status', ['queued', 'processing'])
            ->lazyById()
            ->each(function (GeneratedContent $content) use ($recovery, &$generatedCount): void {
                if ($recovery->recoverGeneratedContent($content) instanceof GeneratedContent) {
                    $generatedCount++;
                }
            });

        $documentCount = 0;
        ProjectDocument::query()
            ->whereIn('processing_status', ['pending', 'processing'])
            ->lazyById()
            ->each(function (ProjectDocument $document) use ($recovery, &$documentCount): void {
                if ($recovery->recoverDocument($document) instanceof ProjectDocument) {
                    $documentCount++;
                }
            });

        $this->info("Pemulihan selesai: {$generatedCount} generator dan {$documentCount} dokumen dijadwalkan ulang.");

        return self::SUCCESS;
    }
}
