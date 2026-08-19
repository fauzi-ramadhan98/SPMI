@extends('layouts.public')

@section('content')
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Abstract pattern background -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="opacity: 0.05; background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
    
    <div class="container position-relative z-index-1">
        <div class="row align-items-center justify-content-center py-4 py-lg-5">
            <div class="col-lg-8 mx-auto text-center">
                <div class="badge bg-white text-primary px-3 py-2 rounded-pill mb-4 shadow-sm" style="font-weight: 600; letter-spacing: 1px;">SISTEM PENJAMINAN MUTU INTERNAL</div>
                <h1 class="hero-title display-4">Mengawal Kualitas, <br><span class="text-info">Membangun Keunggulan</span></h1>
                <p class="lead text-white-50 mb-5 px-md-5">Lembaga Penjaminan Mutu berdedikasi untuk terus memastikan standar penyelenggaraan pendidikan yang unggul dan berkelanjutan.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="{{ route('public.surveys.index') }}" class="btn btn-custom btn-lg shadow">Isi Survei Layanan</a>
                    <a href="{{ route('public.documents.index') }}" class="btn btn-outline-light btn-lg rounded-pill px-4" style="border-width: 2px;">Jelajahi Dokumen Mutu</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats/Features Section -->
<section class="py-5" style="margin-top: -60px; position:relative; z-index: 10;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-custom h-100 text-center p-4">
                    <div class="card-body">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-file-shield fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Dokumen Standar</h5>
                        <p class="text-muted small mb-0">Akses seluruh dokumen kebijakan, manual mutu, dan SOP standar tridharma perguruan tinggi.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom h-100 text-center p-4">
                    <div class="card-body">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-list-check fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Audit Mutu Internal</h5>
                        <p class="text-muted small mb-0">Sistem terintegrasi untuk pendaftaran, pelaporan, dan evaluasi hasil audit mutu prodi.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom h-100 text-center p-4">
                    <div class="card-body">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-face-smile fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Survei Kepuasan</h5>
                        <p class="text-muted small mb-0">Partisipasi aktif sivitas akademika dalam memberikan feedback untuk peningkatan mutu berkelanjutan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sambutan Section -->
@if($sambutan)
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="position-relative">
                    <div class="bg-primary rounded-4" style="height: 400px; width: 100%; opacity: 0.1;"></div>
                    <img src="https://ui-avatars.com/api/?name=Kepala+LPM&size=400&background=random" alt="Kepala LPM" class="img-fluid rounded-4 position-absolute top-50 start-50 translate-middle shadow-lg" style="width: 90%; height: 90%; object-fit: cover;">
                </div>
            </div>
            <div class="col-lg-7 px-lg-5">
                <h6 class="text-primary fw-bold text-uppercase tracking-wider mb-2">Sambutan</h6>
                <h2 class="fw-bold mb-4">{{ $sambutan->title }}</h2>
                <div class="text-muted" style="line-height: 1.8;">
                    {!! Str::limit(strip_tags($sambutan->content), 300) !!}
                </div>
                <a href="{{ route('sambutan') }}" class="btn btn-outline-primary mt-4 rounded-pill px-4">Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Berita Section -->
@if($latestNews && $latestNews->count() > 0)
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase tracking-wider mb-2">Informasi Terkini</h6>
            <h2 class="fw-bold">Berita & Pengumuman</h2>
        </div>
        <div class="row g-4">
            @foreach($latestNews as $news)
            <div class="col-md-4">
                <div class="card card-custom h-100">
                    <div class="card-body p-4">
                        <span class="badge bg-light text-secondary mb-3"><i class="fa-regular fa-calendar me-1"></i> {{ $news->published_at->format('d M Y') }}</span>
                        <h5 class="fw-bold mb-3"><a href="{{ route('public.news.show', $news->slug) }}" class="text-dark text-decoration-none">{{ $news->title }}</a></h5>
                        <p class="text-muted small">{{ $news->excerpt ?? Str::limit(strip_tags($news->content), 100) }}</p>
                    </div>
                    <div class="card-footer bg-white border-0 p-4 pt-0">
                        <a href="{{ route('public.news.show', $news->slug) }}" class="text-primary text-decoration-none fw-semibold">Baca lebih lanjut &rarr;</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('public.news.index') }}" class="btn btn-primary btn-custom shadow">Lihat Semua Berita</a>
        </div>
    </div>
</section>
@endif
@endsection
