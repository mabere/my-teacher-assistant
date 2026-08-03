<?php

namespace App\Console\Commands;

use App\Contracts\RppGenerator;
use App\Data\Generation\RppGenerationInput;
use App\Models\TeachingProject;
use App\Services\AI\RppGenerationContextBuilder;
use Illuminate\Console\Command;
use Throwable;

class RunRppGenerationSpike extends Command
{
    protected $signature = 'ai:spike-rpp
        {--project= : ID proyek pembelajaran yang konteksnya akan dipakai}
        {--live : Memanggil Gemini sungguhan; tanpa opsi ini tidak ada request eksternal}
        {--json : Menampilkan output draft sebagai JSON}';

    protected $description = 'Menjalankan technical spike structured generation untuk draf RPP tanpa menyimpan hasil.';

    public function handle(RppGenerator $generator, RppGenerationContextBuilder $contextBuilder): int
    {
        try {
            $input = $this->inputFromOption($contextBuilder);

            $this->info('Structured generation spike');
            $this->line('Kontrak input valid: '.$input->title.' / '.$input->subject.' / '.$input->grade);
            $this->line('Sumber konteks: '.count($input->sources).' dokumen siap digunakan.');

            if (! $this->option('live')) {
                $this->warn('Dry-run: tidak ada request ke Gemini dan tidak ada data yang disimpan. Gunakan --live untuk smoke test nyata.');

                return self::SUCCESS;
            }

            $startedAt = microtime(true);
            $output = $generator->generate($input);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->info("Structured output valid. Durasi: {$durationMs} ms.");

            if ($this->option('json')) {
                $this->line(json_encode($output->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            } else {
                $this->line('Tujuan pembelajaran: '.count($output->learningObjectives));
                $this->line('Aktivitas inti: '.count($output->learningActivities['core']));
                $this->line('Asesmen formatif: '.count($output->assessment['formative']));
                $this->comment('Hasil masih berada di memory spike dan belum disimpan sebagai draft database.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Structured generation gagal: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    private function inputFromOption(RppGenerationContextBuilder $contextBuilder): RppGenerationInput
    {
        $projectId = $this->option('project');

        if ($projectId !== null) {
            $project = TeachingProject::query()->findOrFail((int) $projectId);

            return $contextBuilder->fromProject($project);
        }

        return new RppGenerationInput(
            title: 'Descriptive Text',
            subject: 'Bahasa Inggris',
            grade: 'VIII',
            topic: 'Describing Local Places',
            durationMinutes: 80,
            learningOutcomes: 'Peserta didik memahami fungsi sosial dan unsur kebahasaan teks deskriptif.',
            learningObjectives: 'Peserta didik dapat mengidentifikasi struktur dan menulis teks deskriptif sederhana.',
            classContext: [
                'student_count' => 28,
                'ability_profile' => 'mixed',
                'facilities' => 'Papan tulis dan proyektor.',
                'local_context' => 'Lingkungan sekitar sekolah.',
                'differentiation_needs' => 'Contoh bertingkat dan dukungan kosakata.',
            ],
            sources: [],
        );
    }
}
