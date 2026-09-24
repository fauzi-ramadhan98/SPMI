@extends('layouts.admin')

@section('title', 'Ajukan SOP Baru')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-file-contract me-2"></i>Formulir Pengajuan SOP Baru</h6>
    </div>

    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card-body">
        <form method="POST" action="{{ route('admin.sops.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Judul SOP <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Contoh: SOP Peminjaman Buku Perpustakaan" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi / Tujuan SOP</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan tujuan, cakupan, dan dasar penyusunan SOP ini...">{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">File Draf SOP <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
                    <div class="form-text">Format: PDF, DOC, DOCX. Maksimal 10 MB.</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary btn-custom"><i class="fa-solid fa-paper-plane me-1"></i>Ajukan SOP</button>
                <a href="{{ route('admin.sops.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection