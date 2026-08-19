@extends('layouts.public')

@section('title', $title . ' - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #2563eb);">
    <div class="container py-4 text-center">
        <h1 class="fw-bold display-5">{{ $title }}</h1>
    </div>
</div>
<div class="container mb-5 pb-5">
    <div class="text-center text-muted py-5">
        <i class="fa-solid fa-file-circle-question fs-1 opacity-25 mb-3"></i>
        <h5>Halaman Tidak Ditemukan</h5>
        <p>Konten untuk halaman ini belum tersedia atau sedang dalam penyusunan.</p>
        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 mt-2"><i class="fa-solid fa-house me-2"></i> Kembali ke Beranda</a>
    </div>
</div>
@endsection
