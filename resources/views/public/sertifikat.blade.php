@extends('layouts.public')

@section('title', 'Sertifikat - ' . config('app.name'))

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

        .sertif-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 20px;
            background: #fff;
            transition: all 0.3s ease;
            text-align: center;
            border-top: 4px solid #f59e0b;
        }

        .sertif-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .sertif-card .icon-wrap {
            width: 64px;
            height: 64px;
            background: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            font-size: 1.75rem;
            color: #f59e0b;
        }

        .sertif-card h5 {
            color: #0f172a;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .sertif-card p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
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
            <h1 class="page-title">Daftar Sertifikat PT dan Prodi</h1>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="content-area">

                    <p>Berikut ini adalah daftar sertifikat Institusi (Perguruan Tinggi) dan Program Studi yang ada di
                        STMIK Mardira Indonesia Bandung.</p>

                    <hr class="my-4 opacity-25">

                    <div class="row gy-4">
                        <div class="col-md-4">
                            <div class="sertif-card">
                                <div class="icon-wrap"><i class="fa-solid fa-award"></i></div>
                                <h5>Sertifikat Akreditasi PT</h5>
                                <p>Sertifikat Akreditasi STMIK Mardira Indonesia dari BAN-PT dan LAM INfokom</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sertif-card">
                                <div class="icon-wrap"><i class="fa-solid fa-certificate"></i></div>
                                <h5>Sertifikat Prodi Teknik Informatika S1</h5>
                                <p>Sertifikat Akreditasi Program Studi Teknik Informatika dari LAM-INfokom</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sertif-card">
                                <div class="icon-wrap"><i class="fa-solid fa-certificate"></i></div>
                                <h5>Sertifikat Prodi Teknik Informatika D3</h5>
                                <p>Sertifikat Akreditasi Program Studi Teknik Informatika dari BAN-PT</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sertif-card">
                                <div class="icon-wrap"><i class="fa-solid fa-certificate"></i></div>
                                <h5>Sertifikat Prodi Manajemen Informatika D3</h5>
                                <p>Sertifikat Akreditasi Program Studi Manajemen Informatika dari LAM Infokom</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sertif-card">
                                <div class="icon-wrap"><i class="fa-solid fa-certificate"></i></div>
                                <h5>Sertifikat Prodi Komputerisasi Akuntansi D3</h5>
                                <p>Sertifikat Akreditasi Program Studi Komputerisasi Akuntansi dari LAM Infokom</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sertif-card">
                                <div class="icon-wrap"><i class="fa-solid fa-plus"></i></div>
                                <h5>Lihat Semua Sertifikat</h5>
                                <p>Klik untuk melihat daftar lengkap seluruh sertifikat akreditasi</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection