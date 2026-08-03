<?php

namespace App\Services\Documents;

use App\Contracts\DocumentTextExtractor;
use App\Data\ExtractedDocument;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;

class DocxTextExtractor implements DocumentTextExtractor
{
    public function supports(ProjectDocument $document): bool
    {
        return strtolower((string) $document->extension) === 'docx';
    }

    public function extract(ProjectDocument $document): ExtractedDocument
    {
        if (! $this->supports($document)) {
            throw new RuntimeException('Extractor DOCX menerima dokumen dengan ekstensi yang tidak didukung.');
        }

        $path = Storage::disk('private')->path($document->storage_path);

        if (! is_file($path)) {
            throw new RuntimeException('Berkas DOCX tidak ditemukan pada private storage.');
        }

        try {
            $word = IOFactory::load($path);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Berkas DOCX tidak dapat dibaca: '.$exception->getMessage(), previous: $exception);
        }

        $lines = [];

        foreach ($word->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $this->collectElementText($element, $lines);
            }
        }

        $text = trim(implode("\n", array_values(array_filter(
            array_map(static fn (string $line): string => trim($line), $lines),
            static fn (string $line): bool => $line !== '',
        ))));

        return new ExtractedDocument($text, [
            'extractor' => self::class,
            'format' => 'docx',
            'paragraph_count' => count($lines),
            'character_count' => mb_strlen($text),
            'word_count' => $text === '' ? 0 : str_word_count($text),
        ]);
    }

    /** @param list<string> $lines */
    private function collectElementText(mixed $element, array &$lines): void
    {
        if (! is_object($element)) {
            return;
        }

        if ($element instanceof Table) {
            foreach ($element->getRows() as $row) {
                $cells = [];

                foreach ($row->getCells() as $cell) {
                    $cellLines = [];

                    foreach ($cell->getElements() as $cellElement) {
                        $this->collectElementText($cellElement, $cellLines);
                    }

                    $cells[] = implode(' ', $cellLines);
                }

                $lines[] = implode(' | ', $cells);
            }

            return;
        }

        if (method_exists($element, 'getText')) {
            $text = $element->getText();

            if (is_string($text)) {
                $lines[] = $text;
            }
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $this->collectElementText($child, $lines);
            }
        }
    }
}
