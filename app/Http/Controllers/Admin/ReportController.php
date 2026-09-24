<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AcademicProgram;
use App\Models\Evaluation;
use App\Models\Unit;
use App\Models\QualityStandard;
use App\Models\RiskRegister;
use App\Models\Document;
use App\Models\StandardDecree;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isProdi = $user->hasRole('prodi');

        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();

        $programs = AcademicProgram::query()
            ->when($isProdi && $user->academic_program_id, fn ($q) => $q->where('id', $user->academic_program_id))
            ->orderBy('degree_level')->orderBy('name')->get();

        $cycleId = $request->filled('cycle_id') ? (int) $request->input('cycle_id') : ($cycles->first()->id ?? null);
        $level = $isProdi
            ? 'prodi'
            : ($request->filled('level') && in_array($request->input('level'), ['institusi', 'prodi']) ? $request->input('level') : 'institusi');
        $programId = $isProdi
            ? $user->academic_program_id
            : ($request->filled('academic_program_id') ? (int) $request->input('academic_program_id') : null);

        $summary = null;
        $reportedAssignments = collect();

        if ($cycleId) {
            $reportedAssignments = $this->scopedAssignments($cycleId, $level, $programId);
            $summary = $this->summarize($reportedAssignments);
        }

        return view('admin.reports.index', compact(
            'cycles', 'programs', 'cycleId', 'level', 'programId',
            'summary', 'reportedAssignments', 'isProdi'
        ));
    }

    public function generateAmiPdf(Request $request)
    {
        $request->validate([
            'cycle_id' => 'required|exists:audit_cycles,id',
            'level' => 'required|in:institusi,prodi',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            // Poin catatan client: pilih 1 dari 2 format laporan AMI
            'jenis' => 'nullable|in:klasik,berbasis-risiko',
        ]);

        $user = auth()->user();
        $isProdi = $user->hasRole('prodi');

        $cycle = AuditCycle::findOrFail($request->input('cycle_id'));
        $level = $isProdi ? 'prodi' : $request->input('level');
        $programId = $isProdi
            ? $user->academic_program_id
            : ($request->filled('academic_program_id') ? (int) $request->input('academic_program_id') : null);

        if ($level === 'prodi' && !$programId) {
            return back()->withErrors(['academic_program_id' => 'Pilih program studi terlebih dahulu.']);
        }

        $assignments = $this->scopedAssignments($cycle->id, $level, $programId);
        $summary = $this->summarize($assignments);

        $program = $level === 'prodi' ? AcademicProgram::find($programId) : null;

        // Poin catatan client — 2 jenis laporan sesuai contoh PDF lampiran:
        //   klasik          = Laporan Kegiatan AMI (cover, pengesahan, BAB I-IV, Lampiran Daftar Tilik)
        //   berbasis-risiko = Hasil Audit Berbasis Risiko (matriks Dampak x Likelihood + Rekomendasi/TL)
        $jenis = $request->input('jenis') ?: 'klasik';

        $standars = QualityStandard::with('checklistItems')->orderBy('kode_standar')->get();

        // Evaluasi Diri terbaru per auditee (untuk isi Lampiran Daftar Tilik)
        $edMap = [];
        foreach ($assignments as $assignment) {
            $evaluation = null;
            if ($assignment->academic_program_id) {
                $evaluation = Evaluation::where('evaluable_type', AcademicProgram::class)
                    ->where('evaluable_id', $assignment->academic_program_id)
                    ->latest()->first();
            } elseif ($assignment->unit_id) {
                $evaluation = Evaluation::where('evaluable_type', Unit::class)
                    ->where('evaluable_id', $assignment->unit_id)
                    ->latest()->first();
            }
            if ($evaluation) {
                $evaluation->load(['items.checklistItem', 'items.attachments', 'attachments']);
            }
            $edMap[$assignment->id] = $evaluation;
        }

        // Risk Register pada tahun akademik siklus (jenis berbasis risiko)
        $risks = collect();
        if ($jenis === 'berbasis-risiko') {
            $risks = $this->scopedRisks($cycle, $level, $programId);
        }

        $safeYear = str_replace(['/', '\\'], '-', $cycle->academic_year);
        $jenisSlug = $jenis === 'berbasis-risiko' ? 'berbasis-risiko' : 'klasik';
        $filename = 'laporan-ami-' . $jenisSlug . '-'
            . $safeYear . '-'
            . $cycle->semester
            . ($level === 'prodi' ? '-' . Str::slug($program->name ?? 'prodi') : '')
            . '.pdf';

        $view = $jenis === 'berbasis-risiko'
            ? 'admin.reports.ami_risiko_pdf'
            : 'admin.reports.ami_klasik_pdf';

        // ===== Pengaturan cetak dari halaman Konfigurasi Aplikasi =====
        $showCover = setting('report_cover_enabled', '1') !== '0';
        $coverImage = null;
        if ($showCover && setting('report_cover_image')) {
            $coverImage = $this->embeddedImage((string) setting('report_cover_image'), 'public', 698, 1015);
        }
        $headerLogo = $this->headerLogo();
        $reportInst = strtoupper(trim((string) setting('institution_name', 'STMIK Mardira Indonesia')));
        $reportKode = trim((string) setting('report_kode', '')) ?: 'STMIKMI.LPMI.AMI.VIII.1';
        $reportEdisi = trim((string) setting('report_edisi', '')) ?: '2';

        // ===== Lampiran: SK & dokumen yang terkait siklus =====
        $decrees = StandardDecree::where('audit_cycle_id', $cycle->id)
            ->orderByRaw('tanggal_sk IS NULL')
            ->orderBy('tanggal_sk')
            ->orderBy('id')
            ->get();

        $decreesLampiran = $decrees->map(fn ($d) => [
            'sk_no'     => $d->sk_no,
            'judul'     => $d->judul,
            'kategori'  => $d->kategori ?: 'penetapan',
            'tanggal'   => $d->tanggal_sk,
            'status'    => $d->status ?: 'draft',
            'file_name' => $d->file_name,
            'image'     => $d->file_path ? $this->embeddedImage($d->file_path, 'local', 698, 950) : null,
        ]);

        $docsLampiran = Document::where(function ($q) use ($cycle, $decrees) {
                $q->where('audit_cycle_id', $cycle->id)
                    ->orWhereIn('standard_decree_id', $decrees->pluck('id'));
            })
            ->orderBy('id')
            ->get()
            ->map(fn ($doc) => [
                'code'      => $doc->code,
                'title'     => $doc->title,
                'module'    => $doc->module,
                'doc_date'  => $doc->doc_date,
                'file_name' => $doc->file_name,
                'image'     => $doc->file_path ? $this->embeddedImage($doc->file_path, 'local', 698, 950) : null,
            ]);

        // ===== Lampiran: bukti kegiatan (Evaluasi Diri & Temuan Audit) =====
        $buktiLampiran = $this->collectBukti($edMap, $assignments);

        $pdf = Pdf::loadView($view, compact(
            'cycle', 'assignments', 'summary', 'level', 'program',
            'jenis', 'standars', 'edMap', 'risks',
            'showCover', 'coverImage', 'headerLogo',
            'reportInst', 'reportKode', 'reportEdisi',
            'decreesLampiran', 'docsLampiran', 'buktiLampiran'
        ));
        return $pdf->download($filename);
    }

    /**
     * Kumpulkan bukti kegiatan untuk lampiran PDF:
     * lampiran Evaluasi Diri (tingkat dokumen & indikator) plus lampiran temuan audit.
     */
    protected function collectBukti(array $edMap, $assignments)
    {
        $rows = collect();
        $seen = [];

        $push = function ($sumber, $judul, $att) use (&$rows, &$seen) {
            $key = $att->file_path ?: $att->link;
            if (!$key || isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $rows->push([
                'sumber'    => $sumber,
                'judul'     => $judul,
                'category'  => $att->category,
                'file_name' => $att->file_name ?: ($att->file_path ? basename($att->file_path) : null),
                'link'      => $att->link,
                'image'     => $att->file_path
                    ? $this->embeddedImage($att->file_path, 'public', 698, 950)
                    : null,
            ]);
        };

        foreach ($edMap as $ed) {
            if (!$ed) {
                continue;
            }
            $ed->loadMissing(['attachments', 'items.attachments']);
            $sumber = 'Evaluasi Diri — ' . $ed->owner_label;
            foreach ($ed->attachments as $att) {
                $push($sumber, $att->title ?: 'Bukti Evaluasi Diri', $att);
            }
            foreach ($ed->items as $item) {
                foreach ($item->attachments as $att) {
                    $push($sumber, $att->title ?: ($item->indicator ?: 'Bukti Evaluasi Diri'), $att);
                }
            }
        }

        foreach ($assignments as $assignment) {
            foreach ($assignment->findings ?? [] as $finding) {
                $sumber = 'Temuan ' . ($finding->type ?: 'AMI') . ' — ' . $assignment->auditee_label;
                foreach ($finding->attachments ?? [] as $att) {
                    $push($sumber, $att->title ?: 'Bukti Temuan', $att);
                }
            }
        }

        return $rows;
    }

    /**
     * Representasi gambar sebagai data URI + dimensi tampil (muat dalam kotak maksimum).
     * Mengembalikan null bila file bukan gambar yang didukung (mis. PDF/Word).
     */
    protected function embeddedImage(?string $path, string $disk, int $maxW, int $maxH): ?array
    {
        if (!$path) {
            return null;
        }
        try {
            if (!Storage::disk($disk)->exists($path)) {
                return null;
            }
            $abs = Storage::disk($disk)->path($path);
            if (!is_file($abs) || filesize($abs) > 8 * 1024 * 1024) {
                return null;
            }
            $info = @getimagesize($abs);
            if ($info === false || !in_array($info['mime'] ?? '', ['image/jpeg', 'image/png', 'image/gif'], true)) {
                return null;
            }
            $scale = min(1, $maxW / max(1, $info[0]), $maxH / max(1, $info[1]));

            return [
                'src' => 'data:' . $info['mime'] . ';base64,' . base64_encode(file_get_contents($abs)),
                'w'   => (int) max(1, round($info[0] * $scale)),
                'h'   => (int) max(1, round($info[1] * $scale)),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Logo institusi untuk running head: diperkecil via GD agar ringan
     * disisipkan berulang pada setiap halaman PDF.
     */
    protected function headerLogo(): ?array
    {
        $abs = public_path((string) setting('logo', 'images/logo.png'));
        if (!is_file($abs)) {
            return null;
        }
        try {
            $raw = file_get_contents($abs);
            if ($raw === false || $raw === '') {
                return null;
            }
            $img = @imagecreatefromstring($raw);
            if ($img) {
                $sw = imagesx($img);
                $sh = imagesy($img);
                $scale = min(76 / max(1, $sw), 76 / max(1, $sh));
                $w = max(1, (int) round($sw * $scale));
                $h = max(1, (int) round($sh * $scale));
                $out = imagecreatetruecolor($w, $h);
                imagealphablending($out, false);
                imagesavealpha($out, true);
                imagecopyresampled($out, $img, 0, 0, 0, 0, $w, $h, $sw, $sh);
                ob_start();
                imagepng($out);
                $png = ob_get_clean();
                imagedestroy($out);
                imagedestroy($img);

                return ['src' => 'data:image/png;base64,' . base64_encode($png), 'w' => $w, 'h' => $h];
            }
            // Fallback (mis. SVG) — sisipkan file apa adanya bila ukurannya wajar.
            $mime = match (strtolower(pathinfo($abs, PATHINFO_EXTENSION))) {
                'svg'       => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                default     => 'image/png',
            };
            if (strlen($raw) > 300000) {
                return null;
            }

            return ['src' => 'data:' . $mime . ';base64,' . base64_encode($raw), 'w' => 76, 'h' => 76];
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function scopedAssignments(int $cycleId, string $level, ?int $programId = null)
    {
        // Sinkron dengan data auditor: tampilkan semua penugasan pada siklus tersebut (berlangsung/pending/finalisasi/selesai)
        // Sebelumnya hanya finalisasi/selesai sehingga laporan kosong padahal auditor sudah menilai
        $query = AuditAssignment::where('audit_cycle_id', $cycleId);

        if ($level === 'prodi' && $programId) {
            $query->where('academic_program_id', $programId);
        }

        return $query->with(['academicProgram', 'unit', 'instruments', 'findings.attachments'])->get();
    }

    /**
     * Risk Register pada tahun akademik siklus (laporan AMI Berbasis Risiko).
     * Semester lama (NULL) atau Tahunan tetap disertakan agar data pra-semester tidak hilang.
     */
    protected function scopedRisks(AuditCycle $cycle, string $level, ?int $programId = null)
    {
        $query = RiskRegister::query()
            ->with(['academicProgram', 'unit'])
            ->where('academic_year', $cycle->academic_year)
            ->where(function ($q) use ($cycle) {
                $q->whereNull('semester')
                    ->orWhere('semester', 'Tahunan')
                    ->orWhere('semester', $cycle->semester);
            });

        if ($level === 'prodi' && $programId) {
            $query->where('academic_program_id', $programId);
        }

        return $query->orderByDesc('risk_score')->get();
    }

    protected function summarize($assignments)
    {
        $instruments = $assignments->flatMap->instruments;
        $findings = $assignments->flatMap->findings;

        return [
            'auditee_count' => $assignments->count(),
            'instrument_count' => $instruments->count(),
            'kts_mayor' => $instruments->filter(fn ($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Mayor'))->count(),
            'kts_minor' => $instruments->filter(fn ($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Minor'))->count(),
            'sesuai' => $instruments->filter(fn ($i) => in_array($i->finding_category, ['Melampaui Standar Nasional', 'Sesuai dengan Standar']))
                ->count(),
            'ob' => $findings->where('type', 'OB')->count(),
            'kts_findings' => $findings->where('type', 'KTS')->count(),
            'total_findings' => $findings->count(),
            'total_instruments' => $instruments->count(),
        ];
    }

    public function reviewDetail(AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        $assignment->load(['academicProgram', 'unit', 'cycle', 'instruments', 'findings', 'auditor']);
        // Evaluasi Diri auditee
        $evaluation = null;
        if ($assignment->academic_program_id) {
            $evaluation = Evaluation::where('evaluable_type', AcademicProgram::class)->where('evaluable_id', $assignment->academic_program_id)->latest()->first();
        } elseif ($assignment->unit_id) {
            $evaluation = Evaluation::where('evaluable_type', Unit::class)->where('evaluable_id', $assignment->unit_id)->latest()->first();
        }
        if ($evaluation) $evaluation->load(['items.attachments', 'items.standard', 'items.checklistItem']);
        // Risiko tinggi terkait
        $risks = collect();
        if ($assignment->academic_program_id) {
            $risks = \App\Models\RiskRegister::where('academic_program_id', $assignment->academic_program_id)->where('risk_level', 'High')->get();
        } elseif ($assignment->unit_id) {
            $risks = \App\Models\RiskRegister::where('unit_id', $assignment->unit_id)->where('risk_level', 'High')->get();
        }
        // RTL jika ada (dari RtmMeeting)
        $rtl = null;
        if ($assignment->audit_cycle_id) {
            $rtl = \App\Models\RtmMeeting::where('audit_cycle_id', $assignment->audit_cycle_id)->latest()->first();
        }
        return view('admin.reports.review', compact('assignment', 'evaluation', 'risks', 'rtl'));
    }

    public function approveLha(AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        $assignment->update(['status' => 'selesai']);
        return back()->with('success', 'LHA untuk ' . $assignment->auditee_label . ' disetujui (status: selesai).');
    }

    public function reminderAuditor(AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        // Notifikasi sederhana ke auditor (jika ada)
        if ($assignment->auditor) {
            $assignment->auditor->notify(new \Illuminate\Notifications\DatabaseNotification([
                'type' => 'audit_reminder',
                'assignment_id' => $assignment->id,
            ]));
        }
        return back()->with('success', 'Reminder dikirim ke auditor ' . ($assignment->auditor_name ?? '—') . ' untuk ' . $assignment->auditee_label . '.');
    }

    public function decideRtl(Request $request, AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        $request->validate(['decision' => 'required|in:setujui,tolak,eskalasi', 'catatan' => 'nullable|string|max:2000']);
        $decision = $request->input('decision');
        if ($decision === 'setujui') {
            $assignment->update(['status' => 'selesai']);
            $msg = 'RTL disetujui. LHA disahkan.';
        } elseif ($decision === 'tolak') {
            $assignment->update(['status' => 'berlangsung']);
            $msg = 'RTL ditolak, diminta revisi. Status dikembalikan ke berlangsung.';
        } else {
            // eskalasi ke RTM - buat entri RTM jika belum ada
            $rtm = \App\Models\RtmMeeting::firstOrCreate(
                ['audit_cycle_id' => $assignment->audit_cycle_id],
                ['title' => 'RTM Eskalasi - ' . $assignment->auditee_label, 'status' => 'draft', 'created_by' => auth()->id()]
            );
            $msg = 'Dieskalasi ke RTM (ID: ' . $rtm->id . '). Rektor akan memutuskan.';
        }
        // Simpan catatan jika ada
        if ($request->filled('catatan')) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'spmi_decide_rtl_' . $decision,
                'description' => 'SPMI ' . $decision . ' RTL untuk ' . $assignment->auditee_label . ': ' . $request->input('catatan'),
                'loggable_type' => AuditAssignment::class,
                'loggable_id' => $assignment->id,
            ]);
        }
        return back()->with('success', $msg);
    }
}