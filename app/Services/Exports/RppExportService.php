<?php

namespace App\Services\Exports;

use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RppExportService
{
    public function download(TeachingProject $project, GeneratedContentVersion $version, string $format): Response|BinaryFileResponse
    {
        return match (strtolower($format)) {
            'docx' => $this->docx($project, $version),
            'pdf' => $this->pdf($project, $version),
            default => throw new RuntimeException('Format export tidak didukung.'),
        };
    }

    private function docx(TeachingProject $project, GeneratedContentVersion $version): BinaryFileResponse
    {
        $rawPayload = $version->getAttribute('payload');
        $payload = is_array($rawPayload) ? $rawPayload : [];
        $word = new PhpWord;
        $word->setDefaultFontName('Aptos');
        $word->setDefaultFontSize(10);
        $section = $word->addSection();

        $section->addTitle($this->identity($payload, 'topic', $project->topic), 1);
        $section->addText('Proyek: '.$project->title);
        $section->addText('Versi RPP: '.$version->version_number);
        $section->addTextBreak();

        foreach ($this->sections($payload) as [$title, $items]) {
            $section->addTitle($title, 2);
            foreach ($items as $item) {
                $section->addListItem($item);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'rpp_');

        if ($path === false) {
            throw new RuntimeException('Berkas sementara export DOCX tidak dapat dibuat.');
        }

        IOFactory::createWriter($word, 'Word2007')->save($path);

        return response()
            ->download($path, $this->filename($project, $version, 'docx'))
            ->deleteFileAfterSend(true);
    }

    private function pdf(TeachingProject $project, GeneratedContentVersion $version): Response
    {
        $options = new Options;
        $options->setDefaultFont('DejaVu Sans');
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($project, $version), 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($project, $version, 'pdf').'"',
        ]);
    }

    private function html(TeachingProject $project, GeneratedContentVersion $version): string
    {
        $rawPayload = $version->getAttribute('payload');
        $payload = is_array($rawPayload) ? $rawPayload : [];
        $title = $this->escape($this->identity($payload, 'topic', $project->topic));
        $content = '<h1>'.$title.'</h1><p><strong>Proyek:</strong> '.$this->escape($project->title).'</p><p><strong>Versi RPP:</strong> '.$version->version_number.'</p>';

        foreach ($this->sections($payload) as [$heading, $items]) {
            $content .= '<h2>'.$this->escape($heading).'</h2><ul>';

            foreach ($items as $item) {
                $content .= '<li>'.$this->escape($item).'</li>';
            }

            $content .= '</ul>';
        }

        return '<!doctype html><html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;line-height:1.5;color:#172033}h1{color:#243b8f;font-size:22px;margin-bottom:8px}h2{color:#243b8f;font-size:15px;border-bottom:1px solid #d8deef;padding-bottom:4px;margin-top:20px}p{margin:4px 0}li{margin:4px 0}</style></head><body>'.$content.'</body></html>';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{0: string, 1: list<string>}>
     */
    private function sections(array $payload): array
    {
        return [
            ['Identitas', [
                'Mata pelajaran: '.$this->identity($payload, 'subject', '-'),
                'Kelas: '.$this->identity($payload, 'grade', '-'),
                'Topik: '.$this->identity($payload, 'topic', '-'),
            ]],
            ['Tujuan pembelajaran', $this->listAt($payload, 'learning_objectives')],
            ['Aktivitas pembuka', $this->listAt($payload, 'learning_activities.opening')],
            ['Aktivitas inti', $this->listAt($payload, 'learning_activities.core')],
            ['Aktivitas penutup', $this->listAt($payload, 'learning_activities.closing')],
            ['Asesmen diagnostik', $this->listAt($payload, 'assessment.diagnostic')],
            ['Asesmen formatif', $this->listAt($payload, 'assessment.formative')],
            ['Asesmen sumatif', $this->listAt($payload, 'assessment.summative')],
            ['Diferensiasi', $this->listAt($payload, 'differentiation')],
            ['Remedial', $this->listAt($payload, 'remedial')],
            ['Pengayaan', $this->listAt($payload, 'enrichment')],
            ['Referensi', $this->listAt($payload, 'references')],
        ];
    }

    /** @param array<string, mixed> $payload */
    private function identity(array $payload, string $key, string $fallback): string
    {
        $value = data_get($payload, 'identity.'.$key, $fallback);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function listAt(array $payload, string $path): array
    {
        $value = data_get($payload, $path, []);

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn (mixed $item): string => is_string($item) ? trim($item) : '', $value),
            static fn (string $item): bool => $item !== '',
        ));
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function filename(TeachingProject $project, GeneratedContentVersion $version, string $extension): string
    {
        return Str::slug($project->title).'-rpp-v'.$version->version_number.'.'.$extension;
    }
}
