<?php

namespace App\Services\Documents;

use App\Contracts\DocumentTextExtractor;
use App\Data\ExtractedDocument;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\PdfToText\Pdf;

class PdfTextExtractor implements DocumentTextExtractor
{
    public function supports(ProjectDocument $document): bool
    {
        return strtolower((string) $document->extension) === 'pdf';
    }

    public function extract(ProjectDocument $document): ExtractedDocument
    {
        if (! $this->supports($document)) {
            throw new RuntimeException('Extractor PDF menerima dokumen dengan ekstensi yang tidak didukung.');
        }

        $path = Storage::disk('private')->path($document->storage_path);

        if (! is_file($path)) {
            throw new RuntimeException('Berkas PDF tidak ditemukan pada private storage.');
        }

        $binary = config('documents.pdf_to_text_binary');
        $binary = is_string($binary) && $binary !== '' ? $binary : null;

        try {
            $text = (new Pdf($binary))
                ->setPdf($path)
                ->setOptions(['layout'])
                ->setTimeout(60)
                ->text();
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                'Ekstraksi PDF gagal. Pastikan binary pdftotext/Poppler tersedia dan PDF dapat dibaca: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        return new ExtractedDocument($text, [
            'extractor' => self::class,
            'format' => 'pdf',
            'character_count' => mb_strlen($text),
            'word_count' => $text === '' ? 0 : str_word_count($text),
        ]);
    }
}
