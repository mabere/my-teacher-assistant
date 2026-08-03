<?php

namespace App\Models;

use App\Enums\TeachingProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TeachingProject extends Model
{
    protected $fillable = [
        'user_id', 'class_profile_id', 'title', 'topic', 'learning_outcomes',
        'learning_objectives', 'duration_minutes', 'teaching_model', 'status',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'status' => TeachingProjectStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (TeachingProject $project): void {
            $project->documents()
                ->select('storage_path')
                ->get()
                ->each(fn (ProjectDocument $document): bool => Storage::disk('private')->delete($document->storage_path));
        });
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ClassProfile, $this> */
    public function classProfile(): BelongsTo
    {
        return $this->belongsTo(ClassProfile::class);
    }

    /** @return HasMany<ProjectDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    /** @return HasMany<GeneratedContent, $this> */
    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }

    public function latestRppDraft(): ?GeneratedContent
    {
        $draft = $this->generatedContents()
            ->where('content_type', 'rpp')
            ->latest('id')
            ->first();

        return $draft instanceof GeneratedContent ? $draft : null;
    }

    public function latestReadyRppDraft(): ?GeneratedContent
    {
        $draft = $this->latestRppDraft();

        return $draft !== null && $draft->status === 'ready' ? $draft : null;
    }

    /** @return HasMany<AlignmentFinding, $this> */
    public function alignmentFindings(): HasMany
    {
        return $this->hasMany(AlignmentFinding::class);
    }
}
