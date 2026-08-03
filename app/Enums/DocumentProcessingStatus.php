<?php

namespace App\Enums;

enum DocumentProcessingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu diproses',
            self::Processing => 'Sedang diproses',
            self::Ready => 'Siap digunakan',
            self::Failed => 'Gagal diproses',
        };
    }
}
