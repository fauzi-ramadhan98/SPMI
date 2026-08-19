@extends('layouts.admin')

@section('title', 'Executive Dashboard')

@section('content')
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Siklus Aktif</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $cycles->count() }}</div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-danger border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-danger text-uppercase mb-1">Total Temuan</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $findingsCount }}</div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Temuan Terbuka</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $openFindings }}</div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-success text-uppercase mb-1">ED Disubmit</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $evaluationsSubmitted }}</div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-landmark me-2"></i>Siklus AMI Aktif</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted text-uppercase">
                            <tr><th class="ps-4">Nama Siklus</th><th>Tahun</th><th>Semester</th><th>Periode</th></tr>
                        </thead>
                        <tbody>
                        @forelse($cycles as $cycle)
                            <tr>
                                <td class="ps-4 py-3 fw-bold">{{ $cycle->name }}</td>
                                <td>{{ $cycle->academic_year }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $cycle->semester }}</span></td>
                                <td class="text-muted">{{ $cycle->start_date?->format('d M Y') }} s.d. {{ $cycle->end_date?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada siklus aktif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-custom shadow-sm mt-4">
            <div class="card-header-custom">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-chart-column me-2"></i>Jumlah Temuan per Unit Kerja / Program Studi</h6>
            </div>
            <div class="card-body">
                <canvas id="chartTemuan" height="140"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h6></div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.rtm.index') }}" class="btn btn-outline-primary text-start border-2">
                        <i class="fa-solid fa-landmark me-2 fa-fw"></i> Rapat Tinjauan Manajemen (RTM)
                    </a>
                    <a href="{{ route('admin.pimpinan.rtl') }}" class="btn btn-outline-danger text-start border-2">
                        <i class="fa-solid fa-clipboard-check me-2 fa-fw"></i> Pemantauan RTL
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-success text-start border-2">
                        <i class="fa-solid fa-file-lines me-2 fa-fw"></i> Laporan LHA / AMI
                    </a>
                </div>
            </div>
        </div>

        <div class="card card-custom shadow-sm mt-4">
            <div class="card-header-custom">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-fire me-2"></i>Peta Risiko (Risk Heatmap)</h6>
            </div>
            <div class="card-body">
                @php
                    $scoreColor = function ($p, $i) {
                        $score = $p * $i;
                        if ($score >= 15) return '#dc3545';
                        if ($score >= 8) return '#ffc107';
                        return '#20c997';
                    };
                @endphp
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center align-middle mb-2">
                        <thead>
                            <tr>
                                <th class="bg-light">P\D</th>
                                @for($i = 1; $i <= 5; $i++)<th class="bg-light small">D{{ $i }}</th>@endfor
                            </tr>
                        </thead>
                        <tbody>
                            @for($p = 5; $p >= 1; $p--)
                            <tr>
                                <th class="bg-light small">P{{ $p }}</th>
                                @for($i = 1; $i <= 5; $i++)
                                <td style="background-color: {{ $scoreColor($p, $i) }}; color: {{ ($p * $i) >= 8 ? '#fff' : '#000' }};">
                                    {{ $riskMatrix[$p][$i] ?? 0 }}
                                </td>
                                @endfor
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                <div class="small text-muted">
                    <span class="badge" style="background:#dc3545;">Merah</span> Risiko Tinggi (≥15) ·
                    <span class="badge" style="background:#ffc107;">Kuning</span> Sedang (8–14) ·
                    <span class="badge" style="background:#20c997;">Hijau</span> Rendah (&lt;8)
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($findingsByProgram->map(fn($f) => $f->pid ? 'Prodi#' . $f->pid : ($f->unit_name ?? 'Unit#' . $f->uid))->values());
        const values = @json($findingsByProgram->pluck('total')->values());

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