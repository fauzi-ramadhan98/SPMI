<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\QualityStandard;
use App\Models\StandardDecree;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StandardDecreeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake('local');
    }

    private function makeSpmi(): User
    {
        $u = User::factory()->create();
        $u->syncRoles(['spmi']);
        return $u;
    }

    private function makePimpinan(): User
    {
        $u = User::factory()->create();
        $u->syncRoles(['pimpinan']);
        return $u;
    }

    private function makeAuditor(): User
    {
        $u = User::factory()->create();
        $u->syncRoles(['auditor']);
        return $u;
    }

    private function makeStandard(): QualityStandard
    {
        return QualityStandard::create([
            'kode_standar' => 'S.01', 'name' => 'Standar Pendidikan',
            'type' => 'IKU', 'pernyataan_standar' => 'Versi 1', 'is_active' => true,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'sk_no'          => 'SK.05/KMI/2026',
            'judul'          => 'Penetapan Tim Auditor Mutu Internal Tahun 2026',
            'nama_standar'   => 'Standar Pendidikan',
            'lokasi'         => 'Bandung',
            'tanggal_sk'     => '2026-09-02',
            'deskripsi'      => 'Pertimbangan',
            'special_ids' => [],
        ], $overrides);
    }

    public function test_spmi_can_create_decree_draft_and_link_standards(): void
    {
        $spmi = $this->makeSpmi();
        $std = $this->makeStandard();

        $this->actingAs($spmi)
            ->post('/admin/standard-decrees', $this->payload([
                'sk_no' => 'SK.05/KMI/2026', 'judul' => 'Penetapan Standar Pendidikan',
                'standard_ids' => [$std->id], 'deskripsi' => 'Pertimbangan',
            ]))
            ->assertRedirect(route('admin.standard-decrees.index'));

        $decree = StandardDecree::where('sk_no', 'SK.05/KMI/2026')->first();
        $this->assertNotNull($decree);
        $this->assertEquals('menunggu_persetujuan', $decree->status);
        $this->assertEquals($spmi->id, $decree->prepared_by);
        $this->assertEquals('images/kopstmik.jpg', $decree->kop_path);
        $this->assertEquals('images/TTD-KETUA.jpg', $decree->signature_path);
        $this->assertTrue($decree->standards->contains($std->id));

        $std->refresh();
        $this->assertEquals($decree->id, $std->standard_decree_id);
    }

    public function test_pimpinan_can_verify_settle_decree(): void
    {
        $spmi = $this->makeSpmi();
        $pimpinan = $this->makePimpinan();
        $std = $this->makeStandard();
        $cycle = \App\Models\AuditCycle::create([
            'name' => 'AMI 2026', 'academic_year' => '2026/2027',
            'semester' => 'Ganjil', 'start_date' => now(), 'end_date' => now()->addDays(30),
            'status' => 'aktif', 'created_by' => $spmi->id,
        ]);
        $decree = StandardDecree::create([
            'sk_no' => 'SK.10/KMI/2026', 'judul' => 'Penetapan S1',
            'status' => 'draft', 'prepared_by' => $spmi->id,
            'audit_cycle_id' => $cycle->id,
        ]);
        $std->update(['standard_decree_id' => $decree->id]);

        $this->actingAs($pimpinan)
            ->post(route('admin.standard-decrees.verify', $decree))
            ->assertRedirect(route('admin.standard-decrees.index'))
            ->assertSessionHas('success');

        $decree->refresh();
        $this->assertEquals('ditetapkan', $decree->status);
        $this->assertEquals($pimpinan->id, $decree->issued_by);
        $this->assertNotNull($decree->issued_at);
        $this->assertEquals('images/TTD-KETUA.jpg', $decree->signature_path);
    }

    public function test_pimpinan_cannot_verify_decree_without_standard_and_cycle(): void
    {
        $spmi = $this->makeSpmi();
        $pimpinan = $this->makePimpinan();
        $decree = StandardDecree::create([
            'sk_no' => 'SK.9/KMI/2026', 'judul' => 'Tanpa Siklus', 'status' => 'draft', 'prepared_by' => $spmi->id,
        ]);

        $this->actingAs($pimpinan)
            ->post(route('admin.standard-decrees.verify', $decree))
            ->assertRedirect()
            ->assertSessionHas('error');

        $decree->refresh();
        $this->assertEquals('draft', $decree->status);
    }

    public function test_spmi_verify_decree_is_forbidden(): void
    {
        $spmi = $this->makeSpmi();
        $decree = StandardDecree::create([
            'sk_no' => 'SK.11/KMI/2026', 'judul' => 'X', 'status' => 'draft', 'prepared_by' => $spmi->id,
        ]);

        $this->actingAs($spmi)
            ->post(route('admin.standard-decrees.verify', $decree))
            ->assertForbidden();
    }

    public function test_auditor_cannot_review_decree(): void
    {
        $auditor = $this->makeAuditor();
        $decree = StandardDecree::create([
            'sk_no' => 'SK.12/KMI/2026', 'judul' => 'X', 'status' => 'draft',
        ]);

        $this->actingAs($auditor)
            ->get(route('admin.standard-decrees.review', $decree))
            ->assertForbidden();
    }

    public function test_pimpinan_cannot_create_or_edit_decree_draft(): void
    {
        $pimpinan = $this->makePimpinan();

        $this->actingAs($pimpinan)
            ->get(route('admin.standard-decrees.create'))
            ->assertForbidden();

        $this->actingAs($pimpinan)
            ->post(route('admin.standard-decrees.store'), $this->payload(['standard_ids' => []]))
            ->assertForbidden();
    }

    public function test_already_settled_decree_cannot_be_reverified(): void
    {
        $pimpinan = $this->makePimpinan();
        $decree = StandardDecree::create([
            'sk_no' => 'SK.7/KMI/2026', 'judul' => 'Z', 'status' => 'ditetapkan',
            'issued_by' => $pimpinan->id, 'issued_at' => now(),
        ]);

        $this->actingAs($pimpinan)
            ->post(route('admin.standard-decrees.verify', $decree))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_spmi_cannot_edit_settled_decree(): void
    {
        $spmi = $this->makeSpmi();
        $decree = StandardDecree::create([
            'sk_no' => 'SK.2/KMI/2026', 'judul' => 'Z', 'status' => 'ditetapkan',
        ]);

        $this->actingAs($spmi)
            ->get(route('admin.standard-decrees.edit', $decree))
            ->assertForbidden();
    }

    public function test_pimpinan_can_generate_pdf(): void
    {
        $spmi = $this->makeSpmi();
        $pimpinan = $this->makePimpinan();
        $decree = StandardDecree::create([
            'sk_no' => 'SK-PDF/2026', 'judul' => 'Judul SK', 'status' => 'draft',
            'nama_standar' => 'Standar Pendidikan', 'prepared_by' => $spmi->id,
        ]);

        $response = $this->actingAs($pimpinan)
            ->get(route('admin.standard-decrees.pdf', $decree));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
    }

    public function test_spmi_can_upload_and_download_sk_file(): void
    {
        $spmi = $this->makeSpmi();
        $decree = StandardDecree::create([
            'sk_no' => 'SK-UP/2026', 'judul' => 'Upload SK', 'status' => 'draft', 'prepared_by' => $spmi->id,
        ]);

        $file = UploadedFile::fake()->create('sk.pdf', 150, 'application/pdf');

        $this->actingAs($spmi)
            ->post(route('admin.standard-decrees.upload-file', $decree), ['file' => $file])
            ->assertRedirect(route('admin.standard-decrees.index'))
            ->assertSessionHas('success');

        $decree->refresh();
        $this->assertNotNull($decree->file_path);
        $this->assertEquals('sk.pdf', $decree->file_name);
        $this->assertEquals('pdf', $decree->file_type);
        $this->assertTrue(Storage::disk('local')->exists($decree->file_path));

        $this->actingAs($spmi)
            ->get(route('admin.standard-decrees.download-file', $decree))
            ->assertOk();
    }

    public function test_spmi_index_shows_uploaded_sk(): void
    {
        $spmi = $this->makeSpmi();
        $decree = StandardDecree::create([
            'sk_no' => 'SK-LI/2026', 'judul' => 'Lihat SK', 'status' => 'draft', 'prepared_by' => $spmi->id,
            'file_path' => 'standard_decrees/sk-existing.pdf', 'file_name' => 'sk-existing.pdf',
        ]);

        $request = $this->actingAs($spmi)->get(route('admin.standard-decrees.index'));
        $request->assertOk();
        $request->assertSee('SK-LI/2026');
        $request->assertSee('sk-existing.pdf');
        $request->assertSee('Upload SK');
    }

    public function test_auditor_cannot_upload_sk_file(): void
    {
        $auditor = $this->makeAuditor();
        $decree = StandardDecree::create([
            'sk_no' => 'SK-X/2026', 'judul' => 'X', 'status' => 'draft',
        ]);

        $this->actingAs($auditor)
            ->post(route('admin.standard-decrees.upload-file', $decree), ['file' => UploadedFile::fake()->create('sk.pdf')])
            ->assertForbidden();
    }

    public function test_pimpinan_cannot_upload_sk_file_anymore(): void
    {
        $pimpinan = $this->makePimpinan();
        $decree = StandardDecree::create([
            'sk_no' => 'SK-UP2/2026', 'judul' => 'Upload Pimpinan', 'status' => 'ditetapkan',
            'issued_by' => $pimpinan->id, 'issued_at' => now(),
        ]);

        $this->actingAs($pimpinan)
            ->post(route('admin.standard-decrees.upload-file', $decree), ['file' => UploadedFile::fake()->create('sk-resmi.pdf')])
            ->assertForbidden();
    }

    public function test_index_only_shows_standar_decrees_and_auditor_index_only_shows_auditor(): void
    {
        $spmi = $this->makeSpmi();
        $standar = StandardDecree::create([
            'sk_no' => 'SK-ST/2026', 'judul' => 'Penetapan Standar Mutu', 'jenis' => 'standar',
            'status' => 'draft', 'prepared_by' => $spmi->id,
        ]);
        $auditor = StandardDecree::create([
            'sk_no' => 'SK-AU/2026', 'judul' => 'SURAT KEPUTUSAN AUDITOR', 'jenis' => 'auditor',
            'status' => 'draft', 'prepared_by' => $spmi->id,
        ]);

        $standarResp = $this->actingAs($spmi)->get(route('admin.standard-decrees.index'));
        $standarResp->assertOk();
        $standarResp->assertSee('SK-ST/2026');
        $standarResp->assertDontSee('SK-AU/2026');

        $auditorResp = $this->actingAs($spmi)->get(route('admin.standard-decrees.auditor'));
        $auditorResp->assertOk();
        $auditorResp->assertSee('SK-AU/2026');
        $auditorResp->assertDontSee('SK-ST/2026');
    }

    public function test_pimpinan_can_view_auditor_index(): void
    {
        $pimpinan = $this->makePimpinan();
        StandardDecree::create([
            'sk_no' => 'SK-AU/2026', 'judul' => 'SURAT KEPUTUSAN AUDITOR', 'jenis' => 'auditor',
            'status' => 'draft',
        ]);

        $this->actingAs($pimpinan)
            ->get(route('admin.standard-decrees.auditor'))
            ->assertOk()
            ->assertSee('SK-AU/2026');
    }
}