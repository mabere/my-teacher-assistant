<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAiProviderSettingRequest;
use App\Models\AiProviderSetting;
use App\Services\AI\GeminiRppGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiProviderSettingController extends Controller
{
    public function edit(): View
    {
        $setting = AiProviderSetting::query()->firstOrCreate(
            ['provider' => 'gemini'],
            [
                'model' => config('ai.gemini.model', 'gemini-3.6-flash'),
                'base_url' => config('ai.gemini.base_url'),
                'is_active' => true,
            ],
        );

        return view('admin.ai-provider-settings.edit', compact('setting'));
    }

    public function update(UpdateAiProviderSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $setting = AiProviderSetting::query()->firstOrNew(['provider' => 'gemini']);
        $setting->fill([
            'model' => $data['model'],
            'base_url' => $data['base_url'] ?? config('ai.gemini.base_url'),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'last_test_status' => null,
            'last_test_error' => null,
        ]);

        if (filled($data['api_key'] ?? null)) {
            $setting->setApiKey($data['api_key']);
        }

        $setting->save();

        return back()->with('success', 'Pengaturan Gemini berhasil disimpan.');
    }

    public function test(): RedirectResponse
    {
        $setting = AiProviderSetting::query()->where('provider', 'gemini')->first();

        if (! $setting?->apiKey()) {
            return back()->with('error', 'API key Gemini belum disimpan.');
        }

        try {
            app(GeminiRppGenerator::class)->testConnection($setting);
            $setting->update([
                'last_tested_at' => now(),
                'last_test_status' => 'success',
                'last_test_error' => null,
            ]);

            return back()->with('success', 'Koneksi Gemini berhasil diuji.');
        } catch (\Throwable $exception) {
            report($exception);
            $setting->update([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
                'last_test_error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Koneksi Gemini gagal: '.$exception->getMessage());
        }
    }

    public function destroy(): RedirectResponse
    {
        $setting = AiProviderSetting::query()->where('provider', 'gemini')->first();

        if ($setting) {
            $setting->clearApiKey();
            $setting->last_test_status = null;
            $setting->last_test_error = null;
            $setting->last_tested_at = null;
            $setting->save();
        }

        return back()->with('success', 'API key Gemini telah dihapus.');
    }
}
