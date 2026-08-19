@extends('layouts.public')

@section('title', 'Struktur Organisasi - ' . config('app.name'))

@push('scripts')
<style>
    body { background-color: #f5f5f5; }
    .page-header-custom {
        background-color: #ffffff;
        padding: 40px 0;
        border-bottom: 1px solid #eeeeee;
        margin-bottom: 40px;
    }
    .page-title {
        font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #333333;
        font-weight: 700;
        font-size: 2.25rem;
        margin: 0;
    }
    .content-area {
        background: #ffffff;
        padding: 50px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 60px;
        color: #444444;
        line-height: 1.8;
        font-size: 1.05rem;
    }
    .org-chart-img {
        width: 100%;
        max-width: 900px;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fafafa;
        margin: 0 auto;
        display: block;
    }
    @media (max-width: 768px) {
        .content-area { padding: 30px 20px; }
    }
</style>
@endpush

@section('content')
<div class="page-header-custom text-center">
    <div class="container">
        <h1 class="page-title">Struktur Organisasi</h1>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="content-area text-center">
                
                <h4 class="mb-4 fw-bold text-dark">Struktur Pemangku Jabatan Lembaga Penjaminan Mutu (LPM)</h4>
                <p>Berikut ini adalah susunan struktur organisasi dari Unit Lembaga Penjaminan Mutu (LPM) Universitas Nahdlatul Ulama Al Ghazali Cilacap.</p>
                
                <hr class="my-5 opacity-25">
                
                <!-- Placeholder untuk Gambar Struktur Organisasi -->
                <div class="bg-light p-5 rounded border mb-4 d-flex align-items-center justify-content-center" style="min-height: 400px; border-style: dashed !important; border-width: 2px !important;">
                    <div class="text-muted">
                        <i class="fa-solid fa-sitemap fs-1 mb-3"></i>
                        <h5>[Tempat Gambar Bagan Struktur Organisasi]</h5>
                        <p class="small">Silakan ganti bagian ini dengan file gambar (img src) bagan Anda.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
