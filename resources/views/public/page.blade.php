@extends('layouts.public')

@section('title', $page->title . ' - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #1e293b);">
    <div class="container py-4 text-center">
        <h1 class="fw-bold display-5">{{ $page->title }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mt-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">{{ $page->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card card-custom shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    
                    @if($page->author)
                    <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                        <div class="bg-light rounded-circle p-3 text-secondary me-3">
                            <i class="fa-solid fa-user-tie fs-4"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Penulis / Oleh</p>
                            <h6 class="mb-0 fw-bold">{{ $page->author->name }}</h6>
                        </div>
                        <div class="ms-auto text-end">
                            <p class="mb-0 text-muted small">Update Terakhir</p>
                            <h6 class="mb-0 text-dark">{{ $page->updated_at->format('d M Y') }}</h6>
                        </div>
                    </div>
                    @endif

                    <div class="page-content" style="line-height: 1.8; font-size: 1.05rem; color: #475569;">
                        {!! $page->content !!}
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
    .page-content h2, .page-content h3 { color: var(--primary-color); font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
    .page-content ul { margin-bottom: 1.5rem; padding-left: 1.5rem; }
    .page-content li { margin-bottom: 0.5rem; }
</style>
@endpush
