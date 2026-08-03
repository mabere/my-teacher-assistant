<?php

namespace Tests\Unit;

use App\Data\Generation\RppGenerationInput;
use App\Data\Generation\RppGenerationOutput;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RppGenerationContractTest extends TestCase
{
    public function test_input_serializes_project_context_for_a_provider_adapter(): void
    {
        $input = new RppGenerationInput(
            title: 'Descriptive Text',
            subject: 'Bahasa Inggris',
            grade: 'VIII',
            topic: 'Descriptive Text',
            durationMinutes: 80,
            learningOutcomes: 'Memahami teks deskriptif.',
            learningObjectives: 'Siswa dapat mendeskripsikan objek.',
            classContext: ['student_count' => 28, 'ability_profile' => 'mixed'],
            sources: [['filename' => 'buku.docx']],
        );

        $this->assertSame('Bahasa Inggris', $input->toArray()['project']['subject']);
        $this->assertSame(28, $input->toArray()['class_context']['student_count']);
    }

    public function test_output_normalizes_a_valid_structured_payload(): void
    {
        $output = RppGenerationOutput::fromArray($this->validPayload());

        $this->assertSame(['Apersepsi'], $output->learningActivities['opening']);
        $this->assertSame($this->validPayload(), $output->toArray());
    }

    public function test_output_rejects_missing_required_structure(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RppGenerationOutput::fromArray(['identity' => []]);
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'identity' => ['subject' => 'Bahasa Inggris', 'grade' => 'VIII'],
            'learning_objectives' => ['Siswa dapat mendeskripsikan objek.'],
            'learning_activities' => [
                'opening' => ['Apersepsi'],
                'core' => ['Menganalisis contoh teks'],
                'closing' => ['Refleksi'],
            ],
            'assessment' => [
                'diagnostic' => [],
                'formative' => ['Tanya jawab'],
                'summative' => ['Tugas menulis'],
            ],
            'differentiation' => [],
            'remedial' => [],
            'enrichment' => [],
            'references' => ['Buku Bahasa Inggris kelas VIII'],
        ];
    }
}
