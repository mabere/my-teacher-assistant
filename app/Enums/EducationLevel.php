<?php

namespace App\Enums;

enum EducationLevel: string
{
    case PaudTk = 'paud_tk';
    case SdMi = 'sd_mi';
    case SmpMts = 'smp_mts';
    case SmaMa = 'sma_ma';
    case SmkMak = 'smk_mak';
    case Slb = 'slb';

    public function label(): string
    {
        return match ($this) {
            self::PaudTk => 'PAUD/TK',
            self::SdMi => 'SD/MI',
            self::SmpMts => 'SMP/MTs',
            self::SmaMa => 'SMA/MA',
            self::SmkMak => 'SMK/MAK',
            self::Slb => 'SLB',
        };
    }
}
