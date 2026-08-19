@extends('layouts.public')

@section('title', 'Sambutan Kepala SPMI - ' . config('app.name'))

@section('content')
<div class="py-5 text-white mb-5" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="container py-4 text-center">
        <h1 class="fw-bold display-6">Sambutan Kepala SPMI</h1>
        <p class="text-white-50 mt-2"><i class="fa-solid fa-quote-left me-1"></i> STMIK Mardira Indonesia <i class="fa-solid fa-quote-right ms-1"></i></p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            @php
                $sambutanPage = App\Models\Page::where('slug','sambutan-kepala')->where('is_published',true)->first();
            @endphp
            @if($sambutanPage)
            <div class="card card-custom shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-4 flex-shrink-0" style="width: 70px; height: 70px;">
                            <i class="fa-solid fa-user-tie fs-2 text-primary"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1 text-dark" style="font-size: 1.4rem;">{{ $sambutanPage->title }}</h2>
                            @if($sambutanPage->author)
                            <p class="text-muted mb-0 small"><i class="fa-solid fa-pen-to-square me-1"></i> Oleh: {{ $sambutanPage->author->name }} &bull; Diperbarui: {{ $sambutanPage->updated_at->format('d F Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="page-content" style="line-height: 1.9; font-size: 1.05rem; color: #475569;">
                        {!! $sambutanPage->content !!}
                    </div>
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fa-solid fa-file-circle-question text-muted fs-1 opacity-25 mb-3"></i>
                <h5 class="text-muted">Konten belum tersedia.</h5>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .page-content p { margin-bottom: 1.5rem; text-align: justify; }
    .page-content h2, .page-content h3 { color: #0f172a; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
    .page-content ul { margin-bottom: 1.5rem; padding-left: 1.5rem; }
    .page-content li { margin-bottom: 0.5rem; }
    .page-content hr { margin: 2rem 0; border-color: #e2e8f0; border-width: 2px; }
    .page-content a { color: var(--secondary-color); text-decoration: none; font-weight: 600; }
    .page-content a:hover { text-decoration: underline; }
</style>
@endpush
