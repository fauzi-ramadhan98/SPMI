@extends('layouts.admin')

@section('title', 'Buat SK Penetapan')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-stamp me-2"></i>Buat Draf SK Penetapan Standar</h6>
    </div>
    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="card-body">
        <form method="POST" action="{{ route('admin.standard-decrees.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nomor SK</label>
                    <input type="text" name="sk_no" class="form-control" value="{{ old('sk_no') }}" placeholder="mis. SK.05/KMI/2026" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tentang (Judul SK)</label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" placeholder="mis. Penetapan Standar Mutu Akademik STMIK Mardira Indonesia Tahun 2026-2030" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Standar</label>
                    <input type="text" name="nama_standar" class="form-control" value="{{ old('nama_standar') }}" placeholder="mis. Standar Pendidikan, Program Studi Teknik Informatika S1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ditetapkan di</label>
                    <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', 'Bandung') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal SK</label>
                    <input type="date" name="tanggal_sk" class="form-control" value="{{ old('tanggal_sk', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Menimbang</label>
                    <textarea name="menimbang" class="form-control" rows="3" placeholder="mis. bahwa untuk meningkatkan pelaksanaan penjaminan mutu internal, dipandang perlu menetapkan standar ...">{{ old('menimbang') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Mengingat</label>
                    <textarea name="mengingat" class="form-control" rows="3" placeholder="mis. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional; ...">{{ old('mengingat') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Memutuskan</label>
                    <textarea name="memutuskan" class="form-control" rows="3" placeholder="mis. Menetapkan Standar Mutu Akademik sebagai pedoman penyelenggaraan ...">{{ old('memutuskan') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Unggah SK Final (Bertanda Tangan &amp; Stempel)</label>
                    <input type="file" name="file_sk_final" class="form-control" accept="application/pdf">
                    <div class="form-text">Format PDF, maksimal 10 MB. Bisa juga diunggah nanti melalui tombol "Upload SK" pada daftar SK.</div>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Standar Mutu yang Ditetapkan</label>
                    <div class="border rounded p-3" style="max-height:300px; overflow-y:auto;">
                        @forelse($standards as $s)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="standard_ids[]" value="{{ $s->id }}"
                                   id="std-{{ $s->id }}" @checked(is_array(old('standard_ids')) && in_array($s->id, old('standard_ids')))>
                            <label class="form-check-label" for="std-{{ $s->id }}">
                                {{ $s->kode_standar ?: $s->id }} — {{ $s->name ?: $s->pernyataan_standar }}
                            </label>
                        </div>
                        @empty
                        <p class="text-muted mb-0">Belum ada standar aktif. Buat dahulu di menu <em>Standar Mutu</em>.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Draf</button>
                <a href="{{ route('admin.standard-decrees.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection