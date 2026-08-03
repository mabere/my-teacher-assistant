<?php

namespace App\Services\Documents;

use App\Contracts\DocumentTextExtractor;
use App\Data\ExtractedDocument;
use App\Models\ProjectDocument;
use RuntimeException;

class DocumentTextExtractorRegistry implements DocumentTextExtractor
{
    public function __construct(
        private readonly DocxTextExtractor $docx,
        private readonly PdfTextExtractor $pdf,
    ) {}

    public function supports(ProjectDocument $document): bool
    {
        return $this->extractorFor($document) !== null;
    }

    public function extract(ProjectDocument $document): ExtractedDocument
    {
        $extractor = $this->extractorFor($document);

        if ($extractor === null) {
            throw new RuntimeException('Tidak ada extractor yang mendukung format dokumen ini.');
        }

        return $extractor->extract($document);
    }

    private function extractorFor(ProjectDocument $document): ?DocumentTextExtractor
    {
        foreach ([$this->docx, $this->pdf] as $extractor) {
            if ($extractor->supports($document)) {
                return $extractor;
            }
        }

        return null;
    }
}
