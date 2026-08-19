@extends('layouts.public')

@section('title', $news->title . ' - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #2563eb);">
    <div class="container py-4 text-center">
        <div class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 shadow-sm border" style="font-weight: 600; font-size: 0.85rem;"><i class="fa-solid fa-newspaper me-1"></i> Berita Terbaru</div>
        <h1 class="fw-bold display-5 mb-4">{{ $news->title }}</h1>
        
        <ul class="list-inline text-white-50 small mb-0 d-flex justify-content-center flex-wrap gap-4">
            <li class="list-inline-item d-flex align-items-center">
                <i class="fa-regular fa-clock me-2"></i> {{ $news->published_at->format('d F Y') }}
            </li>
            @if($news->author)
            <li class="list-inline-item d-flex align-items-center">
                <i class="fa-regular fa-user me-2"></i> {{ $news->author->name }}
            </li>
            @endif
        </ul>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card card-custom shadow-sm border-0 overflow-hidden">
                @if($news->thumbnail)
                <img src="{{ Storage::url($news->thumbnail) }}" class="img-fluid w-100" style="max-height: 500px; object-fit: cover;" alt="{{ $news->title }}">
                @endif
                
                <div class="card-body p-4 p-lg-5">
                    <div class="page-content" style="line-height: 1.8; font-size: 1.05rem; color: #475569;">
                        {!! $news->content !!}
                    </div>
                </div>
                
                <div class="card-footer bg-light border-0 p-4 d-flex justify-content-between align-items-center">
                    <a href="{{ route('public.news.index') }}" class="btn btn-outline-secondary rounded-pill fw-semibold"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Berita</a>
                    
                    <div class="d-flex text-muted align-items-center gap-3">
                        <span class="small fw-semibold">Bagikan:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="text-secondary"><i class="fa-brands fa-facebook fs-5"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($news->title) }}" target="_blank" class="text-secondary"><i class="fa-brands fa-twitter fs-5"></i></a>
                        <a href="https://wa.me/?text={{ urlencode($news->title . ' ' . request()->url()) }}" target="_blank" class="text-secondary"><i class="fa-brands fa-whatsapp fs-5"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .page-content p { margin-bottom: 1.5rem; }
    .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 2rem 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .page-content h2, .page-content h3, .page-content h4 { color: var(--primary-color); font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
    .page-content ul, .page-content ol { margin-bottom: 1.5rem; padding-left: 2rem; }
    .page-content li { margin-bottom: 0.5rem; }
    .page-content blockquote { padding: 1.5rem; margin: 2rem 0; border-left: 5px solid var(--secondary-color); background-color: #f8fafc; font-style: italic; border-radius: 0 8px 8px 0; }
</style>
@endpush
