<?php

namespace Tests\Feature;

use App\Models\AiProviderSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAiProviderSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_provider_settings(): void
    {
        $this->get(route('admin.ai-provider.edit'))->assertRedirect(route('login'));
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.ai-provider.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_save_encrypted_gemini_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $plainKey = 'test-gemini-key';

        $response = $this->actingAs($admin)->put(route('admin.ai-provider.update'), [
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'api_key' => $plainKey,
            'is_active' => '1',
        ]);

        $response->assertSessionHas('success');
        $setting = AiProviderSetting::query()->firstOrFail();

        $this->assertNotSame($plainKey, $setting->encrypted_api_key);
        $this->assertSame($plainKey, $setting->apiKey());
        $this->assertSame('****'.substr($plainKey, -4), $setting->api_key_hint);
        $this->assertTrue($setting->is_active);
    }

    public function test_admin_can_test_connection_and_remove_key(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $setting = AiProviderSetting::query()->create([
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'is_active' => true,
        ]);
        $setting->setApiKey('test-gemini-key');
        $setting->save();

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash' => Http::response(['name' => 'models/gemini-2.5-flash']),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.ai-provider.test'))
            ->assertSessionHas('success');

        $this->assertSame('success', $setting->fresh()->last_test_status);

        $this->actingAs($admin)
            ->delete(route('admin.ai-provider.key.destroy'))
            ->assertSessionHas('success');

        $this->assertNull($setting->fresh()->apiKey());
    }
}
