<?php

namespace App\Data\Generation;

final readonly class RppGenerationInput
{
    /**
     * @param  array<string, mixed>  $classContext
     * @param  list<array<string, mixed>>  $sources
     */
    public function __construct(
        public string $title,
        public string $subject,
        public string $grade,
        public string $topic,
        public int $durationMinutes,
        public ?string $learningOutcomes,
        public string $learningObjectives,
        public array $classContext = [],
        public array $sources = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'project' => [
                'title' => $this->title,
                'subject' => $this->subject,
                'grade' => $this->grade,
                'topic' => $this->topic,
                'duration_minutes' => $this->durationMinutes,
            ],
            'curriculum' => [
                'learning_outcomes' => $this->learningOutcomes,
                'learning_objectives' => $this->learningObjectives,
            ],
            'class_context' => $this->classContext,
            'sources' => $this->sources,
        ];
    }
}
