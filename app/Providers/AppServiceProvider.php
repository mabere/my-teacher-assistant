<?php

namespace App\Providers;

use App\Contracts\ArtifactGenerator;
use App\Contracts\RppGenerator;
use App\Services\AI\GeminiArtifactGenerator;
use App\Services\AI\GeminiRppGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArtifactGenerator::class, fn (): ArtifactGenerator => app(GeminiArtifactGenerator::class));

        $this->app->bind(RppGenerator::class, function (): RppGenerator {
            return match (config('ai.provider', 'gemini')) {
                'gemini' => app(GeminiRppGenerator::class),
                default => throw new RuntimeException('AI provider belum didukung: '.config('ai.provider')),
            };
        });
    }

    public function boot(): void
    {
        $this->configureDefaults();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
