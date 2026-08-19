<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AuditInstrument;
use App\Models\AuditFinding;
use App\Models\AcademicProgram;

class WorkflowFinalisasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function setupAssignment(string $status = 'berlangsung'): array
    {
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $auditor = User::factory()->create();
        $auditor->syncRoles(['auditor']);
        $spmi = User::factory()->create();
        $spmi->syncRoles(['spmi']);

        $cycle = AuditCycle::create([
            'name' => 'AMI 2026 Ganjil', 'academic_year' => '2025/2026',
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $auditor->id,
        ]);
        $assignment = AuditAssignment::create([
            'audit_cycle_id' => $cycle->id, 'auditor_id' => $auditor->id,
            'auditor_name' => $auditor->name, 'auditor_nidn' => '0412345670',
            'auditor_type' => 'Auditor Internal',
            'academic_program_id' => $prodi->id, 'status' => $status,
        ]);

        return [$auditor, $spmi, $assignment];
    }

    public function test_auditor_can_finalize_own_assignment(): void
    {
        [$auditor, , $assignment] = $this->setupAssignment('berlangsung');

        $this->actingAs($auditor)
            ->post(route('admin.audit.assignments.update-status', $assignment), ['action' => 'finalisasi'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'status' => 'finalisasi',
        ]);
    }

    public function test_auditor_cannot_finalize_others_assignment(): void
    {
        [, , $assignment] = $this->setupAssignment('berlangsung');
        $other = User::factory()->create();
        $other->syncRoles(['auditor']);

        $this->actingAs($other)
            ->post(route('admin.audit.assignments.update-status', $assignment), ['action' => 'finalisasi'])
            ->assertForbidden();

        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'status' => 'berlangsung',
        ]);
    }

    public function test_spmi_can_approve_finalisasi_to_selesai(): void
    {
        [, $spmi, $assignment] = $this->setupAssignment('finalisasi');

        $this->actingAs($spmi)
            ->post(route('admin.audit.assignments.update-status', $assignment), ['action' => 'selesai'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'status' => 'selesai',
        ]);
    }

    public function test_spmi_cannot_set_selesai_before_finalisasi(): void
    {
        [, $spmi, $assignment] = $this->setupAssignment('berlangsung');

        $this->actingAs($spmi)
            ->post(route('admin.audit.assignments.update-status', $assignment), ['action' => 'selesai'])
            ->assertForbidden();

        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'status' => 'berlangsung',
        ]);
    }

    public function test_spmi_can_reopen_finalized_assignment(): void
    {
        [, $spmi, $assignment] = $this->setupAssignment('selesai');

        $this->actingAs($spmi)
            ->post(route('admin.audit.assignments.update-status', $assignment), ['action' => 'buka_kembali'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_assignments', [
            'id' => $assignment->id,
            'status' => 'berlangsung',
        ]);
    }

    public function test_instrument_write_blocked_when_finalisasi(): void
    {
        [, $auditor, $assignment] = $this->setupAssignment('finalisasi');
        $instrument = AuditInstrument::create([
            'audit_assignment_id' => $assignment->id,
            'criteria' => 'Std 1', 'indicator' => 'Indikator A', 'score' => 3,
        ]);
        $auditor = User::find($assignment->auditor_id);

        $this->actingAs($auditor)
            ->put(route('admin.audit.instruments.update', $instrument), ['score' => 2])
            ->assertForbidden();

        $this->assertDatabaseHas('audit_instruments', [
            'id' => $instrument->id,
            'score' => 3,
        ]);
    }

    public function test_finding_store_blocked_when_selesai(): void
    {
        [$auditor, , $assignment] = $this->setupAssignment('selesai');

        $this->actingAs($auditor)
            ->post(route('admin.audit.findings.store', $assignment), [
                'type' => 'KTS',
                'criteria' => 'Standar Pendidikan',
                'description' => 'Bukti tidak ditemukan',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('audit_findings', 0);
    }
}