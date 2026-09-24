<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AcademicProgram;

class SuratTugasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function setupAssignment(): AuditAssignment
    {
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $auditor = User::factory()->create();
        $auditor->syncRoles(['auditor']);

        $cycle = AuditCycle::create([
            'name' => 'AMI 2025/2026 - Ganjil', 'academic_year' => '2025/2026',
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $auditor->id,
        ]);

        return AuditAssignment::create([
            'audit_cycle_id' => $cycle->id, 'auditor_id' => $auditor->id,
            'auditor_name' => $auditor->name, 'auditor_nidn' => '0412345670',
            'auditor_type' => 'Auditor Internal',
            'academic_program_id' => $prodi->id, 'status' => 'pending',
        ]);
    }

    public function test_spmi_can_generate_surat_tugas_pdf_with_kop(): void
    {
        $spmi = User::factory()->create();
        $spmi->syncRoles(['spmi']);
        $assignment = $this->setupAssignment();

        $response = $this->actingAs($spmi)
            ->get(route('admin.surat-tugas.generate', $assignment));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
    }

    public function test_spmi_can_generate_jadwal_visitasi_pdf(): void
    {
        $spmi = User::factory()->create();
        $spmi->syncRoles(['spmi']);
        $assignment = $this->setupAssignment();

        $response = $this->actingAs($spmi)
            ->get(route('admin.surat-tugas.jadwal', $assignment->cycle));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
        $response->assertHeader(
            'Content-Disposition',
            'attachment; filename="jadwal-visitasi-AMI 2025-2026 - Ganjil-2025-2026.pdf"'
        );
    }

    public function test_auditor_can_view_surat_tugas_index_but_only_own_assignments(): void
    {
        $assignment = $this->setupAssignment();
        $auditor = User::find($assignment->auditor_id);

        $other = User::factory()->create();
        $other->syncRoles(['auditor']);
        $otherProdi = AcademicProgram::create([
            'code' => 'SI', 'name' => 'Sistem Informasi', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        AuditAssignment::create([
            'audit_cycle_id' => $assignment->cycle->id, 'auditor_id' => $other->id,
            'auditor_name' => $other->name, 'auditor_type' => 'Auditor Internal',
            'academic_program_id' => $otherProdi->id, 'status' => 'pending',
        ]);

        $response = $this->actingAs($auditor)
            ->get(route('admin.surat-tugas.index'));

        $response->assertOk();
        $response->assertSee($assignment->auditee_label, false);
        $response->assertDontSee($otherProdi->name);
    }

    public function test_auditor_can_generate_only_own_surat_tugas(): void
    {
        $assignment = $this->setupAssignment();
        $auditor = User::find($assignment->auditor_id);
        $other = User::factory()->create();
        $other->syncRoles(['auditor']);
        $otherProdi = AcademicProgram::create([
            'code' => 'SI', 'name' => 'Sistem Informasi', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $otherAssignment = AuditAssignment::create([
            'audit_cycle_id' => $assignment->cycle->id, 'auditor_id' => $other->id,
            'auditor_name' => $other->name, 'auditor_type' => 'Auditor Internal',
            'academic_program_id' => $otherProdi->id, 'status' => 'pending',
        ]);

        $response = $this->actingAs($auditor)
            ->get(route('admin.surat-tugas.generate', $otherAssignment));

        $response->assertForbidden();
    }
}
