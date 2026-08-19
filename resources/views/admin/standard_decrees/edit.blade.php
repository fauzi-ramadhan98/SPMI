@extends('layouts.admin')

@section('title', 'Edit SK Penetapan')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-stamp me-2"></i>Edit Draf SK Penetapan — {{ $decree->sk_no }}</h6>
    </div>
    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="card-body">
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
                    <label class="form-label d-block">Standar Mutu yang Ditetapkan</label>
                    <div class="border rounded p-3" style="max-height:300px; overflow-y:auto;">
                        @forelse($standards as $s)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="standard_ids[]" value="{{ $s->id }}"
                                   id="std-{{ $s->id }}" @checked(in_array($s->id, $selected))>
                            <label class="form-check-label" for="std-{{ $s->id }}">
                                {{ $s->kode_standar ?: $s->id }} — {{ $s->name ?: $s->pernyataan_standar }}
                            </label>
                        </div>
                        @empty
                        <p class="text-muted mb-0">Belum ada standar aktif.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-1"></i>Perbarui Draf</button>
                <a href="{{ route('admin.standard-decrees.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection