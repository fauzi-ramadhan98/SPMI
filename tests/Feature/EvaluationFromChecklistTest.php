<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\QualityStandard;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\EvaluationItem;

class EvaluationFromChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

public function test_generate_from_checklist_creates_ed_items(): void
    {
        $prodi = AcademicProgram::create(['code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true]);
        $user = User::factory()->create(['academic_program_id' => $prodi->id]);
        $user->syncRoles(['prodi']);

        $std = QualityStandard::create(['kode_standar' => 'S.01', 'name' => 'Pendidikan', 'type' => 'IKU', 'pernyataan_standar' => 'Pernyataan', 'is_active' => true]);
        ChecklistItem::create(['quality_standard_id' => $std->id, 'code' => 'A1', 'indicator' => 'Rik tinjauan', 'max_score' => 4, 'is_active' => true]);

        $evaluation = Evaluation::create([
            'name' => 'ED Ganjil', 'academic_year' => '2025/2026', 'semester' => 'Ganjil',
            'evaluable_type' => AcademicProgram::class, 'evaluable_id' => $prodi->id,
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post('/admin/evaluations/' . $evaluation->id . '/generate-from-checklist')
            ->assertRedirect();

        $this->assertDatabaseHas('evaluation_items', [
            'evaluation_id' => $evaluation->id,
            'checklist_item_id' => ChecklistItem::first()->id,
        ]);
    }

    public function test_spmi_can_view_panduan_etik(): void
    {
        $user = User::factory()->create();
        $user->syncRoles(['spmi']);
        $this->actingAs($user)->get('/admin/panduan-etik')->assertStatus(200)->assertSee('Panduan Etik');
    }
}