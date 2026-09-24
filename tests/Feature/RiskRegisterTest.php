<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
use App\Models\RiskRegister;
use App\Models\QualityStandard;

class RiskRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        AcademicYear::create(['name' => '2025/2026', 'is_active' => true]);
    }

    private function makeSpmi(): User
    {
        $u = User::factory()->create();
        $u->syncRoles(['spmi']);
        return $u;
    }

    private function payload(array $overrides = []): array
    {
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        return array_merge([
            'academic_program_id' => $prodi->id,
            'academic_year' => '2025/2026',
            'standar_mutu' => 'Standar Pendidikan',
            'butir_tilik' => 'IKU 1.1',
            'risk_category' => 'SDM',
            'risk_description' => 'Risiko kekurangan dosen',
            'temuan' => 'Rasio dosen-mahasiswa belum memenuhi',
            'akar_masalah' => 'Keterbatasan rekrutmen',
            'impact' => 4,
            'probability' => 3,
            'mitigation_plan' => 'Mengajukan rekrutmen dosen baru',
            'document_link' => null,
            'pic' => 'Kaprodi',
            'target_date' => '2026-12-31',
        ], $overrides);
    }

    public function test_create_page_marks_required_fields(): void
    {
        $this->actingAs($this->makeSpmi())
            ->get('/admin/risk-registers/create')
            ->assertOk()
            ->assertSee('Akar Masalah')
            ->assertSee('Mitigasi')
            ->assertSee('PIC (Penanggungjawab)')
            ->assertSee('Tanggal Penyelesaian')
            ->assertSee('2025/2026');
    }

    public function test_mitigation_pic_and_target_date_are_required(): void
    {
        $this->actingAs($this->makeSpmi())
            ->post('/admin/risk-registers', $this->payload([
                'mitigation_plan' => '',
                'pic' => '',
                'target_date' => '',
            ]))
            ->assertSessionHasErrors(['mitigation_plan', 'pic', 'target_date']);

        $this->assertEquals(0, RiskRegister::count());
    }

    public function test_akar_masalah_is_required(): void
    {
        $this->actingAs($this->makeSpmi())
            ->post('/admin/risk-registers', $this->payload(['akar_masalah' => '']))
            ->assertSessionHasErrors(['akar_masalah']);

        $this->assertEquals(0, RiskRegister::count());
    }

    public function test_academic_year_must_come_from_master(): void
    {
        $this->actingAs($this->makeSpmi())
            ->post('/admin/risk-registers', $this->payload(['academic_year' => '1999/2000']))
            ->assertSessionHasErrors(['academic_year']);

        $this->assertEquals(0, RiskRegister::count());
    }

    public function test_store_succeeds_with_all_required_fields(): void
    {
        $this->actingAs($this->makeSpmi())
            ->post('/admin/risk-registers', $this->payload())
            ->assertRedirect(route('admin.risk-registers.index'));

        $this->assertEquals(1, RiskRegister::count());
        $risk = RiskRegister::first();
        $this->assertEquals('Kaprodi', $risk->pic);
        $this->assertEquals('2026-12-31', $risk->target_date);
        $this->assertEquals('2025/2026', $risk->academic_year);
    }

    public function test_auditor_cannot_create_or_edit_risk_register(): void
    {
        $auditor = User::factory()->create();
        $auditor->syncRoles(['auditor']);

        $this->actingAs($auditor)
            ->get('/admin/risk-registers/create')
            ->assertForbidden();

        $this->actingAs($auditor)
            ->post('/admin/risk-registers', $this->payload())
            ->assertForbidden();

        $this->assertEquals(0, RiskRegister::count());
    }

    public function test_auditor_can_view_risk_register_index(): void
    {
        $spmi = $this->makeSpmi();
        $this->actingAs($spmi)->post('/admin/risk-registers', $this->payload());
        $prodi = AcademicProgram::first();

        $auditor = User::factory()->create();
        $auditor->syncRoles(['auditor']);

        $cycle = \App\Models\AuditCycle::create([
            'name' => 'AMI 2026', 'academic_year' => '2025/2026',
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $auditor->id,
        ]);
        \App\Models\AuditAssignment::create([
            'audit_cycle_id' => $cycle->id, 'auditor_id' => $auditor->id,
            'auditor_name' => $auditor->name, 'auditor_type' => 'Auditor Internal',
            'academic_program_id' => $prodi->id, 'status' => 'pending',
        ]);

        $this->actingAs($auditor)
            ->get('/admin/risk-registers')
            ->assertOk()
            ->assertSee('Teknik Informatika')
            ->assertDontSee('Tambah Entri Risiko');
    }
}
