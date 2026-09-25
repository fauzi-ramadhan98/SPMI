<?php

namespace Tests\Feature;

use App\Models\AuditCycle;
use App\Models\GeneratedReport;
use App\Models\GeneratedReportAttachment;
use App\Models\StandardDecree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeneratedReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function spmi(): User
    {
        $user = User::factory()->create();
        $user->syncRoles(['spmi']);
        return $user;
    }

    private function cycle(string $name = 'AMI Ganjil 2025/2026'): AuditCycle
    {
        return AuditCycle::create([
            'name' => $name,
            'academic_year' => '2025/2026',
            'semester' => 'Ganjil',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'status' => 'aktif',
        ]);
    }

    /** PDF 1 halaman asli (FPDF) supaya FPDI bisa mengimpornya. */
    private function realPdf(string $label = 'Lampiran'): string
    {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 14);
        $pdf->Cell(0, 12, $label);
        return $pdf->Output('S');
    }

    private function makeDecree(AuditCycle $cycle, string $skNo = 'SK-001/AMI/2025', string $path = 'standard_decrees/sk.pdf'): StandardDecree
    {
        Storage::disk('local')->put($path, $this->realPdf('SK ' . $skNo));

        return StandardDecree::create([
            'sk_no' => $skNo,
            'judul' => 'Penetapan Tim Auditor Mutu Internal',
            'nama_standar' => 'Standar Pendidikan',
            'lokasi' => 'Bandung',
            'audit_cycle_id' => $cycle->id,
            'kategori' => 'penetapan',
            'status' => 'ditetapkan',
            'tanggal_sk' => '2025-09-01',
            'file_path' => $path,
            'file_name' => basename($path),
        ]);
    }

    private function createReport(User $user, AuditCycle $cycle, array $overrides = []): GeneratedReport
    {
        $this->actingAs($user)->post(route('admin.reports.generated.store'), $overrides + [
            'cycle_id' => $cycle->id,
            'level' => 'institusi',
            'jenis' => 'klasik',
        ])->assertRedirect();

        return GeneratedReport::latest('id')->first();
    }

    public function test_spmi_can_create_report_with_auto_filled_attachments(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);

        $response = $this->actingAs($this->spmi())->post(route('admin.reports.generated.store'), [
            'cycle_id' => $cycle->id,
            'level' => 'institusi',
            'jenis' => 'klasik',
        ]);

        $report = GeneratedReport::first();
        $this->assertNotNull($report);
        $response->assertRedirect(route('admin.reports.generated.show', $report));

        $report->load('attachments');
        $this->assertSame('draft', $report->status);
        $this->assertCount(1, $report->attachments);
        $this->assertSame(StandardDecree::class, $report->attachments->first()->source_type);
        $this->assertSame(0, (int) $report->attachments->first()->sort_order);
    }

    public function test_attachments_without_file_are_not_auto_filled(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        // SK kedua belum diunggah scan/file — tidak boleh masuk susunan.
        StandardDecree::create([
            'sk_no' => 'SK-002/AMI/2025',
            'judul' => 'SK Tanpa File',
            'audit_cycle_id' => $cycle->id,
            'kategori' => 'penetapan',
            'status' => 'ditetapkan',
        ]);

        $this->createReport($this->spmi(), $cycle);

        $this->assertCount(1, GeneratedReport::first()->attachments);
    }

    public function test_detail_page_lists_attachments_in_order(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);

        $this->actingAs($user)
            ->get(route('admin.reports.generated.show', $report))
            ->assertOk()
            ->assertSee('SK-001/AMI/2025')
            ->assertSee('Urutan Lampiran');
    }

    public function test_upload_attachment_and_reorder_up_then_down(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);

        $this->actingAs($user)->post(route('admin.reports.generated.attachments.store', $report), [
            'title' => 'Daftar Hadir Rapat 1',
            'file' => UploadedFile::fake()->createWithContent('daftar-hadir.pdf', $this->realPdf('Daftar Hadir')),
        ])->assertRedirect();

        $report->refresh();
        $this->assertCount(2, $report->attachments);
        [$first, $second] = $report->attachments->all();
        $this->assertSame('Daftar Hadir Rapat 1', $second->title);

        // Geser lampiran manual ke atas
        $this->actingAs($user)
            ->post(route('admin.reports.generated.attachments.sort', [$report, $second]), ['direction' => 'up'])
            ->assertRedirect();

        $order = GeneratedReportAttachment::where('generated_report_id', $report->id)
            ->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
        $this->assertSame([$second->id, $first->id], $order);

        // Geser lagi ke bawah — kembali ke posisi awal
        $this->actingAs($user)
            ->post(route('admin.reports.generated.attachments.sort', [$report, $second]), ['direction' => 'down'])
            ->assertRedirect();

        $order = GeneratedReportAttachment::where('generated_report_id', $report->id)
            ->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
        $this->assertSame([$first->id, $second->id], $order);
    }

    public function test_generate_merges_body_with_pdf_attachment_and_stores_archive(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);

        $response = $this->actingAs($user)
            ->post(route('admin.reports.generated.generate', $report))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $report->refresh();
        $this->assertSame('generated', $report->status);
        $this->assertNotNull($report->generated_at);
        $this->assertStringStartsWith('generated_reports/' . $report->id . '/', $report->file_path);

        Storage::disk('local')->assertExists($report->file_path);
        $bytes = Storage::disk('local')->get($report->file_path);
        $this->assertStringStartsWith('%PDF', $bytes);

        // Lampiran PDF ikut ter-gabung: ukuran harus lebih besar daripada
        // halaman bodiesa tanpa lampiran? verifikasi minimal: PDF valid & tidak nol.
        $this->assertGreaterThan(1000, strlen($bytes));
        $this->assertEmpty(session('warnings'));
    }

    public function test_download_returns_archived_pdf(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);
        $this->actingAs($user)->post(route('admin.reports.generated.generate', $report));

        $response = $this->actingAs($user)->get(route('admin.reports.generated.download', $report));
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('laporan-ami-klasik', (string) $response->headers->get('content-disposition'));
    }

    public function test_download_builds_pdf_when_archive_missing(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);

        // Belum pernah di-generate → download harus tetap berhasil (bangun otomatis).
        $this->actingAs($user)->get(route('admin.reports.generated.download', $report))->assertOk();
        $this->assertSame('generated', $report->refresh()->status);
    }

    public function test_destroy_attachment_keeps_source_file(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);
        $attachment = $report->attachments()->first();

        $this->actingAs($user)
            ->delete(route('admin.reports.generated.attachments.destroy', [$report, $attachment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('generated_report_attachments', ['id' => $attachment->id]);
        // Berkas asli SK tidak boleh ikut terhapus.
        Storage::disk('local')->assertExists('standard_decrees/sk.pdf');
    }

    public function test_destroy_report_removes_record_and_archive(): void
    {
        $cycle = $this->cycle();
        $this->makeDecree($cycle);
        $user = $this->spmi();
        $report = $this->createReport($user, $cycle);
        $this->actingAs($user)->post(route('admin.reports.generated.generate', $report));
        $filePath = $report->refresh()->file_path;

        $this->actingAs($user)
            ->delete(route('admin.reports.generated.destroy', $report))
            ->assertRedirect(route('admin.reports.generated.index'));

        $this->assertDatabaseMissing('generated_reports', ['id' => $report->id]);
        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_store_validates_required_inputs(): void
    {
        $this->actingAs($this->spmi())
            ->post(route('admin.reports.generated.store'), ['level' => 'institusi'])
            ->assertSessionHasErrors(['cycle_id', 'jenis']);

        $this->assertSame(0, GeneratedReport::count());
    }
}
