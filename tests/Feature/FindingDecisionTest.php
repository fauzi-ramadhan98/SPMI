<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AuditFinding;
use App\Models\AcademicProgram;

class FindingDecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function setupAssignment(): array
    {
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $auditor = User::factory()->create();
        $auditor->syncRoles(['auditor']);
        $prodiUser = User::factory()->create(['academic_program_id' => $prodi->id]);
        $prodiUser->syncRoles(['prodi']);

        $cycle = AuditCycle::create([
            'name' => 'AMI 2025/2026 Ganjil', 'academic_year' => '2025/2026',
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $auditor->id,
        ]);
        $assignment = AuditAssignment::create([
            'audit_cycle_id' => $cycle->id, 'auditor_id' => $auditor->id,
            'auditor_name' => $auditor->name, 'auditor_type' => 'Auditor Internal',
            'academic_program_id' => $prodi->id, 'status' => 'pending',
        ]);
        $finding = AuditFinding::create([
            'audit_assignment_id' => $assignment->id, 'type' => 'KTS',
            'criteria' => 'Standar Pendidikan', 'description' => 'Temuan', 'status' => 'open',
        ]);

        return [$prodiUser, $auditor, $assignment, $finding];
    }

    public function test_auditee_can_set_decision_on_finding(): void
    {
        [$prodiUser, , , $finding] = $this->setupAssignment();

        $this->actingAs($prodiUser)
            ->put('/admin/audit/findings/' . $finding->id, [
                'prodi_decision' => 'setuju',
                'prodi_decision_note' => 'Kami setuju dengan temuan',
                'root_cause' => 'Penyebab',
                'corrective_action' => 'Rencana perbaikan',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_findings', [
            'id' => $finding->id,
            'prodi_decision' => 'setuju',
            'prodi_decision_note' => 'Kami setuju dengan temuan',
        ]);
    }

    public function test_tolak_requires_note(): void
    {
        [$prodiUser, , , $finding] = $this->setupAssignment();

        $this->actingAs($prodiUser)
            ->from('/admin/audit/findings/' . $finding->audit_assignment_id)
            ->put('/admin/audit/findings/' . $finding->id, [
                'prodi_decision' => 'tolak',
                'prodi_decision_note' => '',
            ])
            ->assertSessionHasErrors('prodi_decision_note');
    }
}