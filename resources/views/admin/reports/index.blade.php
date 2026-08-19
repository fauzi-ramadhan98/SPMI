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

            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold shadow">
                    <i class="fa-solid fa-magnifying-glass-chart me-2"></i> Lihat Ringkasan
                </button>
                <button type="button" class="btn btn-success px-4 rounded-pill fw-bold shadow" id="generatePdfBtn">
                    <i class="fa-solid fa-file-export me-2"></i> Generate Laporan PDF (LHA)
                </button>
            </div>
        </form>

        {{-- Form tersembunyi untuk download PDF --}}
        <form action="{{ route('admin.reports.ami_pdf') }}" method="POST" id="pdfForm" target="_blank">
            @csrf
            <input type="hidden" name="cycle_id" id="pdfCycleId">
            <input type="hidden" name="level" id="pdfLevel">
            <input type="hidden" name="academic_program_id" id="pdfProgramId">
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
                            <div class="small text-muted">Indikator Tidak Tercapai Sedang</div>
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

                {{-- Tabel auditee per tingkat prodi --}}
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
                        <p class="text-muted small">PDF berisi cover, lembar pengesahan + tanda tangan, ringkasan temuan, dan borang/daftar tilik pada lampiran.</p>
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

            document.getElementById('pdfCycleId').value = cycleSelect.value;
            document.getElementById('pdfLevel').value = level;
            document.getElementById('pdfProgramId').value = programId || '';
            pdfForm.submit();
        });

        syncProgramState();
    });
</script>
@endpush