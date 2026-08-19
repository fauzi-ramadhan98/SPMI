@extends('layouts.public')

@section('title', 'Survei Pemahaman Visi Misi - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Survei Pemahaman Visi Misi</h1>
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
                        Survei Pemahaman Visi dan Misi STMIK Mardira Indonesia dilaksanakan secara berkala untuk mengukur
                        sejauh mana sivitas akademika — mulai dari mahasiswa, dosen, tenaga kependidikan, hingga alumni —
                        memahami dan menginternalisasi Visi, Misi, Tujuan, dan Sasaran (VMTS) institusi.
                    </p>
                    <p class="text-muted" style="line-height:1.8;">
                        Hasil survei ini menjadi bahan evaluasi dan dasar pengambilan kebijakan dalam rangka penguatan
                        budaya mutu dan penyebarluasan informasi VMTS secara lebih efektif di lingkungan kampus.
                    </p>
                    <hr class="my-4">
                    <div class="row g-4 text-center mt-2">
                        <div class="col-md-4">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-4">
                                <i class="fa-solid fa-users-viewfinder text-primary fs-2 mb-2"></i>
                                <h6 class="fw-bold">Sasaran Responden</h6>
                                <p class="text-muted small mb-0">Mahasiswa, Dosen, Tendik, dan Alumni</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-success bg-opacity-10 rounded-3 p-4">
                                <i class="fa-solid fa-calendar-check text-success fs-2 mb-2"></i>
                                <h6 class="fw-bold">Frekuensi</h6>
                                <p class="text-muted small mb-0">Dilaksanakan setiap semester</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-4">
                                <i class="fa-solid fa-chart-bar text-warning fs-2 mb-2"></i>
                                <h6 class="fw-bold">Output</h6>
                                <p class="text-muted small mb-0">Laporan dan tindak lanjut SPMI</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-5">
                        <a href="{{ route('public.surveys.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow fw-bold">
                            <i class="fa-solid fa-arrow-right me-2"></i> Isi Survei Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
