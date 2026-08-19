<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\QualityStandard;
use App\Models\ActivityLog;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_model_creation_is_logged(): void
    {
        $spmi = User::factory()->create();
        $spmi->syncRoles(['spmi']);
        $this->actingAs($spmi);

        QualityStandard::create([
            'kode_standar' => 'STD-001',
            'name' => 'Standar Pendidikan',
            'type' => 'IKU',
            'pernyataan_standar' => 'Pernyataan',
        ]);

        $log = ActivityLog::where('model_type', QualityStandard::class)->first();
        $this->assertNotNull($log);
        $this->assertEquals('created', $log->action);
        $this->assertEquals($spmi->id, $log->user_id);
    }

    public function test_admin_can_view_activity_logs(): void
    {
        $admin = User::factory()->create();
        $admin->syncRoles(['administrator']);
        $this->actingAs($admin)
            ->get('/admin/activity-logs')
            ->assertStatus(200)
            ->assertSee('Audit Trail');
    }
}