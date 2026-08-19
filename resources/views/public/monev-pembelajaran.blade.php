@extends('layouts.public')

@section('title', 'Monev Pembelajaran - ' . config('app.name'))

@push('scripts')
<style>
    body { background-color: #f5f5f5; }
    .page-header-custom { background-color: #fff; padding: 40px 0; border-bottom: 1px solid #eee; margin-bottom: 40px; }
    .page-title { font-weight: 700; font-size: 2.25rem; color: #333; margin: 0; }
    .content-area { background: #fff; padding: 50px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 60px; color: #444; line-height: 1.8; font-size: 1.05rem; }
    .monev-card { border: none; border-left: 4px solid #10b981; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 20px 24px; margin-bottom: 16px; background: #fff; transition: all 0.2s ease; }
    .monev-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); transform: translateX(4px); }
    .monev-card h5 { color: #0f172a; font-weight: 700; margin-bottom: 4px; }
    .monev-card p { color: #666; font-size: 0.9rem; margin: 0; }
    .monev-badge { background: #10b981; color: white; border-radius: 20px; padding: 2px 12px; font-size: 0.8rem; font-weight: 600; }
    @media (max-width: 768px) { .content-area { padding: 30px 20px; } }
</style>
@endpush

@section('content')
<div class="page-header-custom text-center">
    <div class="container">
        <h1 class="page-title">Monitoring & Evaluasi Pembelajaran</h1>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="content-area">

                <p>Monitoring dan Evaluasi (Monev) Pembelajaran merupakan kegiatan pemantauan proses perkuliahan yang dilakukan secara rutin setiap semester oleh LPM untuk memastikan pelaksanaan pembelajaran berjalan sesuai standar mutu yang ditetapkan.</p>

                <hr class="my-4 opacity-25">

                <h5 class="fw-bold mb-3">Tahun Akademik 2024/2025</h5>

                <a href="#" class="text-decoration-none">
                    <div class="monev-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Monev Pembelajaran 2024/2025 — Semester Genap</h5>
                                <p>Laporan Monitoring & Evaluasi Semester Genap TA 2024/2025</p>
                            </div>
                            <span class="monev-badge">Terbaru</span>
                        </div>
                    </div>
                </a>

                <a href="#" class="text-decoration-none">
                    <div class="monev-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Monev Pembelajaran 2024/2025 — Semester Ganjil</h5>
                                <p>Laporan Monitoring & Evaluasi Semester Ganjil TA 2024/2025</p>
                            </div>
                        </div>
                    </div>
                </a>

                <h5 class="fw-bold mt-4 mb-3">Tahun Akademik 2023/2024</h5>

                <a href="#" class="text-decoration-none">
                    <div class="monev-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Monev Pembelajaran 2023/2024 — Semester Genap</h5>
                                <p>Laporan Monitoring & Evaluasi Semester Genap TA 2023/2024</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="#" class="text-decoration-none">
                    <div class="monev-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Monev Pembelajaran 2023/2024 — Semester Gasal</h5>
                                <p>Laporan Monitoring & Evaluasi Semester Gasal TA 2023/2024</p>
                            </div>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</div>
@endsection
