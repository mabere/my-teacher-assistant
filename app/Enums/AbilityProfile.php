<?php

namespace App\Enums;

enum AbilityProfile: string
{
    case Foundational = 'foundational';
    case Mixed = 'mixed';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::Foundational => 'Dasar',
            self::Mixed => 'Beragam',
            self::Intermediate => 'Menengah',
            self::Advanced => 'Lanjutan',
        };
    }
}
