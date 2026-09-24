@extends('layouts.admin')

@section('title', 'Edit SK Penetapan')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid {{ $decree->isPerubahan() ? 'fa-arrows-rotate' : 'fa-stamp' }} me-2"></i>
            Edit {{ $decree->isPerubahan() ? 'SK Perubahan' : 'SK Penetapan' }} — {{ $decree->sk_no }}
            @if($decree->isPerubahan())
            <span class="badge bg-warning-subtle text-warning ms-1">P5.2</span>
            @endif
        </h6>
    </div>
    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="card-body">
        @if($decree->isDitolak() && $decree->reject_reason)
        <div class="alert alert-danger d-flex align-items-start mb-3">
            <i class="fa-solid fa-exclamation-triangle me-2 mt-1"></i>
            <div>
                <strong>SK ini ditolak oleh Pimpinan.</strong><br>
                <span class="small">Alasan: {{ $decree->reject_reason }}</span><br>
                <span class="small text-muted">Silakan perbaiki sesuai catatan di atas, lalu klik "Perbarui" untuk mengajukan ulang.</span>
            </div>
        </div>
        @endif
        <form method="POST" action="{{ route('admin.standard-decrees.update', $decree->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nomor SK</label>
                    <input type="text" name="sk_no" class="form-control" value="{{ old('sk_no', $decree->sk_no) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tentang (Judul SK)</label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul', $decree->judul) }}" placeholder="mis. Penetapan Standar Mutu Akademik STMIK Mardira Indonesia Tahun 2026-2030" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Standar</label>
                    <input type="text" name="nama_standar" class="form-control" value="{{ old('nama_standar', $decree->nama_standar) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ditetapkan di</label>
                    <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $decree->lokasi ?? 'Bandung') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal SK</label>
                    <input type="date" name="tanggal_sk" class="form-control" value="{{ old('tanggal_sk', $decree->tanggal_sk?->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Menimbang</label>
                    <textarea name="menimbang" class="form-control" rows="3">{{ old('menimbang', $decree->menimbang) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Mengingat</label>
                    <textarea name="mengingat" class="form-control" rows="3">{{ old('mengingat', $decree->mengingat) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Memutuskan</label>
                    <textarea name="memutuskan" class="form-control" rows="3">{{ old('memutuskan', $decree->memutuskan) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Unggah SK Final (Bertanda Tangan &amp; Stempel)</label>
                    @if($decree->file_path)
                    <p class="small text-muted mb-2">
                        File saat ini: <a href="{{ route('admin.standard-decrees.download-file', $decree) }}">{{ $decree->file_name }}</a>
                    </p>
                    @endif
                    <input type="file" name="file_sk_final" class="form-control" accept="application/pdf">
                    <div class="form-text">Format PDF, maksimal 10 MB. Kosongkan jika tidak mengganti.</div>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Standar Mutu{{ $decree->isPerubahan() ? ' (Wajib — hanya standar Revisi Draft)' : ' (opsional)' }}</label>
                    <div class="border rounded p-3" style="max-height:300px; overflow-y:auto;">
                        @forelse($standards as $std)
                        <div class="form-check d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="standard_ids[]" value="{{ $std->id }}"
                                       id="std-{{ $std->id }}" @checked(in_array($std->id, $selected))>
                                <label class="form-check-label" for="std-{{ $std->id }}" style="cursor: pointer;">
                                    <strong>{{ $std->kode_standar }}</strong> — {{ $std->pernyataan_standar }}
                                </label>
                            </div>
                            @if(in_array($std->id, $selected))
                                <span class="badge bg-success">Terpilih</span>
                            @elseif($std->revisi_status === 'draft_revisi')
                                <span class="badge bg-warning-subtle text-warning">Revisi Draft</span>
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </div>
                        @empty
                        <p class="text-muted mb-0">Tidak ada standar tersedia.</p>
                        @endforelse
                    </div>
                    <div class="form-text">Standar yang dicentang akan terikat pada SK ini (menggantikan pilihan sebelumnya).</div>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Dokumen Mutu</label>
                    <div class="border rounded p-3" style="max-height:300px; overflow-y:auto;">
                        @forelse($draftDocuments as $doc)
                        <div class="form-check d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="document_ids[]" value="{{ $doc->id }}"
                                       id="doc-{{ $doc->id }}" @checked(in_array($doc->id, $selectedDocIds))>
                                <label class="form-check-label" for="doc-{{ $doc->id }}" style="cursor: pointer;">
                                    <strong>{{ $doc->code }}</strong> — {{ $doc->title }}
                                </label>
                            </div>
                            @if(in_array($doc->id, $selectedDocIds))
                                <span class="badge bg-success">Terpilih</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </div>
                        @empty
                        <p class="text-muted mb-0">Tidak ada Dokumen Mutu tersedia.</p>
                        @endforelse
                    </div>
                    <div class="form-text">Pilih Dokumen Mutu yang ingin di-SK-kan.</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-1"></i>Perbarui</button>
                <a href="{{ $decree->isPerubahan() ? route('admin.standard-decrees.perubahan') : route('admin.standard-decrees.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection