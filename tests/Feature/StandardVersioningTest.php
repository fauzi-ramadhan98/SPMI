<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\QualityStandard;
use App\Models\StandardVersion;

class StandardVersioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_updating_standard_creates_version_snapshot_and_bumps_version(): void
    {
        $spmi = User::factory()->create();
        $spmi->syncRoles(['spmi']);
        $std = QualityStandard::create([
            'kode_standar' => 'S.01', 'name' => 'Standar Pendidikan',
            'type' => 'IKU', 'pernyataan_standar' => 'Versi 1', 'is_active' => true,
        ]);

        $this->actingAs($spmi)
            ->put('/admin/quality-standards/' . $std->id, [
                'kode_standar' => 'S.01',
                'pernyataan_standar' => 'Versi 2',
                'type' => 'IKU',
            ])
            ->assertRedirect();

        $std->refresh();
        $this->assertEquals(2, $std->version);

        $snap = StandardVersion::where('quality_standard_id', $std->id)->first();
        $this->assertNotNull($snap);
        $this->assertEquals(1, $snap->version);
        $this->assertEquals($spmi->id, $snap->changed_by);
        $this->assertEquals('Versi 1', $snap->snapshot['pernyataan_standar']);
    }
}