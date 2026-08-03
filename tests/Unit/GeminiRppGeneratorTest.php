<?php

namespace Tests\Unit;

use App\Data\Generation\RppGenerationInput;
use App\Models\AiProviderSetting;
use App\Services\AI\GeminiRppGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class GeminiRppGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_gemini_response_is_normalized_into_rpp_output(): void
    {
        config()->set('ai.gemini.api_key', 'test-gemini-key');
        Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($this->payload())]]]]],
        ])]);

        $output = app(GeminiRppGenerator::class)->generate($this->input());

        $this->assertSame(['Apersepsi'], $output->learningActivities['opening']);
        Http::assertSent(function ($request): bool {
            return $request->hasHeader('x-goog-api-key', 'test-gemini-key')
                && $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent'
                && $request['generationConfig']['responseMimeType'] === 'application/json'
                && $request['generationConfig']['responseSchema']['type'] === 'object';
        });
    }

    public function test_active_admin_setting_overrides_environment_fallback(): void
    {
        config()->set('ai.gemini.api_key', 'environment-key');
        $setting = AiProviderSetting::query()->create(['provider' => 'gemini', 'model' => 'gemini-test-model', 'base_url' => 'https://example.test/v1beta', 'is_active' => true]);
        $setting->setApiKey('database-key');
        $setting->save();
        Http::fake(['https://example.test/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($this->payload())]]]]]])]);

        app(GeminiRppGenerator::class)->generate($this->input());

        Http::assertSent(fn ($request): bool => $request->url() === 'https://example.test/v1beta/models/gemini-test-model:generateContent' && $request->hasHeader('x-goog-api-key', 'database-key'));
    }

    public function test_invalid_structured_output_is_rejected(): void
    {
        config()->set('ai.gemini.api_key', 'test-gemini-key');
        Http::fake(['https://generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode(['identity' => []])]]]]]])]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('learning_objectives');
        app(GeminiRppGenerator::class)->generate($this->input());
    }

    public function test_generator_requires_an_api_key(): void
    {
        config()->set('ai.gemini.api_key', null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY');
        app(GeminiRppGenerator::class)->generate($this->input());
    }

    public function test_inactive_admin_setting_does_not_fallback_to_environment(): void
    {
        config()->set('ai.gemini.api_key', 'environment-key');
        AiProviderSetting::query()->create([
            'provider' => 'gemini',
            'model' => 'gemini-test-model',
            'base_url' => 'https://example.test/v1beta',
            'is_active' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan');
        app(GeminiRppGenerator::class)->generate($this->input());
    }

    private function input(): RppGenerationInput
    {
        return new RppGenerationInput(title: 'Descriptive Text', subject: 'Bahasa Inggris', grade: 'VIII', topic: 'Descriptive Text', durationMinutes: 80, learningOutcomes: 'Memahami teks deskriptif.', learningObjectives: 'Siswa dapat mendeskripsikan objek.', classContext: ['student_count' => 28, 'ability_profile' => 'mixed'], sources: [['filename' => 'kurikulum.docx', 'text' => 'Dokumen sumber terproses.']]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['identity' => ['subject' => 'Bahasa Inggris', 'grade' => 'VIII'], 'learning_objectives' => ['Siswa dapat mendeskripsikan objek.'], 'learning_activities' => ['opening' => ['Apersepsi'], 'core' => ['Analisis'], 'closing' => ['Refleksi']], 'assessment' => ['diagnostic' => [], 'formative' => ['Tanya jawab'], 'summative' => ['Tugas']], 'differentiation' => [], 'remedial' => [], 'enrichment' => [], 'references' => ['Buku teks']];
    }
}
