<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->syncRoles(['administrator']);
        return $user;
    }

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/admin/settings')
            ->assertStatus(200)
            ->assertSee('Konfigurasi Aplikasi');
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->adminUser())
            ->put('/admin/settings', [
                'institution_name' => 'STMIK Mardira Indonesia',
                'academic_year' => '2025/2026',
                'semester' => 'Ganjil',
            ])
            ->assertRedirect('/admin/settings');

        $this->assertDatabaseHas('settings', ['key' => 'institution_name', 'value' => 'STMIK Mardira Indonesia']);
    }
}