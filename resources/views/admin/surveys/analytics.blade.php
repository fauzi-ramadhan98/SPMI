@extends('layouts.admin')

@section('title', 'Analitik: ' . $survey->title)

@section('content')
<div class="row mb-4">
    <!-- Stat Cards -->
    <div class="col-md-4">
        <div class="card card-custom h-100 shadow-sm border-0 border-start border-4 border-info py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Responden Unik</div>
                        <div class="h3 mb-0 fw-bold text-dark"><i class="fa-solid fa-users me-2 opacity-50 text-info"></i> {{ $totalResponses }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-custom h-100 shadow-sm border-0 border-start border-4 border-success py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">Indeks Kepuasan Layanan (Skala 100)</div>
                        <div class="h3 mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-chart-line me-2 opacity-50 text-success"></i> 
                            {{ $ikm ? $ikm : 'N/A' }} 
                            @if($ikm) 
                                <span class="fs-6 {{ $ikm >= 80 ? 'text-success' : ($ikm >= 60 ? 'text-warning' : 'text-danger') }} ms-2">
                                    ({{ $ikm >= 80 ? 'Sangat Baik' : ($ikm >= 60 ? 'Cukup Baik' : 'Kurang') }})
                                </span> 
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 d-flex align-items-center justify-content-end">
        <div class="text-end w-100">
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-outline-secondary rounded-pill fw-semibold px-4 shadow-sm mb-2 w-100 d-md-inline-block d-block"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Survei</a>
            @if($totalResponses > 0)
            <button onclick="window.print()" class="btn btn-primary rounded-pill fw-bold shadow px-4 w-100 d-md-inline-block d-block"><i class="fa-solid fa-print me-2"></i> Cetak Laporan Real-time</button>
            @endif
        </div>
    </div>
</div>

@if($totalResponses == 0)
    <div class="text-center bg-white p-5 rounded-4 shadow-sm border border-light mt-4">
        <i class="fa-solid fa-chart-simple text-info opacity-25 mb-3 d-block mx-auto" style="font-size: 5rem;"></i>
        <h4 class="fw-bold text-dark">Belum Ada Data Masuk</h4>
        <p class="text-muted mb-0">Analitik dan grafik akan otomatis muncul di sini setelah ada responden yang mengisi survei ini.</p>
    </div>
@else
    <h5 class="fw-bold mb-4 mt-5"><i class="fa-solid fa-chart-pie text-primary me-2"></i> Analisis Jawaban Per Butir Pertanyaan</h5>
    
    <div class="row g-4">
        @foreach($survey->questions as $index => $q)
        <div class="col-md-6 mb-3">
            <div class="card card-custom shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <h6 class="fw-bold text-dark lh-base mb-0"><span class="text-primary me-1">{{ $index + 1 }}.</span> {{ $q->question_text }}</h6>
                    <div class="mt-2"><span class="badge bg-light text-muted border text-xs">{{ str_replace('_', ' ', $q->question_type) }}</span></div>
                </div>
                <div class="card-body p-4 pt-2">
                    @php $data = $analytics[$q->id] ?? null; @endphp
                    
                    @if($q->question_type == 'rating')
                        <!-- RATING CHART (Doughnut / Bar) -->
                        <div class="position-relative" style="height: 250px;">
                            <canvas id="chart_q{{ $q->id }}"></canvas>
                        </div>
                    @elseif($q->question_type == 'multiple_choice' || $q->question_type == 'checkbox')
                        <!-- PIE CHART -->
                        <div class="position-relative" style="height: 250px;">
                            <canvas id="chart_q{{ $q->id }}"></canvas>
                        </div>
                    @elseif($q->question_type == 'text')
                        <!-- TEXT LIST -->
                        <div class="bg-light p-3 rounded" style="max-height: 250px; overflow-y: auto;">
                            @if(count($data)>0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($data as $response)
                                        <li class="mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                                            <div class="d-flex w-100 align-items-center mb-1">
                                                <i class="fa-solid fa-quote-left fa-fw text-primary opacity-50 me-2" style="font-size: 0.7rem;"></i> 
                                                <small class="fw-semibold text-dark">{{ $response->respondent_name ?? 'Anonim' }} ({{ $response->respondent_type ?? '-' }})</small>
                                            </div>
                                            <p class="small text-muted mb-0 ps-4 lh-base">{{ $response->answer }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted small text-center my-4">Belum ada jawaban teks.</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Chart color palette for rich aesthetics
        const colors = [
            'rgba(59, 130, 246, 0.8)', // Blue
            'rgba(16, 185, 129, 0.8)', // Emerald
            'rgba(245, 158, 11, 0.8)', // Amber
            'rgba(2ef, 68, 68, 0.8)',  // Red
            'rgba(139, 92, 246, 0.8)', // Violet
            'rgba(236, 72, 153, 0.8)'  // Pink
        ];
        
        const borderColors = colors.map(c => c.replace('0.8', '1'));

        @if($totalResponses > 0)
            @foreach($survey->questions as $q)
                @php $data = $analytics[$q->id] ?? null; @endphp
                
                @if($q->question_type == 'rating' && $data)
                    new Chart(document.getElementById('chart_q{{ $q->id }}').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['1 (S.Kurang)', '2 (Kurang)', '3 (Baik)', '4 (S.Baik)'],
                            datasets: [{
                                label: 'Jumlah Responden',
                                data: [{{ $data[1] ?? 0 }}, {{ $data[2] ?? 0 }}, {{ $data[3] ?? 0 }}, {{ $data[4] ?? 0 }}],
                                backgroundColor: [
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(245, 158, 11, 0.8)',
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(16, 185, 129, 0.8)'
                                ],
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                @elseif(($q->question_type == 'multiple_choice' || $q->question_type == 'checkbox') && $data && count($data) > 0)
                    new Chart(document.getElementById('chart_q{{ $q->id }}').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode(array_keys($data)) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($data)) !!},
                                backgroundColor: colors,
                                borderColor: 'white',
                                borderWidth: 2,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'right', labels: { boxWidth: 12, font: {size: 11} } }
                            },
                            cutout: '60%'
                        }
                    });
                @endif
            @endforeach
        @endif
    });
</script>

<style>
    @media print {
        #sidebar-wrapper, .topbar, .btn { display: none !important; }
        #page-content-wrapper { padding-left: 0 !important; width: 100% !important; margin: 0 !important; }
        .card-custom { box-shadow: none !important; border: 1px solid #dee2e6 !important; break-inside: avoid; }
    }
</style>
@endpush
