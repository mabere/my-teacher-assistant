<?php

namespace App\Data;

final readonly class ExtractedDocument
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $text,
        public array $metadata = [],
    ) {}
}
