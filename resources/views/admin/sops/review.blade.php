@extends('layouts.admin')

@section('title', 'Review SOP: ' . $sop->title)

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-clipboard-check me-2"></i>Review SOP
        </h6>
        {!! $sop->statusBadge() !!}
    </div>

    <div class="card-body">
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info me-2"></i>
            Anda mereview SOP <strong>"{{ $sop->title }}"</strong> dari unit <strong>{{ $sop->unit?->name }}</strong>.
            Pilih <strong>Setujui</strong> jika dokumen sudah sesuai, atau <strong>Kembalikan (Revisi)</strong> jika perlu perbaikan.
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Diajukan Oleh</label>
                <div class="fw-semibold">{{ $sop->creator?->name }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted mb-1">Tanggal Pengajuan</label>
                <div class="fw-semibold">{{ $sop->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>

        @if($sop->description)
        <div class="mb-4">
            <label class="form-label text-muted mb-1">Deskripsi / Tujuan</label>
            <div class="border rounded p-3 bg-light">{{ nl2br(e($sop->description)) }}</div>
        </div>
        @endif

        @if($sop->file_path)
        <div class="mb-4">
            <label class="form-label text-muted mb-1">File SOP</label>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.sops.preview', $sop) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-file-pdf me-1"></i>Pratinjau
                </a>
                <a href="{{ route('admin.sops.download', $sop) }}" class="btn btn-sm btn-outline-info">
                    <i class="fa-solid fa-download me-1"></i>Unduh
                </a>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.sops.review', $sop) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Keputusan <span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="action" value="approve" id="action_approve" checked>
                    <label class="form-check-label fw-semibold text-success" for="action_approve">
                        <i class="fa-solid fa-check me-1"></i> Setujui (Approved)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="action" value="reject" id="action_reject">
                    <label class="form-check-label fw-semibold text-danger" for="action_reject">
                        <i class="fa-solid fa-rotate-left me-1"></i> Kembalikan (Revisi)
                    </label>
                </div>
            </div>

            <div class="mb-3" id="notes_field" style="display: none;">
                <label for="review_notes" class="form-label fw-semibold">Catatan Revisi <span class="text-danger">*</span></label>
                <textarea name="review_notes" id="review_notes" class="form-control" rows="4" placeholder="Jelaskan poin-poin yang perlu diperbaiki..."></textarea>
                <div class="form-text">Wajib diisi jika memilih "Kembalikan (Revisi)".</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-custom" id="submit_btn">
                    <i class="fa-solid fa-check me-1"></i> Simpan Keputusan
                </button>
                <a href="{{ route('admin.sops.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveRadio = document.getElementById('action_approve');
    const rejectRadio = document.getElementById('action_reject');
    const notesField = document.getElementById('notes_field');
    const notesTextarea = document.getElementById('review_notes');

    function toggleNotes() {
        if (rejectRadio.checked) {
            notesField.style.display = 'block';
            notesTextarea.required = true;
        } else {
            notesField.style.display = 'none';
            notesTextarea.required = false;
        }
    }

    approveRadio.addEventListener('change', toggleNotes);
    rejectRadio.addEventListener('change', toggleNotes);
    toggleNotes();
});
</script>
@endsection