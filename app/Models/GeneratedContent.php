<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratedContent extends Model
{
    protected $fillable = [
        'user_id', 'teaching_project_id', 'content_type', 'status',
        'input_snapshot', 'output_payload', 'provider', 'model',
        'error_message', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'input_snapshot' => 'encrypted:array',
            'output_payload' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<TeachingProject, $this> */
    public function teachingProject(): BelongsTo
    {
        return $this->belongsTo(TeachingProject::class);
    }

    /** @return HasMany<GeneratedContentVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(GeneratedContentVersion::class);
    }

    /** @return HasMany<ContentApproval, $this> */
    public function approvals(): HasMany
    {
        return $this->hasMany(ContentApproval::class);
    }
}
