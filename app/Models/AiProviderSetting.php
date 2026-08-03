<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiProviderSetting extends Model
{
    protected $fillable = [
        'provider',
        'model',
        'base_url',
        'is_active',
        'last_tested_at',
        'last_test_status',
        'last_test_error',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    public function setApiKey(?string $apiKey): void
    {
        $apiKey = $apiKey !== null ? trim($apiKey) : null;

        if ($apiKey === null || $apiKey === '') {
            return;
        }

        $this->encrypted_api_key = Crypt::encryptString($apiKey);
        $this->api_key_hint = '****'.substr($apiKey, -4);
    }

    public function clearApiKey(): void
    {
        $this->encrypted_api_key = null;
        $this->api_key_hint = null;
    }

    public function apiKey(): ?string
    {
        if (! is_string($this->encrypted_api_key) || $this->encrypted_api_key === '') {
            return null;
        }

        return Crypt::decryptString($this->encrypted_api_key);
    }
}
