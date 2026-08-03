<?php

namespace App\Contracts;

use App\Data\Generation\RppGenerationInput;
use App\Data\Generation\RppGenerationOutput;

interface RppGenerator
{
    public function generate(RppGenerationInput $input): RppGenerationOutput;
}
