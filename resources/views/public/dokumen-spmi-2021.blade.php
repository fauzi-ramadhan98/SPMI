@extends('layouts.public')

@section('title', 'Dokumen SPMI 2021 - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Dokumen SPMI Tahun 2021</h1>
        <p class="text-white-50">STMIK Mardira Indonesia</p>
    </div>
</div>

<div class="container my-5 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 border-top border-4 border-primary">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fa-solid fa-folder-open text-primary fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">Arsip Dokumen SPMI 2021</h4>
                            <p class="text-muted mb-0 small">Dokumen kebijakan, manual mutu, dan standar tahun 2021</p>
                        </div>
                    </div>
                    <hr>
                    <p class="text-muted" style="line-height:1.8;">
                        Dokumen SPMI tahun 2021 mencakup seluruh perangkat Sistem Penjaminan Mutu Internal yang berlaku
                        pada periode tersebut, meliputi: Kebijakan Mutu, Manual Mutu, Standar Mutu, dan Formulir
                        Penjaminan Mutu sesuai Permenristekdikti No. 62 Tahun 2016.
                    </p>
                    <div class="text-center mt-4">
                        <a href="{{ route('public.documents.index') }}?type=SPMI" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                            <i class="fa-solid fa-file-pdf me-2"></i> Lihat Dokumen SPMI
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
