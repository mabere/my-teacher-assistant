<?php

namespace App\Services\Alignment;

use App\Models\GeneratedContentVersion;
use App\Models\TeachingProject;

class AlignmentChecker
{
    /**
     * @return list<array<string, mixed>>
     */
    public function check(TeachingProject $project, GeneratedContentVersion $version): array
    {
        $project->loadMissing('classProfile');

        $rawPayload = $version->getAttribute('payload');
        $payload = is_array($rawPayload) ? $rawPayload : [];
        $findings = [];

        $this->checkIdentity($project, $payload, $findings);

        $projectObjectives = $this->lines((string) $project->learning_objectives);
        $rppObjectives = $this->listAt($payload, 'learning_objectives');

        if ($rppObjectives === []) {
            $findings[] = $this->finding(
                code: 'objective.missing',
                severity: 'warning',
                category: 'Tujuan',
                title: 'Tujuan pembelajaran belum tersedia',
                detail: 'Draft RPP belum memiliki tujuan pembelajaran yang dapat dibandingkan dengan tujuan proyek.',
                recommendation: 'Tambahkan tujuan pembelajaran yang terukur pada editor RPP.',
            );
        } else {
            foreach ($projectObjectives as $index => $objective) {
                if (! $this->matchesAny($objective, $rppObjectives)) {
                    $findings[] = $this->finding(
                        code: 'objective.uncovered.'.($index + 1),
                        severity: 'warning',
                        category: 'Tujuan',
                        title: 'Tujuan proyek belum terlihat di RPP',
                        detail: 'Tujuan proyek berikut belum memiliki tujuan RPP yang cukup mirip: '.$objective,
                        recommendation: 'Tambahkan atau sesuaikan tujuan RPP agar seluruh tujuan proyek tercakup.',
                        metadata: ['project_objective' => $objective],
                    );
                }
            }
        }

        $coreActivities = $this->listAt($payload, 'learning_activities.core');

        if ($coreActivities === []) {
            $findings[] = $this->finding(
                code: 'activity.core.missing',
                severity: 'warning',
                category: 'Aktivitas',
                title: 'Aktivitas inti belum tersedia',
                detail: 'RPP belum memiliki aktivitas inti yang menunjukkan bagaimana tujuan akan dicapai.',
                recommendation: 'Tambahkan aktivitas inti yang menghubungkan tujuan, konteks kelas, dan sumber.',
            );
        }

        $assessments = array_merge(
            $this->listAt($payload, 'assessment.diagnostic'),
            $this->listAt($payload, 'assessment.formative'),
            $this->listAt($payload, 'assessment.summative'),
        );

        if ($assessments === []) {
            $findings[] = $this->finding(
                code: 'assessment.missing',
                severity: 'warning',
                category: 'Asesmen',
                title: 'Asesmen belum tersedia',
                detail: 'Belum ada asesmen diagnostik, formatif, atau sumatif yang dapat diperiksa.',
                recommendation: 'Tambahkan setidaknya satu asesmen yang mengukur tujuan pembelajaran.',
            );
        }

        if ($findings === []) {
            $findings[] = $this->finding(
                code: 'alignment.ready',
                severity: 'info',
                category: 'Ringkasan',
                title: 'Struktur utama RPP tampak selaras',
                detail: 'Tujuan proyek tercakup, aktivitas inti tersedia, dan asesmen sudah diisi.',
                recommendation: 'Guru tetap perlu memeriksa kesesuaian akademik dan konteks nyata kelas.',
            );
        }

        return $findings;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<array<string, mixed>>  $findings
     */
    private function checkIdentity(TeachingProject $project, array $payload, array &$findings): void
    {
        $profile = $project->classProfile;
        $checks = [
            'subject' => [
                'label' => 'mata pelajaran',
                'expected' => (string) $profile->subject,
                'actual' => (string) data_get($payload, 'identity.subject', ''),
            ],
            'grade' => [
                'label' => 'kelas',
                'expected' => (string) $profile->grade,
                'actual' => (string) data_get($payload, 'identity.grade', ''),
            ],
            'topic' => [
                'label' => 'topik',
                'expected' => (string) $project->topic,
                'actual' => (string) data_get($payload, 'identity.topic', ''),
            ],
        ];

        foreach ($checks as $key => $check) {
            if ($check['expected'] !== '' && ! $this->similar($check['expected'], $check['actual'])) {
                $findings[] = $this->finding(
                    code: 'identity.'.$key,
                    severity: 'warning',
                    category: 'Identitas',
                    title: ucfirst($check['label']).' perlu diperiksa',
                    detail: 'Konteks proyek menyimpan "'.$check['expected'].'" sedangkan draft RPP memuat "'.($check['actual'] ?: 'belum diisi').'".',
                    recommendation: 'Pastikan identitas draft mengikuti konteks proyek atau sesuaikan konteks proyek terlebih dahulu.',
                    metadata: ['expected' => $check['expected'], 'actual' => $check['actual']],
                );
            }
        }
    }

    /**
     * @param  list<string>  $items
     */
    private function matchesAny(string $value, array $items): bool
    {
        foreach ($items as $item) {
            if ($this->similar($value, $item)) {
                return true;
            }
        }

        return false;
    }

    private function similar(string $left, string $right): bool
    {
        $left = $this->normalize($left);
        $right = $this->normalize($right);

        if ($left === '' || $right === '') {
            return false;
        }

        if ($left === $right || str_contains($left, $right) || str_contains($right, $left)) {
            return true;
        }

        $leftTokens = $this->tokens($left);
        $rightTokens = $this->tokens($right);
        $intersection = count(array_intersect($leftTokens, $rightTokens));
        $threshold = min(count($leftTokens), count($rightTokens)) >= 2 ? 2 : 1;

        return $intersection >= $threshold;
    }

    /** @return list<string> */
    private function tokens(string $value): array
    {
        $stopWords = [
            'agar', 'akan', 'atau', 'bagi', 'dapat', 'dan', 'dari',
            'dengan', 'di', 'ke', 'menjadi', 'siswa', 'untuk', 'yang',
        ];

        return array_values(array_filter(
            explode(' ', $value),
            static fn (string $token): bool => mb_strlen($token) > 2
                && ! in_array($token, $stopWords, true),
        ));
    }

    private function normalize(string $value): string
    {
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($value));

        return trim((string) $normalized);
    }

    /** @return list<string> */
    private function lines(string $value): array
    {
        $lines = preg_split('/\R/u', trim($value)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
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

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>
     */
    private function finding(
        string $code,
        string $severity,
        string $category,
        string $title,
        string $detail,
        string $recommendation,
        ?array $metadata = null,
    ): array {
        return [
            'code' => $code,
            'severity' => $severity,
            'category' => $category,
            'title' => $title,
            'detail' => $detail,
            'recommendation' => $recommendation,
            'metadata' => $metadata,
        ];
    }
}
