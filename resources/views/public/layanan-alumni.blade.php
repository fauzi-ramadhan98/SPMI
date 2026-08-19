@extends('layouts.public')

@section('title', 'Layanan Alumni (Karir Link) - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #b45309);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Layanan Alumni & Karir Link</h1>
        <p class="text-white-50">STMIK Mardira Indonesia</p>
    </div>
</div>

<div class="container my-5 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width:60px;height:60px;">
                            <i class="fa-solid fa-graduation-cap text-warning fs-3"></i>
                        </div>
                        <h6 class="fw-bold">Tracer Study</h6>
                        <p class="text-muted small">Lacak perjalanan karir lulusan dan data keterlacakan alumni STMIK Mardira Indonesia.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width:60px;height:60px;">
                            <i class="fa-solid fa-briefcase text-primary fs-3"></i>
                        </div>
                        <h6 class="fw-bold">Lowongan Kerja</h6>
                        <p class="text-muted small">Informasi lowongan kerja dari mitra perusahaan yang bekerjasama dengan institusi.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width:60px;height:60px;">
                            <i class="fa-solid fa-network-wired text-success fs-3"></i>
                        </div>
                        <h6 class="fw-bold">Jejaring Alumni</h6>
                        <p class="text-muted small">Terhubung dengan ribuan alumni STMIK Mardira Indonesia di seluruh Indonesia.</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h4 class="fw-bold mb-3">Survei Alumni (Tracer Study)</h4>
                    <p class="text-muted" style="line-height:1.8;">
                        Sebagai bagian dari upaya peningkatan mutu berkelanjutan, STMIK Mardira Indonesia secara rutin
                        melaksanakan Tracer Study untuk mengetahui kondisi dan pengalaman para lulusan setelah memasuki
                        dunia kerja. Data ini menjadi acuan penting bagi pengembangan kurikulum dan program karir kampus.
                    </p>
                    <div class="text-center mt-4">
                        <a href="{{ route('public.surveys.index') }}" class="btn btn-warning btn-lg rounded-pill px-5 shadow fw-bold text-dark">
                            <i class="fa-solid fa-user-graduate me-2"></i> Isi Survei Alumni
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
