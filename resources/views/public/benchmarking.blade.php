@extends('layouts.public')

@section('title', 'Benchmarking - ' . config('app.name'))

@push('scripts')
    <style>
        body {
            background-color: #f5f5f5;
        }

        .page-header-custom {
            background-color: #fff;
            padding: 40px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 40px;
        }

        .page-title {
            font-weight: 700;
            font-size: 2.25rem;
            color: #333;
            margin: 0;
        }

        .content-area {
            background: #fff;
            padding: 50px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 60px;
            color: #444;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .bench-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 28px;
            margin-bottom: 24px;
            background: #fff;
            transition: all 0.3s ease;
            border-top: 4px solid #8b5cf6;
        }

        .bench-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .bench-card h5 {
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .bench-card p {
            color: #666;
            font-size: 0.95rem;
        }

        .bench-badge {
            background: #8b5cf6;
            color: white;
            border-radius: 20px;
            padding: 3px 14px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 30px 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-header-custom text-center">
        <div class="container">
            <h1 class="page-title">Benchmarking</h1>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="content-area">

                    <p>Kegiatan Benchmarking dilakukan oleh SPMI STMIK Mardira Indonesia Bandung sebagai salah satu upaya
                        peningkatan mutu berkelanjutan dengan cara mempelajari, membandingkan, dan mengadaptasi praktik
                        terbaik dari lembaga pendidikan lain yang telah terakreditasi unggul.</p>

                    <hr class="my-4 opacity-25">

                    <div class="bench-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5>Benchmarking ke STMIK Mardira Indonesia (STMIK MI) 2025</h5>
                                <p>Kunjungan benchmarking ke STMIK Mardira Indonesia (STMIK MI) dilaksanakan untuk
                                    mempelajari sistem penjaminan mutu internal dan praktik terbaik pengelolaan perguruan
                                    tinggi yang telah mendapatkan Akreditasi Unggul.</p>
                                <div class="d-flex align-items-center gap-3 mt-3">
                                    <span class="bench-badge">2025</span>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i>Bandung, Jawa
                                        Barat</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bench-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5>Benchmarking (Kegiatan Mendatang)</h5>
                                <p>Informasi kegiatan benchmarking selanjutnya akan dipublikasikan di halaman ini. Silakan
                                    kunjungi kembali secara berkala untuk mendapatkan informasi terbaru.</p>
                                <div class="d-flex align-items-center gap-3 mt-3">
                                    <span class="bench-badge" style="background:#aaa;">Segera</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection