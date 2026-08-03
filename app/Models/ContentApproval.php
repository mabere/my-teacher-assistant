<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentApproval extends Model
{
    protected $fillable = [
        'generated_content_id',
        'generated_content_version_id',
        'approved_by',
        'checklist',
        'notes',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'encrypted:array',
            'approved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<GeneratedContent, $this> */
    public function generatedContent(): BelongsTo
    {
        return $this->belongsTo(GeneratedContent::class);
    }

    /** @return BelongsTo<GeneratedContentVersion, $this> */
    public function generatedContentVersion(): BelongsTo
    {
        return $this->belongsTo(GeneratedContentVersion::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
