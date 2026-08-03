<?php

namespace App\Services\AI;

use App\Contracts\ArtifactGenerator;
use App\Data\Generation\ArtifactGenerationOutput;
use App\Models\AiProviderSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiArtifactGenerator implements ArtifactGenerator
{
    public function generate(string $artifactType, array $context, array $rppPayload): ArtifactGenerationOutput
    {
        if ($artifactType !== 'lkpd') {
            throw new RuntimeException('Artifact belum didukung: '.$artifactType);
        }

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
        $response = Http::asJson()
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout((int) config('ai.gemini.timeout', 90))
            ->post($baseUrl.'/models/'.rawurlencode($model).':generateContent', [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $this->prompt($artifactType, $context, $rppPayload)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->schema(),
                ],
            ]);

        $response->throw();

        return ArtifactGenerationOutput::fromArray($this->decodeResponse($response));
    }

    /** @return array<string, mixed> */
    private function decodeResponse(Response $response): array
    {
        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Respons Gemini tidak berisi structured output artifact.');
        }

        $payload = json_decode($text, true);

        if (! is_array($payload)) {
            throw new RuntimeException('Structured output artifact bukan JSON object yang valid.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $rppPayload
     */
    private function prompt(string $artifactType, array $context, array $rppPayload): string
    {
        return implode("\n", [
            'Anda adalah asisten guru Indonesia yang membantu mengembangkan perangkat pembelajaran.',
            'Buat LKPD yang dapat diedit guru dari konteks proyek dan draft RPP berikut.',
            'Jangan mengubah tujuan pembelajaran secara diam-diam; tandai bagian yang perlu diverifikasi guru.',
            'Hasilkan instruksi yang jelas, aktivitas siswa yang realistis, lembar kerja, dan refleksi.',
            'Jenis artifact: '.$artifactType,
            'Konteks proyek: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'Draft RPP: '.json_encode($rppPayload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $stringList = ['type' => 'array', 'items' => ['type' => 'string']];

        return [
            'type' => 'object',
            'required' => ['title', 'sections'],
            'properties' => [
                'title' => ['type' => 'string'],
                'sections' => [
                    'type' => 'object',
                    'properties' => [
                        'petunjuk' => $stringList,
                        'tujuan' => $stringList,
                        'kegiatan' => $stringList,
                        'lembar_kerja' => $stringList,
                        'refleksi' => $stringList,
                    ],
                ],
            ],
        ];
    }
}
