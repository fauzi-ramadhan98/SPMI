<?php

namespace App\Services;

use App\Models\AcademicProgram;
use App\Models\AuditAssignment;
use App\Models\AuditCycle;
use App\Models\Document;
use App\Models\Evaluation;
use App\Models\QualityStandard;
use App\Models\RiskRegister;
use App\Models\StandardDecree;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sumber data Laporan AMI.
 *
 * Dipakai dua jalur pembuatan PDF agar isi badan laporan selalu konsisten:
 *  - ReportController::generateAmiPdf  → cetak cepat (langsung unduh)
 *  - ReportBuilderController           → Generate Laporan (daftar + lampiran terurut + arsip)
 */
class AmiReportService
{
    /**
     * Susun seluruh data view untuk template PDF Laporan AMI.
     */
    public function assembleData(AuditCycle $cycle, string $level, ?int $programId, ?string $jenis = null): array
    {
        $jenis = in_array($jenis, ['klasik', 'berbasis-risiko'], true) ? $jenis : 'klasik';

        $assignments = $this->scopedAssignments($cycle->id, $level, $programId);
        $summary = $this->summarize($assignments);
        $program = $level === 'prodi' ? AcademicProgram::find($programId) : null;
        $standars = QualityStandard::with('checklistItems')->orderBy('kode_standar')->get();
        $edMap = $this->edMap($assignments);

        // Risk Register pada tahun akademik siklus (jenis berbasis risiko)
        $risks = collect();
        if ($jenis === 'berbasis-risiko') {
            $risks = $this->scopedRisks($cycle, $level, $programId);
        }

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

        // ===== Lampiran tabel (dipakai jalur cetak cepat; builder memakai "Daftar Lampiran" terurut) =====
        $decrees = $this->cycleDecrees($cycle);

        $decreesLampiran = $decrees->map(fn ($d) => [
            'sk_no'     => $d->sk_no,
            'judul'     => $d->judul,
            'kategori'  => $d->kategori ?: 'penetapan',
            'tanggal'   => $d->tanggal_sk,
            'status'    => $d->status ?: 'draft',
            'file_name' => $d->file_name,
            'image'     => $d->file_path ? $this->embeddedImage($d->file_path, 'local', 698, 950) : null,
        ]);

        $docsLampiran = $this->cycleDocuments($cycle, $decrees)->map(fn ($doc) => [
            'code'      => $doc->code,
            'title'     => $doc->title,
            'module'    => $doc->module,
            'doc_date'  => $doc->doc_date,
            'file_name' => $doc->file_name,
            'image'     => $doc->file_path ? $this->embeddedImage($doc->file_path, 'local', 698, 950) : null,
        ]);

        // ===== Lampiran: bukti kegiatan (Evaluasi Diri & Temuan Audit) =====
        $buktiLampiran = $this->collectBukti($edMap, $assignments);

        return compact(
            'cycle', 'assignments', 'summary', 'level', 'program',
            'jenis', 'standars', 'edMap', 'risks',
            'showCover', 'coverImage', 'headerLogo',
            'reportInst', 'reportKode', 'reportEdisi',
            'decreesLampiran', 'docsLampiran', 'buktiLampiran'
        );
    }

    /**
     * Render badan PDF laporan (belum termasuk penggabungan lampiran PDF).
     *
     * @param  bool  $builderMode  true = tampilkan "Daftar Lampiran" terurut
     *                             (file menyusul via PdfAssembler), false = lampiran tabel lama.
     */
    public function renderBody(array $data, bool $builderMode = false, ?Collection $lampiranRows = null): string
    {
        $data['builderMode'] = $builderMode;
        $data['lampiranRows'] = $lampiranRows ?? collect();

        $view = ($data['jenis'] ?? 'klasik') === 'berbasis-risiko'
            ? 'admin.reports.ami_risiko_pdf'
            : 'admin.reports.ami_klasik_pdf';

        return Pdf::loadView($view, $data)->output();
    }

    /**
     * Nama file unduhan laporan (dipakai cetak cepat & arsip builder).
     */
    public function filename(AuditCycle $cycle, string $level, ?AcademicProgram $program, string $jenis): string
    {
        $safeYear = str_replace(['/', '\\'], '-', $cycle->academic_year);
        $jenisSlug = $jenis === 'berbasis-risiko' ? 'berbasis-risiko' : 'klasik';

        return 'laporan-ami-' . $jenisSlug . '-'
            . $safeYear . '-'
            . $cycle->semester
            . ($level === 'prodi' ? '-' . Str::slug($program->name ?? 'prodi') : '')
            . '.pdf';
    }

    /**
     * Penugasan pada siklus (terscope jenjang/prodi bila laporan per prodi).
     */
    public function scopedAssignments(int $cycleId, string $level, ?int $programId = null): Collection
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
     * Evaluasi Diri terbaru per auditee (untuk Lampiran Daftar Tilik & bukti kegiatan).
     */
    public function edMap(iterable $assignments): array
    {
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

        return $edMap;
    }

    /**
     * Ringkasan eksekutif (KTS Mayor/Minor, OB, dsb.) dari penugasan.
     */
    public function summarize($assignments): array
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

    /**
     * Risk Register pada tahun akademik siklus (laporan AMI Berbasis Risiko).
     * Semester lama (NULL) atau Tahunan tetap disertakan agar data pra-semester tidak hilang.
     */
    public function scopedRisks(AuditCycle $cycle, string $level, ?int $programId = null): Collection
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

    /**
     * SK pada siklus (urut tanggal terbit).
     */
    public function cycleDecrees(AuditCycle $cycle): Collection
    {
        return StandardDecree::where('audit_cycle_id', $cycle->id)
            ->orderByRaw('tanggal_sk IS NULL')
            ->orderBy('tanggal_sk')
            ->orderBy('id')
            ->get();
    }

    /**
     * Dokumen terkait siklus — milik siklus atau melekat pada SK siklus tersebut.
     */
    public function cycleDocuments(AuditCycle $cycle, Collection $decrees): Collection
    {
        return Document::where(function ($q) use ($cycle, $decrees) {
                $q->where('audit_cycle_id', $cycle->id)
                    ->orWhereIn('standard_decree_id', $decrees->pluck('id'));
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Kumpulkan bukti kegiatan untuk lampiran PDF:
     * lampiran Evaluasi Diri (tingkat dokumen & indikator) plus lampiran temuan audit.
     *
     * @return Collection<int, array> baris: sumber, judul, category, file_name, link, file_path,
     *                                 source_type, source_id, disk, image (data URI, bila gambar)
     */
    public function collectBukti(array $edMap, $assignments): Collection
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
                'sumber'     => $sumber,
                'judul'      => $judul,
                'category'   => $att->category,
                'file_name'  => $att->file_name ?: ($att->file_path ? basename($att->file_path) : null),
                'link'       => $att->link,
                'file_path'  => $att->file_path,
                'source_type' => $att::class,
                'source_id'  => $att->id,
                'disk'       => 'public',
                'image'      => $att->file_path
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
     * Kandidat lampiran dari data existing: SK siklus, dokumen terkait, bukti
     * Evaluasi Diri & temuan audit. Hanya yang memiliki berkas, dedup per path.
     *
     * Dipakai untuk auto-isi lampiran saat laporan dibuat dan dropdown
     * "pilih lampiran" pada halaman detail.
     *
     * @return Collection<int, array{title: string, source_label: string, source_type: string,
     *                                source_id: int, file_path: string, disk: string}>
     */
    public function attachmentCandidates(AuditCycle $cycle, string $level, ?int $programId): Collection
    {
        $rows = collect();
        $seen = [];
        $add = function (string $label, string $title, string $type, int $id, string $path, string $disk) use (&$rows, &$seen) {
            if ($path === '' || isset($seen[$path])) {
                return;
            }
            $seen[$path] = true;
            $rows->push([
                'title'        => $title,
                'source_label' => $label,
                'source_type'  => $type,
                'source_id'    => $id,
                'file_path'    => $path,
                'disk'         => $disk,
            ]);
        };

        $decrees = $this->cycleDecrees($cycle);
        foreach ($decrees as $d) {
            if ($d->file_path) {
                $add(
                    'SK ' . ucfirst($d->kategori ?: 'penetapan'),
                    'SK ' . $d->sk_no . ' — ' . $d->judul,
                    StandardDecree::class,
                    (int) $d->id,
                    $d->file_path,
                    'local'
                );
            }
        }

        foreach ($this->cycleDocuments($cycle, $decrees) as $doc) {
            if ($doc->file_path) {
                $add(
                    'Dokumen siklus',
                    trim(($doc->code ?: 'Dokumen') . ' — ' . $doc->title, ' —'),
                    Document::class,
                    (int) $doc->id,
                    $doc->file_path,
                    'local'
                );
            }
        }

        $assignments = $this->scopedAssignments($cycle->id, $level, $programId);
        foreach ($this->collectBukti($this->edMap($assignments), $assignments) as $b) {
            if (!empty($b['file_path'])) {
                $add($b['sumber'], $b['judul'], $b['source_type'], (int) $b['source_id'], $b['file_path'], 'public');
            }
        }

        return $rows;
    }

    /**
     * Representasi gambar sebagai data URI + dimensi tampil (muat dalam kotak maksimum).
     * Mengembalikan null bila file bukan gambar yang didukung (mis. PDF/Word).
     */
    public function embeddedImage(?string $path, string $disk, int $maxW, int $maxH): ?array
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
    public function headerLogo(): ?array
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
                'svg'        => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                default      => 'image/png',
            };
            if (strlen($raw) > 300000) {
                return null;
            }

            return ['src' => 'data:' . $mime . ';base64,' . base64_encode($raw), 'w' => 76, 'h' => 76];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
