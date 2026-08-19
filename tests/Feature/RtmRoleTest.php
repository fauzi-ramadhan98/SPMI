<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AcademicProgram;
use App\Models\RtmMeeting;
use App\Models\RtmInstruction;

class RtmRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function makeUser(string $role, array $attrs = []): User
    {
        $u = User::factory()->create($attrs);
        $u->syncRoles([$role]);
        return $u;
    }

    private function makeMeeting(array $attrs = [], string $year = '2025/2026'): RtmMeeting
    {
        $spmi = $this->makeUser('spmi');
        $cycle = AuditCycle::create([
            'name' => 'AMI ' . $year . ' Ganjil', 'academic_year' => $year,
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $spmi->id,
        ]);
        return RtmMeeting::create(array_merge([
            'audit_cycle_id' => $cycle->id,
            'title' => 'RTM Siklus ' . $year,
            'meeting_date' => now(),
            'status' => 'dijadwalkan',
            'created_by' => $spmi->id,
        ], $attrs));
    }

    public function test_spmi_can_create_rtm_with_participants(): void
    {
        $spmi = $this->makeUser('spmi');
        $pimpinan = $this->makeUser('pimpinan');
        $prodiUser = $this->makeUser('prodi');
        $cycle = AuditCycle::create([
            'name' => 'AMI 2026', 'academic_year' => '2025/2026',
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $spmi->id,
        ]);

        $this->actingAs($spmi)
            ->post('/admin/rtm', [
                'audit_cycle_id' => $cycle->id,
                'title' => 'RTM Sem. Ganjil 2025/2026',
                'meeting_date' => '2026-01-15',
                'meeting_time' => '09:00',
                'location' => 'Ruang LPM',
                'participant_ids' => [$pimpinan->id, $prodiUser->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rtm_meetings', ['title' => 'RTM Sem. Ganjil 2025/2026', 'status' => 'dijadwalkan']);
        $this->assertDatabaseHas('rtm_meeting_user', ['user_id' => $pimpinan->id]);
        $this->assertDatabaseHas('rtm_meeting_user', ['user_id' => $prodiUser->id]);
    }

    public function test_pimpinan_cannot_create_rtm(): void
    {
        $pimpinan = $this->makeUser('pimpinan');
        $this->actingAs($pimpinan)->get('/admin/rtm/create')->assertForbidden();
        $this->actingAs($pimpinan)->post('/admin/rtm', ['title' => 'X'])->assertForbidden();
    }

    public function test_spmi_notulensi_and_submit_to_pimpinan(): void
    {
        $spmi = $this->makeUser('spmi');
        $meeting = $this->makeMeeting();

        $this->actingAs($spmi)
            ->post('/admin/rtm/' . $meeting->id . '/notulensi', [
                'notulensi' => 'Rapat membahas temuan KTS Mayor.',
                'status' => 'notulensi',
            ])
            ->assertRedirect();

        $meeting->refresh();
        $this->assertEquals('notulensi', $meeting->status);
        $this->assertEquals('Rapat membahas temuan KTS Mayor.', $meeting->notulensi);
    }

    public function test_spmi_can_add_instruction_for_prodi(): void
    {
        $spmi = $this->makeUser('spmi');
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $meeting = $this->makeMeeting();

        $this->actingAs($spmi)
            ->post('/admin/rtm/' . $meeting->id . '/instructions', [
                'academic_program_id' => $prodi->id,
                'unit_id' => '',
                'instruction' => 'Susun RTL perbaikan rasio dosen.',
                'target_date' => '2026-06-30',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rtm_instructions', [
            'rtm_meeting_id' => $meeting->id,
            'academic_program_id' => $prodi->id,
            'instruction' => 'Susun RTL perbaikan rasio dosen.',
        ]);
    }

    public function test_pimpinan_can_approve_submitted_meeting(): void
    {
        $pimpinan = $this->makeUser('pimpinan');
        $meeting = $this->makeMeeting(['status' => 'notulensi', 'notulensi' => 'Isi risalah.']);

        $this->actingAs($pimpinan)
            ->post('/admin/rtm/' . $meeting->id . '/approve', ['approve' => '1'])
            ->assertRedirect();

        $meeting->refresh();
        $this->assertEquals('disahkan', $meeting->status);
        $this->assertEquals($pimpinan->id, $meeting->approved_by);
    }

    public function test_pimpinan_cannot_approve_not_yet_submitted(): void
    {
        $pimpinan = $this->makeUser('pimpinan');
        $meeting = $this->makeMeeting(['status' => 'dijadwalkan']);

        $this->actingAs($pimpinan)
            ->post('/admin/rtm/' . $meeting->id . '/approve', ['approve' => '1'])
            ->assertForbidden();

        $meeting->refresh();
        $this->assertNotEquals('disahkan', $meeting->status);
    }

    public function test_auditee_only_sees_approved_meetings_with_their_instruction(): void
    {
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $prodiUser = $this->makeUser('prodi', ['academic_program_id' => $prodi->id]);

        $approved = $this->makeMeeting(['status' => 'disahkan']);
        RtmInstruction::create([
            'rtm_meeting_id' => $approved->id, 'academic_program_id' => $prodi->id,
            'instruction' => 'Laksanakan RTL.', 'target_date' => null,
        ]);
        $notApproved = $this->makeMeeting(['status' => 'notulensi'], '2024/2025');

        $response = $this->actingAs($prodiUser)->get('/admin/rtm')->assertOk();
        $response->assertSee('Laksanakan RTL.');
        $response->assertDontSee($notApproved->title);
    }

    public function test_auditee_pdf_only_when_approved_and_has_instruction(): void
    {
        $prodi = AcademicProgram::create([
            'code' => 'TI', 'name' => 'Teknik Informatika', 'degree_level' => 'S1', 'is_active' => true,
        ]);
        $prodiUser = $this->makeUser('prodi', ['academic_program_id' => $prodi->id]);

        $pending = $this->makeMeeting(['status' => 'notulensi'], '2023/2024');
        $this->actingAs($prodiUser)->get('/admin/rtm/' . $pending->id . '/pdf')->assertForbidden();

        $approvedNoInstruction = $this->makeMeeting(['status' => 'disahkan'], '2022/2023');
        $this->actingAs($prodiUser)->get('/admin/rtm/' . $approvedNoInstruction->id . '/pdf')->assertForbidden();

        $approved = $this->makeMeeting(['status' => 'disahkan'], '2021/2022');
        RtmInstruction::create([
            'rtm_meeting_id' => $approved->id, 'academic_program_id' => $prodi->id,
            'instruction' => 'Laksanakan.', 'target_date' => null,
        ]);
        $this->actingAs($prodiUser)->get('/admin/rtm/' . $approved->id . '/pdf')
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_spmi_cannot_delete_or_edit_disahkan_meeting(): void
    {
        $spmi = $this->makeUser('spmi');
        $meeting = $this->makeMeeting(['status' => 'disahkan']);

        $this->actingAs($spmi)->delete('/admin/rtm/' . $meeting->id)->assertForbidden();
        $this->actingAs($spmi)->put('/admin/rtm/' . $meeting->id, ['title' => 'X'])->assertForbidden();
    }
}