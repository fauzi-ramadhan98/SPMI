<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->syncRoles([$role]);
        return $user;
    }

    public function test_admin_sees_system_dashboard(): void
    {
        $this->actingAs($this->userWithRole('administrator'))
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Sistem');
    }

    public function test_spmi_sees_institusi_dashboard(): void
    {
        $this->actingAs($this->userWithRole('spmi'))
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Mutu Institusi');
    }

    public function test_auditor_sees_auditor_dashboard(): void
    {
        $this->actingAs($this->userWithRole('auditor'))
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Auditor');
    }

    public function test_prodi_sees_kinerja_dashboard(): void
    {
        $this->actingAs($this->userWithRole('prodi'))
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Dashboard Kinerja');
    }

    public function test_pimpinan_sees_executive_dashboard(): void
    {
        $this->actingAs($this->userWithRole('pimpinan'))
            ->get('/admin/dashboard')
            ->assertStatus(200)
            ->assertSee('Executive Dashboard');
    }
}