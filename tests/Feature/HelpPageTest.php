<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_help_page(): void
    {
        $this->get('/help')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_read_real_workflow_guidance(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('help.index'))
            ->assertOk()
            ->assertSee('Panduan ruang kerja')
            ->assertSee('Enam tahap yang saling terhubung')
            ->assertSee('Buat profil kelas')
            ->assertSee('Generate draft RPP')
            ->assertSee('Periksa dan edit RPP')
            ->assertSee('Alignment, approval, dan export')
            ->assertSee('6 tahap tersedia')
            ->assertSee('Retrieval embedding/pgvector')
            ->assertSee(route('teaching-projects.create'))
            ->assertSee(route('teaching-projects.index'))
            ->assertSee('Panduan');
    }
}
