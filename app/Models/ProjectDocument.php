<?php

namespace App\Models;

use App\Enums\DocumentProcessingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocument extends Model
{
    protected $fillable = [
        'user_id', 'teaching_project_id', 'original_filename', 'storage_path',
        'mime_type', 'extension', 'size_bytes', 'checksum', 'extracted_text',
        'extraction_metadata', 'processing_status', 'processing_error', 'processing_started_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'extraction_metadata' => 'array',
            'processing_status' => DocumentProcessingStatus::class,
            'processing_started_at' => 'datetime',
            'processed_at' => 'datetime',
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
}
