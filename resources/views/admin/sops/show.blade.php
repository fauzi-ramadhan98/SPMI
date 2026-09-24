@extends('layouts.admin')

@section('title', 'Detail SOP: ' . $sop->title)

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-file-contract me-2"></i>{{ $sop->title }}
        </h6>
        <div class="d-flex gap-2">
            {!! $sop->statusBadge() !!}
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Unit</label>
                <div class="fw-semibold">{{ $sop->unit?->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Diajukan Oleh</label>
                <div class="fw-semibold">{{ $sop->creator?->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Tanggal Pengajuan</label>
                <div class="fw-semibold">{{ $sop->created_at->format('d M Y H:i') }}</div>
            </div>
            @if($sop->reviewed_at)
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Direview Oleh</label>
                <div class="fw-semibold">{{ $sop->reviewer?->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Tanggal Review</label>
                <div class="fw-semibold">{{ $sop->reviewed_at->format('d M Y H:i') }}</div>
            </div>
            @endif
        </div>

        @if($sop->description)
        <div class="mb-4">
            <label class="form-label text-muted mb-1">Deskripsi / Tujuan</label>
            <div class="border rounded p-3 bg-light">{{ nl2br(e($sop->description)) }}</div>
        </div>
        @endif

        @if($sop->review_notes)
        <div class="alert alert-{{ $sop->isApproved() ? 'success' : ($sop->isRevisi() ? 'danger' : 'warning') }} mb-4">
            <strong>Catatan Review SPMI:</strong>
            <div class="mt-2">{{ nl2br(e($sop->review_notes)) }}</div>
        </div>
        @endif

        <div class="d-flex gap-2 flex-wrap">
            @if($sop->file_path)
                <a href="{{ route('admin.sops.preview', $sop) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fa-solid fa-file-pdf me-1"></i>Pratinjau
                </a>
                <a href="{{ route('admin.sops.download', $sop) }}" class="btn btn-outline-info">
                    <i class="fa-solid fa-download me-1"></i>Unduh
                </a>
            @else
                <span class="text-muted align-self-center">Belum ada file</span>
            @endif

            @hasrole('unit')
            @if($sop->isRevisi())
            <a href="{{ route('admin.sops.show', $sop) }}" class="btn btn-warning">
                <i class="fa-solid fa-upload me-1"></i>Upload Revisi
            </a>
            @endif
            @endhasrole

            <a href="{{ route('admin.sops.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
</div>

@hasrole('unit')
@if($sop->isRevisi())
<div class="card card-custom shadow-sm mt-3">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-warning"><i class="fa-solid fa-upload me-2"></i>Upload File Revisi</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.sops.upload-revision', $sop) }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">File SOP Revisi <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
                <div class="form-text">Format: PDF, DOC, DOCX. Maksimal 10 MB. File lama akan digantikan.</div>
            </div>
            <button class="btn btn-warning"><i class="fa-solid fa-upload me-1"></i>Kirim Revisi</button>
            <a href="{{ route('admin.sops.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
        </form>
    </div>
</div>
@endif
@endhasrole
@endsection