<?php

namespace App\Data\Generation;

use InvalidArgumentException;

final readonly class RppGenerationOutput
{
    /**
     * @param  array<string, mixed>  $identity
     * @param  array<int, string>  $learningObjectives
     * @param  array{opening: array<int, string>, core: array<int, string>, closing: array<int, string>}  $learningActivities
     * @param  array{diagnostic: array<int, string>, formative: array<int, string>, summative: array<int, string>}  $assessment
     * @param  array<int, string>  $differentiation
     * @param  array<int, string>  $remedial
     * @param  array<int, string>  $enrichment
     * @param  array<int, string>  $references
     */
    public function __construct(
        public array $identity,
        public array $learningObjectives,
        public array $learningActivities,
        public array $assessment,
        public array $differentiation = [],
        public array $remedial = [],
        public array $enrichment = [],
        public array $references = [],
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            identity: self::arrayValue($payload, 'identity'),
            learningObjectives: self::stringList($payload, 'learning_objectives'),
            learningActivities: [
                'opening' => self::nestedStringList($payload, 'learning_activities', 'opening'),
                'core' => self::nestedStringList($payload, 'learning_activities', 'core'),
                'closing' => self::nestedStringList($payload, 'learning_activities', 'closing'),
            ],
            assessment: [
                'diagnostic' => self::nestedStringList($payload, 'assessment', 'diagnostic'),
                'formative' => self::nestedStringList($payload, 'assessment', 'formative'),
                'summative' => self::nestedStringList($payload, 'assessment', 'summative'),
            ],
            differentiation: self::optionalStringList($payload, 'differentiation'),
            remedial: self::optionalStringList($payload, 'remedial'),
            enrichment: self::optionalStringList($payload, 'enrichment'),
            references: self::optionalStringList($payload, 'references'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'identity' => $this->identity,
            'learning_objectives' => $this->learningObjectives,
            'learning_activities' => $this->learningActivities,
            'assessment' => $this->assessment,
            'differentiation' => $this->differentiation,
            'remedial' => $this->remedial,
            'enrichment' => $this->enrichment,
            'references' => $this->references,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function arrayValue(array $payload, string $key): array
    {
        $value = $payload[$key] ?? null;
        if (! is_array($value)) {
            throw new InvalidArgumentException("RPP output field [{$key}] must be an object.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private static function stringList(array $payload, string $key): array
    {
        $value = $payload[$key] ?? null;
        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidArgumentException("RPP output field [{$key}] must be a list of strings.");
        }
        foreach ($value as $item) {
            if (! is_string($item)) {
                throw new InvalidArgumentException("RPP output field [{$key}] must be a list of strings.");
            }
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private static function optionalStringList(array $payload, string $key): array
    {
        return array_key_exists($key, $payload) ? self::stringList($payload, $key) : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private static function nestedStringList(array $payload, string $parent, string $key): array
    {
        return self::stringList(self::arrayValue($payload, $parent), $key);
    }
}
