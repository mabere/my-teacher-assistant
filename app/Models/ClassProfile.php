<?php

namespace App\Models;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassProfile extends Model
{
    protected $fillable = [
        'user_id', 'name', 'education_level', 'grade', 'subject', 'student_count',
        'ability_profile', 'facilities', 'local_context', 'differentiation_needs',
    ];

    protected function casts(): array
    {
        return [
            'education_level' => EducationLevel::class,
            'ability_profile' => AbilityProfile::class,
            'student_count' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<TeachingProject, $this> */
    public function teachingProjects(): HasMany
    {
        return $this->hasMany(TeachingProject::class);
    }
}
