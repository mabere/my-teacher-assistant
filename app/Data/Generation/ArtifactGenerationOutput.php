<?php

namespace App\Data\Generation;

use InvalidArgumentException;

final readonly class ArtifactGenerationOutput
{
    /**
     * @param  array<string, list<string>>  $sections
     */
    public function __construct(
        public string $title,
        public array $sections,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $title = $payload['title'] ?? null;
        $sections = $payload['sections'] ?? null;

        if (! is_string($title) || trim($title) === '') {
            throw new InvalidArgumentException('Artifact output field [title] must be a non-empty string.');
        }

        if (! is_array($sections)) {
            throw new InvalidArgumentException('Artifact output field [sections] must be an object.');
        }

        $normalized = [];

        foreach ($sections as $key => $items) {
            if (! is_string($key) || ! is_array($items)) {
                throw new InvalidArgumentException('Artifact output sections must contain lists of strings.');
            }

            $normalized[$key] = [];

            foreach ($items as $item) {
                if (! is_string($item)) {
                    throw new InvalidArgumentException('Artifact output sections must contain lists of strings.');
                }

                $normalized[$key][] = $item;
            }
        }

        return new self(trim($title), $normalized);
    }

    /** @return array{title: string, sections: array<string, list<string>>} */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'sections' => $this->sections,
        ];
    }
}
