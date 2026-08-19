@extends('layouts.public')

@section('title', 'Survei Layanan - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #059669);">
    <div class="container py-3 text-center">
        <h1 class="fw-bold display-5">Survei Kepuasan Layanan</h1>
        <p class="lead text-white-50 mt-3 max-w-2xl mx-auto">Partisipasi Anda sangat berarti bagi peningkatan kualitas layanan akademik dan non-akademik institut kami.</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-5 p-4 d-flex align-items-center" role="alert">
            <div class="bg-success text-white rounded-circle p-2 me-3 fs-4 text-center" style="width: 48px; height: 48px;"><i class="fa-solid fa-check"></i></div>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Berhasil!</h5>
                <p class="mb-0">{{ session('success') }}</p>
            </div>
            <button type="button" class="btn-close ms-auto me-2 mt-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 justify-content-center">
        @forelse($surveys as $survey)
        <div class="col-lg-4 col-md-6">
            <div class="card card-custom h-100 border-0 shadow-sm position-relative">
                @if($survey->is_anonymous)
                    <div class="position-absolute top-0 end-0 mt-3 me-3">
                        <span class="badge bg-light text-dark shadow-sm border text-xs" title="Survei ini bersifat anonim"><i class="fa-solid fa-user-secret me-1"></i> Anonim</span>
                    </div>
                @endif
                <div class="card-body p-4 p-xl-5 d-flex flex-column">
                    <div class="mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-clipboard-question fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $survey->title }}</h5>
                        <p class="text-muted small mb-0">{{ Str::limit($survey->description, 100) }}</p>
                    </div>
                    
                    <div class="mt-auto pt-3 border-top border-light">
                        <ul class="list-unstyled small text-muted mb-4">
                            <li class="mb-2"><i class="fa-solid fa-tag fa-fw me-2"></i> Kategori: <span class="text-capitalize">{{ str_replace('_', ' ', $survey->type) }}</span></li>
                            @if($survey->end_date)
                                <li><i class="fa-regular fa-calendar-xmark fa-fw me-2 text-danger"></i> Berakhir: {{ \Carbon\Carbon::parse($survey->end_date)->format('d M Y') }}</li>
                            @endif
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="{{ route('public.surveys.fill', $survey->id) }}" class="btn btn-primary fw-semibold rounded-pill py-2 shadow-sm">Ikuti Survei <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="card card-custom border-0 shadow-sm bg-light">
                <div class="card-body py-5">
                    <i class="fa-solid fa-box-open text-muted opacity-50 mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-dark fw-bold">Belum Ada Survei Aktif</h4>
                    <p class="text-muted">Saat ini tidak ada survei yang sedang berjalan. Terima kasih atas antusiasme Anda!</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
