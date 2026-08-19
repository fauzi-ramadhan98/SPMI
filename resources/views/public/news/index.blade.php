@extends('layouts.public')

@section('title', 'Berita & Pengumuman - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #2563eb);">
    <div class="container py-3 text-center">
        <h1 class="fw-bold display-5">Berita & Pengumuman</h1>
        <p class="lead text-white-50 mt-3 max-w-2xl mx-auto">Informasi terbaru seputar kegiatan dan perkembangan Sistem Penjaminan Mutu Internal.</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row g-4 justify-content-center">
        @forelse($news as $item)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card card-custom h-100 shadow-sm border-0 d-flex flex-column transition-hover">
                <div class="card-img-top bg-light ratio ratio-16x9 border-bottom position-relative overflow-hidden" style="min-height: 200px;">
                    @if($item->thumbnail)
                        <img src="{{ Storage::url($item->thumbnail) }}" alt="{{ $item->title }}" class="img-fluid w-100 h-100 object-fit-cover position-absolute top-0 start-0">
                    @else
                        <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-secondary bg-opacity-10 text-secondary position-absolute top-0 start-0">
                            <i class="fa-solid fa-newspaper fs-1"></i>
                        </div>
                    @endif
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><i class="fa-regular fa-clock me-1"></i> {{ $item->published_at->format('d M Y') }}</span>
                    </div>
                    <h5 class="fw-bold card-title mb-3">
                        <a href="{{ route('public.news.show', $item->slug) }}" class="text-dark text-decoration-none stretched-link">{{ $item->title }}</a>
                    </h5>
                    <p class="card-text text-muted mb-4 flex-grow-1" style="font-size: 0.95rem;">
                        {{ $item->excerpt ?? Str::limit(strip_tags($item->content), 120) }}
                    </p>
                </div>
                <div class="card-footer bg-white border-top border-light p-4 pt-0 d-flex align-items-center">
                    @if($item->author)
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle text-secondary d-flex justify-content-center align-items-center me-2" style="width: 30px; height: 30px;">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                        <small class="text-muted fw-semibold">{{ $item->author->name }}</small>
                    </div>
                    @endif
                    <div class="ms-auto text-primary fw-bold text-sm">Baca <i class="fa-solid fa-angle-right ms-1"></i></div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="card card-custom border-0 shadow-sm bg-light">
                <div class="card-body py-5">
                    <i class="fa-solid fa-newspaper text-muted opacity-50 mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-dark fw-bold">Belum Ada Berita</h4>
                    <p class="text-muted mb-0">Belum ada berita atau pengumuman yang dapat ditampilkan saat ini.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    
    @if($news->hasPages())
    <div class="mt-5 d-flex justify-content-center">
        {{ $news->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<style>
    .transition-hover {
        transition: all 0.3s ease;
    }
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .card-title a {
        transition: color 0.2s ease;
    }
    .card-title a:hover {
        color: var(--secondary-color) !important;
    }
</style>
@endpush
