<?php

namespace App\Services\AI;

use App\Data\Generation\RppGenerationInput;
use App\Enums\AbilityProfile;
use App\Enums\DocumentProcessingStatus;
use App\Models\TeachingProject;
use Illuminate\Support\Str;
use RuntimeException;

class RppGenerationContextBuilder
{
    public function fromProject(TeachingProject $project): RppGenerationInput
    {
        $project->loadMissing(['classProfile', 'documents']);
        $classProfile = $project->classProfile;

        if ($classProfile === null) {
            throw new RuntimeException('Profil kelas proyek belum tersedia.');
        }

        $sources = $project->documents
            ->filter(function ($document): bool {
                $status = $document->getAttribute('processing_status');
                $isReady = $status instanceof DocumentProcessingStatus
                    ? $status === DocumentProcessingStatus::Ready
                    : DocumentProcessingStatus::tryFrom((string) $status) === DocumentProcessingStatus::Ready;

                return $isReady && filled($document->getAttribute('extracted_text'));
            })
            ->map(fn ($document): array => [
                'filename' => (string) $document->getAttribute('original_filename'),
                'text' => Str::limit((string) $document->getAttribute('extracted_text'), 12000, '...'),
            ])
            ->values()
            ->all();

        $abilityProfile = $classProfile->getAttribute('ability_profile');
        $abilityValue = $abilityProfile instanceof AbilityProfile
            ? $abilityProfile->value
            : AbilityProfile::tryFrom((string) $abilityProfile)?->value;

        return new RppGenerationInput(
            title: (string) $project->getAttribute('title'),
            subject: (string) $classProfile->getAttribute('subject'),
            grade: (string) $classProfile->getAttribute('grade'),
            topic: (string) $project->getAttribute('topic'),
            durationMinutes: (int) $project->getAttribute('duration_minutes'),
            learningOutcomes: $project->getAttribute('learning_outcomes'),
            learningObjectives: (string) $project->getAttribute('learning_objectives'),
            classContext: [
                'student_count' => $classProfile->getAttribute('student_count'),
                'ability_profile' => $abilityValue,
                'facilities' => $classProfile->getAttribute('facilities'),
                'local_context' => $classProfile->getAttribute('local_context'),
                'differentiation_needs' => $classProfile->getAttribute('differentiation_needs'),
                'teaching_model' => $project->getAttribute('teaching_model'),
            ],
            sources: array_values($sources),
        );
    }
}
