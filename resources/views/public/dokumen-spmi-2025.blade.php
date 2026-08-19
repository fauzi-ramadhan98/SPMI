@extends('layouts.public')

@section('title', 'Dokumen SPMI 2025 - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #0369a1);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Dokumen SPMI Tahun 2025</h1>
        <p class="text-white-50">STMIK Mardira Indonesia</p>
    </div>
</div>

<div class="container my-5 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 border-top border-4 border-info">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fa-solid fa-folder-open text-info fs-2"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">Dokumen SPMI Terbaru 2025</h4>
                            <p class="text-muted mb-0 small">Dokumen mutu yang berlaku pada periode 2025</p>
                        </div>
                    </div>
                    <hr>
                    <p class="text-muted" style="line-height:1.8;">
                        Dokumen SPMI tahun 2025 merupakan pembaruan dan penyesuaian dokumen mutu sesuai dengan
                        perkembangan regulasi pendidikan tinggi terbaru, termasuk penyesuaian terhadap Permendikbudristek
                        dan kebijakan akreditasi terbaru dari LAM Infokom dan BAN-PT.
                    </p>
                    <div class="alert alert-info border-0 rounded-3 shadow-sm d-flex align-items-center mt-3">
                        <i class="fa-solid fa-circle-info text-info fs-4 me-3 flex-shrink-0"></i>
                        <div class="small text-muted">
                            Dokumen ini merupakan versi terkini yang digunakan sebagai acuan dalam seluruh kegiatan
                            penjaminan mutu internal di lingkungan STMIK Mardira Indonesia.
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('public.documents.index') }}?type=SPMI" class="btn btn-info btn-lg rounded-pill px-5 fw-bold shadow text-white">
                            <i class="fa-solid fa-file-pdf me-2"></i> Unduh Dokumen SPMI 2025
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
