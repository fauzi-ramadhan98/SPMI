@extends('layouts.public')

@section('title', 'Survei Kepuasan Mitra Kerjasama - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #7c3aed);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Survei Kepuasan Mitra Kerjasama</h1>
        <p class="text-white-50">STMIK Mardira Indonesia</p>
    </div>
</div>

<div class="container my-5 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h4 class="fw-bold mb-3">Tentang Survei Ini</h4>
                    <p class="text-muted" style="line-height:1.8;">
                        Survei Kepuasan Mitra Kerjasama ini ditujukan kepada seluruh institusi, perusahaan, lembaga
                        pemerintah, dan organisasi yang telah menjalin hubungan kerjasama (MoU/MoA) dengan STMIK
                        Mardira Indonesia.
                    </p>
                    <p class="text-muted" style="line-height:1.8;">
                        Penilaian dari mitra sangat penting bagi SPMI untuk mengukur efektivitas kerjasama, kualitas
                        implementasi kegiatan bersama, serta manfaat yang diperoleh kedua belah pihak.
                    </p>
                    <hr class="my-4">
                    <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center">
                        <i class="fa-solid fa-handshake text-info fs-3 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Untuk Mitra Kerjasama</h6>
                            <p class="mb-0 small text-muted">Isi survei berikut sebagai bentuk kontribusi Anda dalam peningkatan mutu kerjasama institusi.</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('public.surveys.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow fw-bold">
                            <i class="fa-solid fa-handshake me-2"></i> Isi Survei Mitra
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
