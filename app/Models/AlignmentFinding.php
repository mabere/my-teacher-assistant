<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlignmentFinding extends Model
{
    protected $fillable = [
        'teaching_project_id',
        'generated_content_version_id',
        'code',
        'severity',
        'category',
        'title',
        'detail',
        'recommendation',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<TeachingProject, $this> */
    public function teachingProject(): BelongsTo
    {
        return $this->belongsTo(TeachingProject::class);
    }

    /** @return BelongsTo<GeneratedContentVersion, $this> */
    public function generatedContentVersion(): BelongsTo
    {
        return $this->belongsTo(GeneratedContentVersion::class);
    }
}
