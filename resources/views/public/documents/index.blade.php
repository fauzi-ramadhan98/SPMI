@extends('layouts.public')

@section('title', 'Dokumen Mutu - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #2563eb);">
    <div class="container py-3 text-center">
        <h1 class="fw-bold display-5">Direktori Dokumen Mutu</h1>
        <p class="lead text-white-50 mt-3 max-w-2xl mx-auto">Akses dokumen kebijakan, manual mutu, standar, dan formulir publik SPMI.</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    
    <!-- Filter Bar -->
    <div class="card card-custom mb-5 shadow-sm border-0 p-2 p-md-4">
        <form action="{{ route('public.documents.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold">Pencarian</label>
                <div class="input-group text-bg-light rounded">
                    <span class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-transparent py-2" placeholder="Cari judul dokumen..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold">Kategori Dokumen</label>
                <select name="type" class="form-select border-0 bg-light py-2">
                    <option value="">Semua Kategori</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Document List -->
    <div class="card card-custom shadow-sm border-0 z-index-1">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-muted small text-uppercase">Informasi Dokumen</th>
                            <th class="py-3 border-0 text-muted small text-uppercase w-25">Kategori</th>
                            <th class="py-3 border-0 text-muted small text-uppercase text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td class="ps-4 py-4 border-bottom border-light">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3 text-center d-none d-sm-block">
                                        <i class="fa-solid fa-file-pdf fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark">{{ $doc->title }}</h6>
                                        <div class="text-muted small">
                                            <span class="me-3"><i class="fa-regular fa-clock me-1"></i> {{ $doc->created_at->format('d M Y') }}</span>
                                            @if($doc->academic_year)
                                                <span><i class="fa-solid fa-calendar-day me-1"></i> T.A {{ $doc->academic_year }} - {{ $doc->semester }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 border-bottom border-light">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">{{ $doc->document_type }}</span>
                            </td>
                            <td class="py-4 border-bottom border-light text-center">
                                <a href="{{ route('public.documents.download', $doc->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-bold">
                                    <i class="fa-solid fa-cloud-arrow-down me-1"></i> Unduh
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 mb-3 text-light"></i>
                                    <h5>Belum Ada Dokumen</h5>
                                    <p>Tidak ada dokumen publik yang ditemukan dengan filter tersebut.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($documents->hasPages())
        <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
            {{ $documents->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
