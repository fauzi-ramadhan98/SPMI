<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\QualityStandard;

class QSUpdateReproTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function spmi(): User
    {
        $u = User::factory()->create();
        $u->syncRoles(['spmi']);
        return $u;
    }

    public function test_edit_page_loads_for_spmi(): void
    {
        $std = QualityStandard::create([
            'kode_standar' => 'S.01',
            'name' => 'Standar Pendidikan',
            'type' => 'IKU',
            'pernyataan_standar' => 'Pernyataan A',
            'is_active' => true,
        ]);

        $this->actingAs($this->spmi())
            ->get('/admin/quality-standards/' . $std->id . '/edit')
            ->assertStatus(200)
            ->assertSee('Edit Standar Mutu');
    }

    public function test_update_persists_indicators_and_name(): void
    {
        $std = QualityStandard::create([
            'kode_standar' => 'S.01',
            'name' => 'Standar Pendidikan',
            'type' => 'IKU',
            'pernyataan_standar' => 'Pernyataan A',
            'is_active' => true,
        ]);

        $this->actingAs($this->spmi())
            ->patch('/admin/quality-standards/' . $std->id, [
                'kode_standar' => 'S.01',
                'name' => 'Nama Baru',
                'pernyataan_standar' => 'Pernyataan B',
                'rujukan' => 'Permendikbud',
                'description' => '',
                'iku' => [['text' => 'Indikator Utama A', 'target' => '80%']],
                'ikt' => [['text' => 'Indikator Tambahan B', 'target' => '']],
                'is_active' => '1',
            ])
            ->assertRedirect();

        $std->refresh();
        $this->assertEquals('Nama Baru', $std->name);
        $this->assertEquals('Pernyataan B', $std->pernyataan_standar);
        $this->assertEquals('Indikator Utama A', $std->indicators['iku'][0]['text']);
        $this->assertEquals('80%', $std->indicators['iku'][0]['target']);
        $this->assertEquals('Indikator Tambahan B', $std->indicators['ikt'][0]['text']);
    }
}