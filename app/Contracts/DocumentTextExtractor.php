<?php

namespace App\Contracts;

use App\Data\ExtractedDocument;
use App\Models\ProjectDocument;

interface DocumentTextExtractor
{
    public function supports(ProjectDocument $document): bool;

    public function extract(ProjectDocument $document): ExtractedDocument;
}
