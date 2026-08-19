@extends('layouts.public')

@section('title', 'Survei Kepuasan Pengguna Lulusan - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #065f46);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Survei Kepuasan Pengguna Lulusan</h1>
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
                        Survei Kepuasan Pengguna Lulusan (Tracer Employer) dilaksanakan untuk mengetahui tingkat kepuasan
                        institusi/perusahaan/mitra kerja terhadap kompetensi dan kinerja lulusan STMIK Mardira Indonesia
                        yang bekerja di instansi mereka.
                    </p>
                    <p class="text-muted" style="line-height:1.8;">
                        Data dari survei ini digunakan sebagai bahan evaluasi kurikulum, peningkatan mutu pembelajaran,
                        serta penguatan relevansi program studi terhadap kebutuhan dunia kerja dan industri.
                    </p>
                    <hr class="my-4">
                    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center">
                        <i class="fa-solid fa-circle-check text-success fs-3 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Survei Tersedia!</h6>
                            <p class="mb-0 small text-muted">Klik tombol di bawah untuk mengisi formulir survei kepuasan pengguna lulusan yang tersedia.</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('public.surveys.index') }}" class="btn btn-success btn-lg rounded-pill px-5 shadow fw-bold">
                            <i class="fa-solid fa-clipboard-check me-2"></i> Isi Survei Pengguna Lulusan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
