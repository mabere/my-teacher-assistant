<?php

namespace App\Enums;

enum TeachingProjectStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Archived => 'Diarsipkan',
        };
    }
}
