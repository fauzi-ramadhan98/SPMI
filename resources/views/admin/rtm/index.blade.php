@extends('layouts.admin')

@section('title', 'Rapat Tinjauan Manajemen (RTM)')

@section('content')
@php
    $isSpmi = auth()->user()->hasRole('spmi');
    $isPimpinan = auth()->user()->hasRole('pimpinan');
    $isAuditee = auth()->user()->hasRole('prodi|unit');
    $isAuditor = auth()->user()->hasRole('auditor');
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-landmark me-2 text-primary"></i>Rapat Tinjauan Manajemen (RTM)</h4>
        <p class="text-muted mb-0 small">Modul Pengendalian (P3) — Jadwal, risalah rapat, dan instruksi tindak lanjut hasil AMI.</p>
    </div>
    @if($isSpmi)
        <a href="{{ route('admin.rtm.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
            <i class="fa-solid fa-plus me-1"></i> Buat Jadwal RTM
        </a>
    @endif
</div>

@if($isAuditee)
    {{-- ===== PRODI / UNIT: read-only, hanya risalah disahkan ===== --}}
    <div class="alert alert-light border rounded-4 shadow-sm">
        <i class="fa-solid fa-circle-info me-2 text-primary"></i>
        Anda melihat <strong>Risalah RTM yang telah disahkan</strong> oleh Pimpinan dan memuat instruksi untuk auditee Anda. Download &amp; baca instruksi strategis sebagai pedoman tahun akademik berikutnya.
    </div>

    @forelse($meetings as $meeting)
        <div class="card card-custom shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <h5 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-gavel me-2"></i>{{ $meeting->title }}</h5>
                        <div class="text-muted small">
                            <i class="fa-regular fa-calendar me-1"></i>{{ $meeting->meeting_date?->format('d M Y') }}
                            @if($meeting->meeting_time) · {{ $meeting->meeting_time->format('H:i') }} @endif
                            @if($meeting->location) · {{ $meeting->location }} @endif
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i> Disahkan
                        @if($meeting->approved_at) · {{ $meeting->approved_at->format('d M Y') }} @endif
                    </span>
                </div>
            </div>
            <div class="card-body px-4 py-3">
                <h6 class="fw-bold text-uppercase small text-muted mb-2">Instruksi untuk Auditee Anda:</h6>
                <ul class="list-group list-group-flush">
                    @foreach($meeting->instructions as $instruction)
                        @php
                            $isMine = ($instruction->academic_program_id && $instruction->academic_program_id === auth()->user()->academic_program_id)
                                   || ($instruction->unit_id && $instruction->unit_id === auth()->user()->unit_id);
                        @endphp
                        @if($isMine)
                        <li class="list-group-item px-0 bg-transparent">
                            <div class="d-flex gap-3">
                                <i class="fa-solid fa-arrow-right text-success mt-1"></i>
                                <div>
                                    <div>{{ $instruction->instruction }}</div>
                                    <div class="small text-muted mt-1">
                                        @if($instruction->target_date)
                                            <i class="fa-regular fa-clock me-1"></i>Target: {{ $instruction->target_date->format('d M Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endif
                    @endforeach
                </ul>
                <a href="{{ route('admin.rtm.pdf', $meeting->id) }}" class="btn btn-sm btn-outline-primary rounded-pill mt-2">
                    <i class="fa-solid fa-file-pdf me-1"></i> Unduh Risalah RTM (PDF)
                </a>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted bg-white rounded-4 shadow-sm">
            <i class="fa-solid fa-file-circle-check fs-1 mb-3 opacity-25"></i>
            <p class="mb-0 fw-semibold">Belum ada risalah RTM yang disahkan untuk auditee Anda.</p>
            <small>Instruksi strategis dari Pimpinan akan tampil di sini setelah RTM resmi diketuk palu.</small>
        </div>
    @endforelse
@elseif($isPimpinan)
    {{-- ===== PIMPINAN: read-only + executive summary + Sahkan ===== --}}
    <div class="alert alert-light border rounded-4 shadow-sm mb-4">
        <i class="fa-solid fa-gavel me-2 text-primary"></i>
        Anda tidak perlu mengetik apa pun. Pelajari ringkasan eksekutif berikut, lalu gunakan tombol <strong>Sahkan</strong> pada risalah yang telah diajukan SPMI.
    </div>

    {{-- Executive summary --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Butir Dinilai</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $summary['total_scored'] }}</div>
                    <small class="text-muted">instrumen ber-skor</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Rata-rata Skor</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $summary['avg_score'] !== null ? number_format($summary['avg_score'], 2) : '—' }} <small class="text-muted">/4</small></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-danger border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">Temuan KTS</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $summary['kts'] }}</div>
                    <small class="text-muted">dari {{ $summary['total_findings'] }} total temuan</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Observasi (OB)</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $summary['ob'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik temuan per auditee --}}
    <div class="card card-custom shadow-sm mb-4">
        <div class="card-header-custom">
            <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-chart-column me-2"></i>Temuan AMI per Program Studi / Unit Kerja</h6>
        </div>
        <div class="card-body">
            <canvas id="chartTemuan" height="110"></canvas>
        </div>
    </div>

    {{-- Daftar RTM + tombol sahkan --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-landmark me-2 text-primary"></i>Daftar RTM</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">RTM</th>
                            <th class="py-3">Siklus</th>
                            <th class="py-3 text-center">Jadwal</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center">Peserta</th>
                            <th class="py-3 pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $meeting)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $meeting->title }}</div>
                                <div class="text-muted small">{{ $meeting->location ?? 'Lokasi belum diatur' }}</div>
                            </td>
                            <td class="small">{{ $meeting->cycle?->name }}</td>
                            <td class="small text-center">{{ $meeting->meeting_date?->format('d M Y') }}<br>{{ $meeting->meeting_time?->format('H:i') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $meeting->status_color }} bg-opacity-10 text-{{ $meeting->status_color }} border border-{{ $meeting->status_color }} border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                    {{ $meeting->status_label }}
                                </span>
                            </td>
                            <td class="text-center">{{ $meeting->participants->count() }}</td>
                            <td class="py-3 pe-4 text-end">
                                <a href="{{ route('admin.rtm.show', $meeting->id) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1">
                                    <i class="fa-solid fa-eye me-1"></i> Baca
                                </a>
                                @if($meeting->status === 'notulensi')
                                    <form action="{{ route('admin.rtm.approve', $meeting->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" name="approve" value="1" class="btn btn-sm btn-success rounded-pill shadow-sm">
                                            <i class="fa-solid fa-gavel me-1"></i> Sahkan Risalah
                                        </button>
                                    </form>
                                @elseif($meeting->status === 'disahkan')
                                    <form action="{{ route('admin.rtm.approve', $meeting->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" name="approve" value="0" class="btn btn-sm btn-outline-warning rounded-pill">
                                            <i class="fa-solid fa-xmark me-1"></i> Batalkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada jadwal RTM.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    {{-- ===== SPMI (operator) / Auditor: manajemen penuh ===== --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Jadwal RTM</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $meetings->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-info border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">Menunggu Pengesahan</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $meetings->where('status', 'notulensi')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Disahkan</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $meetings->where('status', 'disahkan')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Berlangsung</div>
                    <div class="h5 mb-0 fw-bold text-dark">{{ $meetings->where('status', 'berlangsung')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-landmark me-2 text-primary"></i>Daftar Jadwal &amp; Risalah RTM</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">RTM</th>
                            <th class="py-3">Siklus</th>
                            <th class="py-3 text-center">Jadwal</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center">Peserta</th>
                            <th class="py-3 text-center">Instruksi</th>
                            <th class="py-3 pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $meeting)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $meeting->title }}</div>
                                <div class="text-muted small">{{ $meeting->location ?? 'Lokasi belum diatur' }}</div>
                            </td>
                            <td class="small">{{ $meeting->cycle?->name }}</td>
                            <td class="small text-center">{{ $meeting->meeting_date?->format('d M Y') }}<br>{{ $meeting->meeting_time?->format('H:i') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $meeting->status_color }} bg-opacity-10 text-{{ $meeting->status_color }} border border-{{ $meeting->status_color }} border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                    {{ $meeting->status_label }}
                                </span>
                            </td>
                            <td class="text-center">{{ $meeting->participants->count() }}</td>
                            <td class="text-center">{{ $meeting->instructions->count() }}</td>
                            <td class="py-3 pe-4 text-end">
                                <a href="{{ route('admin.rtm.show', $meeting->id) }}" class="btn btn-sm btn-primary rounded-pill shadow-sm me-1">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Kelola
                                </a>
                                <a href="{{ route('admin.rtm.pdf', $meeting->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill me-1">
                                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                </a>
                                @if($meeting->status !== 'disahkan')
                                    <a href="{{ route('admin.rtm.edit', $meeting->id) }}" class="btn btn-sm btn-outline-warning rounded-pill me-1">
                                        <i class="fa-solid fa-pencil me-1"></i>
                                    </a>
                                    <form action="{{ route('admin.rtm.destroy', $meeting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal RTM ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-landmark fs-1 mb-3 opacity-25 d-block"></i>
                            Belum ada jadwal RTM. <a href="{{ route('admin.rtm.create') }}">Buat jadwal pertama</a>.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@if($isPimpinan)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($summary['by_program']->map(fn($f) => $f->pid ? 'Prodi#' . $f->pid : ($f->unit_name ?? 'Unit#' . $f->uid))->values());
        const values = @json($summary['by_program']->pluck('total')->values());

        if (document.getElementById('chartTemuan')) {
            new Chart(document.getElementById('chartTemuan'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Temuan',
                        data: values,
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    });
</script>
@endpush
@endif