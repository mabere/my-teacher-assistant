<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GeneratedContentVersion extends Model
{
    protected $fillable = [
        'generated_content_id',
        'created_by',
        'version_number',
        'payload',
        'change_summary',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'payload' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<GeneratedContent, $this> */
    public function generatedContent(): BelongsTo
    {
        return $this->belongsTo(GeneratedContent::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<AlignmentFinding, $this> */
    public function alignmentFindings(): HasMany
    {
        return $this->hasMany(AlignmentFinding::class);
    }

    /** @return HasOne<ContentApproval, $this> */
    public function approval(): HasOne
    {
        return $this->hasOne(ContentApproval::class);
    }
}
