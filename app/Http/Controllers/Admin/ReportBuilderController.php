<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\AuditCycle;
use App\Models\GeneratedReport;
use App\Models\GeneratedReportAttachment;
use App\Services\AmiReportService;
use App\Services\PdfAssembler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Generate Laporan AMI — daftar laporan, susunan lampiran terurut, arsip PDF.
 *
 * Alur (sesuai catatan client):
 *   Laporan → [Generate Laporan] → daftar → detail (urutkan/tambah lampiran)
 *             → [Generate PDF] → arsip tersimpan → unduh kapan saja.
 */
class ReportBuilderController extends Controller
{
    public function __construct(
        private AmiReportService $reports,
        private PdfAssembler $assembler,
    ) {
    }

    /** Daftar laporan yang pernah/sudah digenerate. */
    public function index()
    {
        $reports = GeneratedReport::with(['cycle', 'program'])
            ->withCount('attachments')
            ->latest()->get();

        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();
        $programs = AcademicProgram::orderBy('degree_level')->orderBy('name')->get();

        $user = auth()->user();
        $isProdi = $user->hasRole('prodi');

        return view('admin.reports.generated.index', compact('reports', 'cycles', 'programs', 'isProdi'));
    }

    /** Buat draft laporan; lampiran auto-isi dari data existing (SK, dokumen, bukti ED & temuan). */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isProdi = $user->hasRole('prodi');

        $request->validate([
            'cycle_id' => 'required|exists:audit_cycles,id',
            'level' => 'required|in:institusi,prodi',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'jenis' => 'required|in:klasik,berbasis-risiko',
        ]);

        $level = $isProdi ? 'prodi' : $request->input('level');
        $programId = $isProdi
            ? $user->academic_program_id
            : ($level === 'prodi' ? (int) $request->input('academic_program_id') : null);

        if ($level === 'prodi' && !$programId) {
            return back()->withErrors(['academic_program_id' => 'Pilih program studi terlebih dahulu.']);
        }

        $report = GeneratedReport::create([
            'audit_cycle_id' => $request->input('cycle_id'),
            'level' => $level,
            'academic_program_id' => $programId,
            'jenis' => $request->input('jenis'),
            'generated_by' => $user->id,
        ]);

        $cycle = $report->cycle;
        foreach ($this->reports->attachmentCandidates($cycle, $level, $programId) as $i => $candidate) {
            $report->attachments()->create($candidate + ['sort_order' => $i]);
        }

        return redirect()
            ->route('admin.reports.generated.show', $report)
            ->with('success', 'Laporan dibuat. Susun urutan lampiran, lalu klik Generate PDF.');
    }

    /** Detail: info siklus + daftar lampiran terurut + kandidat untuk ditambahkan. */
    public function show(GeneratedReport $report)
    {
        $report->load(['cycle', 'program', 'attachments']);

        $attachedPaths = $report->attachments->pluck('file_path')->all();
        $candidates = $this->reports
            ->attachmentCandidates($report->cycle, $report->level, $report->academic_program_id)
            ->reject(fn ($c) => in_array($c['file_path'], $attachedPaths, true))
            ->values();

        return view('admin.reports.generated.show', [
            'report' => $report,
            'candidates' => $candidates,
        ]);
    }

    /** Tambah lampiran: upload file baru ATAU salin dari data existing. */
    public function storeAttachment(Request $request, GeneratedReport $report)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|max:20480',
            'source_key' => 'nullable|string|max:512',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif'], true)) {
                return back()->withErrors(['file' => 'Format harus PDF, JPG, PNG, atau GIF.']);
            }
            $path = $file->store('generated_reports/' . $report->id, 'local');
            $meta = [
                'source_label' => 'Upload manual',
                'source_type' => null,
                'source_id' => null,
                'disk' => 'local',
            ];
        } elseif ($request->filled('source_key')) {
            $picked = $this->reports
                ->attachmentCandidates($report->cycle, $report->level, $report->academic_program_id)
                ->first(fn ($c) => ($c['disk'] . '|' . $c['file_path']) === $request->input('source_key'));

            if (!$picked) {
                return back()->withErrors(['source_key' => 'Lampiran tidak ditemukan pada data existing.']);
            }
            $src = Storage::disk($picked['disk'])->path($picked['file_path']);
            if (!is_file($src)) {
                return back()->withErrors(['source_key' => 'File sumber tidak ditemukan.']);
            }
            // Salin ke folder laporan agar arsip laporan mandiri (file asli boleh berubah/hapus).
            $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
            $path = 'generated_reports/' . $report->id . '/' . sha1_file($src) . '.' . $ext;
            if (!Storage::disk('local')->exists($path)) {
                Storage::disk('local')->put($path, (string) file_get_contents($src));
            }
            $meta = [
                'source_label' => $picked['source_label'],
                'source_type' => $picked['source_type'],
                'source_id' => $picked['source_id'],
                'disk' => 'local',
            ];
        } else {
            return back()->withErrors(['file' => 'Pilih file untuk diunggah atau pilih lampiran dari data existing.']);
        }

        $next = ((int) $report->attachments()->max('sort_order')) + 1;
        $report->attachments()->create([
            'title' => $request->input('title'),
            'file_path' => $path,
            'sort_order' => $next,
        ] + $meta);

        return back()->with('success', 'Lampiran ditambahkan ke urutan terakhir.');
    }

    /** Geser lampiran satu langkah ke atas/bawah (mengubah urutan halaman di PDF). */
    public function sort(Request $request, GeneratedReport $report, GeneratedReportAttachment $attachment)
    {
        abort_unless($attachment->generated_report_id === $report->id, 404);
        $request->validate(['direction' => 'required|in:up,down']);

        $items = $report->attachments()->get()->values();
        $pos = $items->search(fn ($a) => $a->id === $attachment->id);
        if ($pos === false) {
            return back()->withErrors(['sort' => 'Lampiran tidak ditemukan.']);
        }

        $swapWith = $request->input('direction') === 'up' ? $pos - 1 : $pos + 1;
        if ($swapWith < 0 || $swapWith >= $items->count()) {
            return back()->with('info', 'Sudah berada di urutan paling ' . ($request->input('direction') === 'up' ? 'atas' : 'bawah') . '.');
        }

        // Normalisasi sort_order rapat 0..n-1, lalu tukar dua posisi.
        foreach ($items as $i => $item) {
            if ((int) $item->sort_order !== $i) {
                $item->sort_order = $i;
                $item->save();
            }
        }
        $a = $items[$pos];
        $b = $items[$swapWith];
        $tmp = (int) $a->sort_order;
        $a->sort_order = (int) $b->sort_order;
        $b->sort_order = $tmp;
        $a->save();
        $b->save();

        return back()->with('success', 'Urutan lampiran diperbarui.');
    }

    /** Hapus lampiran dari susunan (berkas salinan ikut dihapus; file asli tidak disentuh). */
    public function destroyAttachment(GeneratedReport $report, GeneratedReportAttachment $attachment)
    {
        abort_unless($attachment->generated_report_id === $report->id, 404);

        $prefix = 'generated_reports/' . $report->id . '/';
        if ($attachment->disk === 'local' && $attachment->file_path && str_starts_with($attachment->file_path, $prefix)) {
            Storage::disk('local')->delete($attachment->file_path);
        }
        $attachment->delete();

        return back()->with('success', 'Lampiran dihapus dari susunan.');
    }

    /** Render + gabung laporan menurut urutan lampiran, simpan sebagai arsip PDF. */
    public function generate(GeneratedReport $report)
    {
        $result = $this->buildPdf($report);

        $msg = 'Laporan digenerate: ' . $result['pages'] . ' halaman.';
        if ($result['warnings']) {
            $msg .= ' ' . count($result['warnings']) . ' lampiran dilewati (lihat catatan).';
        }

        return back()->with('success', $msg)->with('warnings', $result['warnings']);
    }

    /** Unduh arsip PDF; bila belum ada/hilang, bangun ulang dulu. */
    public function download(GeneratedReport $report)
    {
        if (!$report->file_path || !Storage::disk('local')->exists($report->file_path)) {
            $this->buildPdf($report);
            $report->refresh();
        }

        return Storage::disk('local')->download(
            $report->file_path,
            $report->file_name ?: 'laporan-ami.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    /** Hapus laporan beserta arsip & salinan lampirannya. */
    public function destroy(GeneratedReport $report)
    {
        Storage::disk('local')->deleteDirectory('generated_reports/' . $report->id);
        $report->delete();

        return redirect()->route('admin.reports.generated.index')->with('success', 'Laporan dihapus.');
    }

    /**
     * Render badan laporan + gabungkan lampiran terurut, simpan ke arsip.
     *
     * @return array{bytes: string, pages: int, warnings: array<int, string>}
     */
    protected function buildPdf(GeneratedReport $report): array
    {
        $report->load(['cycle', 'program', 'attachments']);
        $cycle = $report->cycle;
        $program = $report->level === 'prodi' ? $report->program : null;

        $data = $this->reports->assembleData($cycle, $report->level, $report->academic_program_id, $report->jenis);

        // Daftar urut untuk halaman "Daftar Lampiran" di badan PDF.
        $lampiranRows = $report->attachments->map(fn ($a, $i) => [
            'no'      => $i + 1,
            'title'   => $a->title,
            'sumber'  => $a->source_label ?? '—',
            'berkas'  => $a->berkas_label,
            'jenis'   => $a->jenis,
        ]);

        $body = $this->reports->renderBody($data, true, $lampiranRows);
        $result = $this->assembler->assemble($body, $report->attachments);

        $filename = $this->reports->filename($cycle, $report->level, $program, $report->jenis);
        $path = 'generated_reports/' . $report->id . '/' . $filename;
        Storage::disk('local')->put($path, $result['bytes']);

        $report->update([
            'status' => 'generated',
            'file_path' => $path,
            'file_name' => $filename,
            'generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);

        return $result;
    }
}
