<?php

namespace App\Contracts;

use App\Data\Generation\ArtifactGenerationOutput;

interface ArtifactGenerator
{
    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $rppPayload
     */
    public function generate(string $artifactType, array $context, array $rppPayload): ArtifactGenerationOutput;
}
