@extends('layouts.admin')

@section('title', 'Laporan & Hasil AMI')

@section('content')
<div class="card card-custom shadow-sm mb-4 border-0 border-top border-4 border-primary">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-file-pdf me-2"></i> Laporan Hasil Audit Mutu Internal (LHA)</h5>
            <p class="text-muted small mb-0">Lihat ringkasan eksekutif dan unduh Laporan Hasil Audit (LHA) dalam format resmi per siklus — level institusi maupun per program studi.</p>
        </div>
    </div>

    <div class="card-body p-4 pt-3">

        {{-- Filter: Siklus + Tingkat Laporan + Prodi --}}
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end p-4 bg-primary bg-opacity-10 rounded-4" id="filterReportsForm">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark">Siklus AMI <span class="text-danger">*</span></label>
                <select name="cycle_id" class="form-select border-0 shadow-sm py-3 px-4 rounded-pill" id="cycleSelect" required>
                    <option value="" disabled {{ !$cycleId ? 'selected' : '' }}>Pilih periode siklus...</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ $cycleId == $cycle->id ? 'selected' : '' }}>
                            {{ $cycle->name }} ({{ $cycle->academic_year }} Sem. {{ $cycle->semester }})
                            [{{ strtoupper($cycle->status) }}]
                        </option>
                    @endforeach
                </select>
                @if($cycles->isEmpty())
                    <div class="text-danger small mt-2 fw-semibold ps-3">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        Belum ada siklus AMI. @hasrole('spmi')<a href="{{ route('admin.audit.cycles.create') }}">Buat siklus terlebih dahulu.</a>@endhasrole
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark">Cetak Berdasarkan <span class="text-danger">*</span></label>
                <select name="level" class="form-select border-0 shadow-sm py-3 px-4 rounded-pill" id="levelSelect" {{ $isProdi ? 'disabled' : '' }}>
                    <option value="institusi" {{ (!$isProdi && $level === 'institusi') ? 'selected' : '' }}>Rekapitulasi Institusi</option>
                    <option value="prodi" {{ $level === 'prodi' ? 'selected' : '' }}>Per Program Studi</option>
                </select>
                @if($isProdi)
                    <div class="form-text small mt-1">Akun Anda (Kaprodi) hanya dapat mengakses LHA program studi Anda.</div>
                @endif
            </div>

            <div class="col-md-4" id="programCol">
                <label class="form-label fw-bold small text-dark">Program Studi <span class="text-danger" id="programRequired">*</span></label>
                <select name="academic_program_id" class="form-select border-0 shadow-sm py-3 px-4 rounded-pill" id="programSelect" {{ $level === 'institusi' ? 'disabled' : '' }}>
                    <option value="" disabled {{ !$programId ? 'selected' : '' }}>Pilih program studi...</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->degree_level }} {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Poin catatan client: pilihan 2 jenis laporan AMI (sesuai contoh PDF lampiran) --}}
            <div class="col-12">
                <label class="form-label fw-bold small text-dark">Jenis Laporan AMI <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="border rounded-4 p-3 d-block h-100 bg-white shadow-sm">
                            <div class="d-flex align-items-start gap-2">
                                <input type="radio" name="jenis" value="klasik" class="mt-1" {{ request('jenis', 'klasik') !== 'berbasis-risiko' ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-bold small">1. AMI Klasik &mdash; Laporan Kegiatan AMI</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Sesuai contoh lampiran 2024: cover, lembar pengesahan, daftar tim auditee, BAB I&ndash;IV, dan lampiran Daftar Tilik per standar.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="border rounded-4 p-3 d-block h-100 bg-white shadow-sm">
                            <div class="d-flex align-items-start gap-2">
                                <input type="radio" name="jenis" value="berbasis-risiko" class="mt-1" {{ request('jenis') === 'berbasis-risiko' ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-bold small">2. AMI Berbasis Risiko</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Sesuai contoh lampiran 2025: skala Dampak (1&ndash;5) &times; Likelihood (1&ndash;5), Tingkat Risiko, dan Rekomendasi/Tindak Lanjut dari Risk Register.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold shadow">
                    <i class="fa-solid fa-magnifying-glass-chart me-2"></i> Lihat Ringkasan
                </button>
                <button type="button" class="btn btn-success px-4 rounded-pill fw-bold shadow" id="generatePdfBtn">
                    <i class="fa-solid fa-file-pdf me-2"></i> Generate Laporan
                </button>
                <a href="{{ route('admin.reports.generated.index') }}" class="btn btn-outline-danger px-4 rounded-pill fw-bold shadow">
                    <i class="fa-solid fa-list-check me-2"></i> Daftar Generate Laporan
                </a>
                <div class="w-100 text-muted small mt-1 text-end">
                    <i class="fa-solid fa-circle-info me-1"></i> Generate Laporan masuk ke daftar — di sana lampiran (SK, bukti kegiatan, upload manual) disusun &amp; PDF diarsipkan.
                    Cover, kode &amp; edisi diatur pada menu <strong>Konfigurasi Aplikasi</strong> (Administrator).
                </div>
            </div>
        </form>

        {{-- Form untuk membuat draft Generate Laporan (masuk daftar & detail lampiran) --}}
        <form action="{{ route('admin.reports.generated.store') }}" method="POST" id="pdfForm">
            @csrf
            <input type="hidden" name="cycle_id" id="pdfCycleId">
            <input type="hidden" name="level" id="pdfLevel">
            <input type="hidden" name="academic_program_id" id="pdfProgramId">
            <input type="hidden" name="jenis" id="pdfJenis">
        </form>

        {{-- Ringkasan Eksekutif --}}
        @if($summary)
            <div class="mt-4">
                <h6 class="fw-bold text-muted text-uppercase tracking-wider mb-3" style="font-size: 0.8rem; letter-spacing: 1px;">
                    <i class="fa-solid fa-chart-simple me-2"></i>Ringkasan Eksekutif
                    @if($level === 'prodi' && $programId && $reportedAssignments->isNotEmpty())
                        — {{ $reportedAssignments->first()->auditee_label }}
                    @elseif($level === 'institusi')
                        — Seluruh Auditee Siklus
                    @endif
                </h6>

                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2"><i class="fa-solid fa-circle-xmark text-danger"></i><span class="small text-danger fw-bold">KTS MAYOR</span></div>
                            <div class="fs-2 fw-bold text-danger">{{ $summary['kts_mayor'] }}</div>
                            <div class="small text-muted">Indikator Tidak Tercapai Berat / Sistem Tidak Berjalan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-25 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2"><i class="fa-solid fa-circle-exclamation text-warning"></i><span class="small text-dark fw-bold">KTS MINOR</span></div>
                            <div class="fs-2 fw-bold text-warning">{{ $summary['kts_minor'] }}</div>
                            <div class="small text-muted">Indikator Tidak Tercapai Ringan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-25 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2"><i class="fa-solid fa-eye text-info"></i><span class="small text-info fw-bold">OBSERVASI (OB)</span></div>
                            <div class="fs-2 fw-bold text-info">{{ $summary['ob'] }}</div>
                            <div class="small text-muted">Catatan Temuan Observasi</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2"><i class="fa-solid fa-circle-check text-success"></i><span class="small text-success fw-bold">SESUAI / MELAMPAUI</span></div>
                            <div class="fs-2 fw-bold text-success">{{ $summary['sesuai'] }}</div>
                            <div class="small text-muted">Kategori Ketercapaian</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <div class="small text-muted fw-bold">Auditee Dilaporkan</div>
                            <div class="fs-4 fw-bold text-dark">{{ $summary['auditee_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <div class="small text-muted fw-bold">Borang (Indikator) Dinilai</div>
                            <div class="fs-4 fw-bold text-dark">{{ $summary['instrument_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <div class="small text-muted fw-bold">Total Temuan (KTS + OB)</div>
                            <div class="fs-4 fw-bold text-dark">{{ $summary['total_findings'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <div class="small text-muted fw-bold">Temuan KTS (RTL)</div>
                            <div class="fs-4 fw-bold text-dark">{{ $summary['kts_findings'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Level 2: Tabel Rekapitulasi per Prodi (sesuai mockup SPMI) --}}
                @if($reportedAssignments->isNotEmpty())
                    <div class="mt-4">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="fa-solid fa-table-list me-2 text-primary"></i>Tabel Rekapitulasi per Prodi — {{ $level === 'prodi' ? ($reportedAssignments->first()->auditee_label ?? 'Per Prodi') : 'Per Program Studi' }}</h6>
                        <p class="text-muted small mb-2">Transparansi per prodi: Skor Evaluasi Diri (klaim) vs Skor Audit (riil), total temuan KTS, dan status LHA. Data sinkron dengan <em>Borang Audit</em> auditor.</p>
                        <div class="table-responsive border rounded-3 bg-white shadow-sm">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 36px;">#</th>
                                        <th>Nama Prodi / Unit</th>
                                        <th class="text-center">Skor Evaluasi Diri (Klaim)</th>
                                        <th class="text-center">Skor Audit (Nilai Riil)</th>
                                        <th class="text-center">Total Temuan (KTS)</th>
                                        <th class="text-center">Status LHA</th>
                                        <th class="text-center" style="min-width: 240px;">Action untuk SPMI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportedAssignments as $idx => $assignment)
                                        @php
                                            $instList = $assignment->instruments;
                                            $perMayor = $instList->filter(fn($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Mayor'))->count();
                                            $perMinor = $instList->filter(fn($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Minor'))->count();
                                            $perTotalKts = $perMayor + $perMinor;
                                            // Skor Evaluasi Diri (rata-rata EvaluationItem.score) & Skor Audit (rata-rata AuditInstrument.score)
                                            $evalForScore = null;
                                            if ($assignment->academic_program_id) {
                                                $evalForScore = \App\Models\Evaluation::where('evaluable_type', \App\Models\AcademicProgram::class)->where('evaluable_id', $assignment->academic_program_id)->latest()->first();
                                            } elseif ($assignment->unit_id) {
                                                $evalForScore = \App\Models\Evaluation::where('evaluable_type', \App\Models\Unit::class)->where('evaluable_id', $assignment->unit_id)->latest()->first();
                                            }
                                            $edScore = $evalForScore ? round($evalForScore->items()->avg('score') ?? 0, 2) : null;
                                            $auditScore = $instList->count() ? round($instList->avg('score') ?? 0, 2) : null;
                                            $lhaStatus = $assignment->status === 'selesai' ? 'Audit Selesai' : ($assignment->status === 'finalisasi' ? 'Finalisasi' : ($assignment->status === 'berlangsung' ? 'Proses' : 'Belum Diaudit'));
                                            $lhaBadge = $assignment->status === 'selesai' ? 'bg-success' : ($assignment->status === 'finalisasi' ? 'bg-primary' : ($assignment->status === 'berlangsung' ? 'bg-warning text-dark' : 'bg-secondary'));
                                            $ktsLabel = $perTotalKts ? $perTotalKts . ' KTS (' . $perMayor . ' Mayor, ' . $perMinor . ' Minor)' : '—';
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $assignment->auditee_label }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $assignment->auditee_type_label }} • {{ $assignment->instruments->count() }} borang</div>
                                            </td>
                                            <td class="text-center">
                                                @if($edScore !== null && $edScore > 0)
                                                    <span class="badge bg-info bg-opacity-10 text-info border rounded-pill px-3">{{ number_format($edScore,2) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($auditScore !== null && $instList->count())
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border rounded-pill px-3">{{ number_format($auditScore,2) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($perTotalKts)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border rounded-pill px-2">{{ $ktsLabel }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center"><span class="badge {{ $lhaBadge }} rounded-pill px-3">{{ $lhaStatus }}</span></td>
                                            <td class="text-center admin-actions-cell"><div class="admin-table-actions">
                                                <div class="admin-table-action-group">
                                                    <a href="{{ route('admin.reports.review', $assignment->id) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Review Detail" aria-label="Review Detail"><i aria-hidden="true" class="fa-solid fa-magnifying-glass"></i></a>
                                                    @if($assignment->status === 'selesai')
                                                        <span class="btn btn-sm btn-outline-secondary icon-only-btn admin-table-action disabled" title="Approved" aria-label="Approved" aria-disabled="true"><i aria-hidden="true" class="fa-solid fa-check"></i></span>
                                                    @elseif(in_array($assignment->status, ['finalisasi','berlangsung','pending']))
                                                        <form action="{{ route('admin.reports.approve', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve LHA untuk {{ $assignment->auditee_label }}?')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-success icon-only-btn admin-table-action" title="Approve LHA" aria-label="Approve LHA"><i aria-hidden="true" class="fa-solid fa-check"></i></button>
                                                        </form>
                                                    @endif
                                                    @if(!in_array($assignment->status, ['selesai']) && $assignment->status !== 'draft')
                                                        <form action="{{ route('admin.reports.reminder', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Kirim reminder ke auditor untuk {{ $assignment->auditee_label }}?')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Reminder" aria-label="Reminder"><i aria-hidden="true" class="fa-solid fa-bell"></i></button>
                                                        </form>
                                                    @elseif($assignment->status === 'draft' || !$assignment->status)
                                                        <form action="{{ route('admin.reports.reminder', $assignment->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-2">Reminder Auditor</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-muted small mt-2"><i class="fa-solid fa-circle-info me-1"></i> Contoh baris sesuai mockup: <em>Teknik Informatika 3.50 → 2.20 | 4 KTS (1 Mayor, 3 Minor) | Audit Selesai | [Review Detail] [Approve LHA]</em></div>
                    </div>
                @endif

                {{-- Tabel auditee per tingkat prodi (legacy, tetap untuk institusi) --}}
                @if($level === 'institusi' && $reportedAssignments->isNotEmpty())
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped align-middle bg-white">
                            <thead class="table-secondary">
                                <tr>
                                    <th class="small text-center">#</th>
                                    <th class="small">Auditee</th>
                                    <th class="small text-center">Tipe</th>
                                    <th class="small text-center">Auditor</th>
                                    <th class="small text-center">Borang</th>
                                    <th class="small text-center">KTS Mayor</th>
                                    <th class="small text-center">KTS Minor</th>
                                    <th class="small text-center">OB</th>
                                    <th class="small text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportedAssignments as $idx => $assignment)
                                    @php
                                        $instList = $assignment->instruments;
                                        $perMayor = $instList->filter(fn($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Mayor'))->count();
                                        $perMinor = $instList->filter(fn($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Minor'))->count();
                                        $perOb = $assignment->findings->where('type', 'OB')->count();
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td class="fw-semibold">{{ $assignment->auditee_label }}</td>
                                        <td class="text-center">{{ $assignment->auditee_type_label }}</td>
                                        <td class="small">{{ $assignment->auditor_name }}</td>
                                        <td class="text-center">{{ $instList->count() }}</td>
                                        <td class="text-center text-danger fw-bold">{{ $perMayor }}</td>
                                        <td class="text-center text-warning fw-bold">{{ $perMinor }}</td>
                                        <td class="text-center text-info fw-bold">{{ $perOb }}</td>
                                        <td class="text-center"><span class="badge bg-dark">{{ strtoupper($assignment->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @elseif(!$cycles->isEmpty())
            <div class="alert alert-light text-center mt-4 py-5">
                <i class="fa-solid fa-chart-simple fa-2x text-muted mb-3 d-block"></i>
                <strong>Pilih siklus AMI lalu klik "Lihat Ringkasan"</strong>
                <div class="small text-muted mt-1">Ringkasan eksekutif (KTS Mayor / KTS Minor / OB) akan tampil di sini sebelum diunduh sebagai PDF.</div>
            </div>
        @endif

        {{-- Fitur Laporan --}}
        <div class="mt-5 text-center">
            <h6 class="fw-bold text-muted text-uppercase tracking-wider mb-4" style="font-size: 0.8rem; letter-spacing: 1px;">Fitur Laporan Otomatis</h6>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="p-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <h6 class="fw-bold">Format LHA Resmi</h6>
                        <p class="text-muted small">PDF berisi cover, lembar pengesahan + tanda tangan, ringkasan temuan, borang/daftar tilik, serta lampiran SK, dokumen siklus, dan bukti kegiatan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3">
                        <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <h6 class="fw-bold">Level Institusi & Prodi</h6>
                        <p class="text-muted small">Cetak rekapitulasi seluruh institusi, atau per program studi untuk kebutuhan LHA masing-masing auditee.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3">
                        <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <h6 class="fw-bold">Efisiensi Waktu</h6>
                        <p class="text-muted small">Menghemat waktu merekap data laporan karena seluruh instrumen dan temuan tersalin otomatis ke format baku.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cycleSelect = document.getElementById('cycleSelect');
        const levelSelect = document.getElementById('levelSelect');
        const programSelect = document.getElementById('programSelect');
        const pdfForm = document.getElementById('pdfForm');
        const generateBtn = document.getElementById('generatePdfBtn');
        const programRequired = document.getElementById('programRequired');
        const isProdi = {{ $isProdi ? 'true' : 'false' }};

        function syncProgramState() {
            const isProdiLevel = isProdi || levelSelect.value === 'prodi';
            programSelect.disabled = !isProdiLevel;
            programRequired.style.display = isProdiLevel ? '' : 'none';
        }

        if (levelSelect) {
            levelSelect.addEventListener('change', syncProgramState);
        }

        generateBtn.addEventListener('click', function (e) {
            if (!cycleSelect.value) {
                e.preventDefault();
                cycleSelect.focus();
                return;
            }
            const level = isProdi ? 'prodi' : levelSelect.value;
            const programId = level === 'prodi' ? programSelect.value : '';

            if (level === 'prodi' && !programId) {
                e.preventDefault();
                programSelect.focus();
                return;
            }

            const jenisEl = document.querySelector('input[name="jenis"]:checked');
            if (!jenisEl) {
                e.preventDefault();
                return;
            }

            document.getElementById('pdfCycleId').value = cycleSelect.value;
            document.getElementById('pdfLevel').value = level;
            document.getElementById('pdfProgramId').value = programId || '';
            document.getElementById('pdfJenis').value = jenisEl.value;
            pdfForm.submit();
        });

        syncProgramState();
    });
</script>
@endpush