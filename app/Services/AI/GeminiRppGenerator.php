<?php

namespace App\Services\AI;

use App\Contracts\RppGenerator;
use App\Data\Generation\RppGenerationInput;
use App\Data\Generation\RppGenerationOutput;
use App\Models\AiProviderSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiRppGenerator implements RppGenerator
{
    public function generate(RppGenerationInput $input): RppGenerationOutput
    {
        $setting = AiProviderSetting::query()
            ->where('provider', 'gemini')
            ->first();

        if ($setting && ! $setting->is_active) {
            throw new RuntimeException('Provider Gemini sedang dinonaktifkan oleh administrator.');
        }
        $apiKey = $setting?->apiKey() ?? config('ai.gemini.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi melalui pengaturan admin atau environment.');
        }

        $model = $setting?->model ?: (string) config('ai.gemini.model', 'gemini-3.6-flash');
        $baseUrl = rtrim($setting?->base_url ?: (string) config('ai.gemini.base_url'), '/');

        return $this->generateWith($apiKey, $model, $baseUrl, $input);
    }

    public function testConnection(AiProviderSetting $setting): void
    {
        $apiKey = $setting->apiKey();

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('API key Gemini belum tersedia.');
        }

        $baseUrl = rtrim($setting->base_url ?: (string) config('ai.gemini.base_url'), '/');
        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout((int) config('ai.gemini.timeout', 90))
            ->get($baseUrl.'/models/'.rawurlencode($setting->model));

        $response->throw();
    }

    private function generateWith(string $apiKey, string $model, string $baseUrl, RppGenerationInput $input): RppGenerationOutput
    {
        $response = Http::asJson()
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout((int) config('ai.gemini.timeout', 90))
            ->post($baseUrl.'/models/'.rawurlencode($model).':generateContent', [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $this->prompt($input)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->schema(),
                ],
            ]);

        $response->throw();

        return RppGenerationOutput::fromArray($this->decodeResponse($response));
    }

    /** @return array<string, mixed> */
    private function decodeResponse(Response $response): array
    {
        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Respons Gemini tidak berisi structured output.');
        }

        $payload = json_decode($text, true);

        if (! is_array($payload)) {
            throw new RuntimeException('Structured output Gemini bukan JSON object yang valid.');
        }

        return $payload;
    }

    private function prompt(RppGenerationInput $input): string
    {
        return implode("\n", [
            'Anda adalah asisten perencanaan pembelajaran untuk guru Indonesia.',
            'Susun draf RPP/modul ajar berdasarkan konteks berikut.',
            'Gunakan hanya informasi sumber yang diberikan sebagai dasar; tandai bagian yang perlu diverifikasi guru.',
            'Pertahankan keputusan profesional guru sebagai keputusan akhir.',
            json_encode($input->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $stringList = ['type' => 'array', 'items' => ['type' => 'string']];

        return [
            'type' => 'object',
            'required' => ['identity', 'learning_objectives', 'learning_activities', 'assessment'],
            'properties' => [
                'identity' => ['type' => 'object'],
                'learning_objectives' => $stringList,
                'learning_activities' => ['type' => 'object', 'required' => ['opening', 'core', 'closing'], 'properties' => ['opening' => $stringList, 'core' => $stringList, 'closing' => $stringList]],
                'assessment' => ['type' => 'object', 'required' => ['diagnostic', 'formative', 'summative'], 'properties' => ['diagnostic' => $stringList, 'formative' => $stringList, 'summative' => $stringList]],
                'differentiation' => $stringList,
                'remedial' => $stringList,
                'enrichment' => $stringList,
                'references' => $stringList,
            ],
        ];
    }
}
