@extends('layouts.admin')

@section('title', 'Buat SK Penetapan')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid {{ ($kategori ?? 'penetapan') === 'perubahan' ? 'fa-arrows-rotate' : 'fa-stamp' }} me-2"></i>
            {{ ($kategori ?? 'penetapan') === 'perubahan' ? 'Buat Draf SK Perubahan Standar' : 'Buat Draf SK Penetapan Standar' }}
        </h6>
    </div>
    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="card-body">
        <form method="POST" action="{{ route('admin.standard-decrees.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="kategori" value="{{ $kategori ?? 'penetapan' }}">
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
                <!--<div class="col-12">-->
                <!--    <label class="form-label d-block">Standar Mutu{{ ($kategori ?? 'penetapan') === 'perubahan' ? ' Revisi (Wajib)' : ' (opsional)' }}</label>-->
                <!--    @if(($kategori ?? 'penetapan') === 'perubahan')-->
                <!--    <div class="alert alert-warning py-2 small mb-2">-->
                <!--        <i class="fa-solid fa-triangle-exclamation me-1"></i>SK Perubahan mengikat standar yang sedang <strong>Revisi Draft</strong>. Pilih minimal satu standar.-->
                <!--    </div>-->
                <!--    @endif-->
                <!--    <div class="border rounded p-3" style="max-height:300px; overflow-y:auto;">-->
                <!--        @forelse($standards ?? [] as $std)-->
                <!--            <div class="form-check d-flex justify-content-between align-items-center mb-2">-->
                <!--                <div class="d-flex align-items-center">-->
                <!--                    <input class="form-check-input me-2" type="checkbox" name="standard_ids[]" value="{{ $std->id }}"-->
                <!--                           id="std-{{ $std->id }}" @checked(is_array(old('standard_ids')) && in_array($std->id, old('standard_ids')))>-->
                <!--                    <label class="form-check-label" for="std-{{ $std->id }}" style="cursor: pointer;">-->
                <!--                        <strong>{{ $std->kode_standar }}</strong> — {{ $std->pernyataan_standar }}-->
                <!--                    </label>-->
                <!--                </div>-->
                <!--                @if($std->revisi_status === 'draft_revisi')-->
                <!--                    <span class="badge bg-warning-subtle text-warning">v{{ $std->version }} Revisi Draft</span>-->
                <!--                @else-->
                <!--                    <span class="badge bg-success-subtle text-success">v{{ $std->version }} Aktif</span>-->
                <!--                @endif-->
                <!--            </div>-->
                <!--        @empty-->
                <!--        <p class="text-muted mb-0">{{ ($kategori ?? 'penetapan') === 'perubahan' ? 'Tidak ada standar berstatus Revisi Draft. Revisi standar dulu lewat menu P5.1.' : 'Tidak ada standar tersedia.' }}</p>-->
                <!--        @endforelse-->
                <!--    </div>-->
                <!--    <div class="form-text">Standar yang dipilih akan terikat pada SK ini. Setelah SK ditetapkan, status revisi standar kembali Aktif.</div>-->
                <!--</div>-->
                <div class="col-12">
                    <label class="form-label d-block">Dokumen SPMI (Status Draft)</label>
                    <div class="border rounded p-3" style="max-height:300px; overflow-y:auto;">
                        @forelse($draftDocuments as $doc)
                            <div class="form-check d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="document_ids[]" value="{{ $doc->id }}"
                                           id="doc-{{ $doc->id }}" @checked(is_array(old('document_ids')) && in_array($doc->id, old('document_ids')))>
                                    <label class="form-check-label" for="doc-{{ $doc->id }}" style="cursor: pointer;">
                                        <strong>{{ $doc->code }}</strong> — {{ $doc->title }}
                                    </label>
                                </div>
                                <span class="badge bg-secondary">Draft</span>
                            </div>
                        @empty
                        <p class="text-muted mb-0">Tidak ada Dokumen Mutu dengan status Draft.</p>
                        @endforelse
                    </div>
                    <div class="form-text">Pilih Dokumen Mutu yang ingin di-SK-kan. Setelah SK ditetapkan, Dokumen akan otomatis menjadi Aktif.</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan</button>
                <a href="{{ ($kategori ?? 'penetapan') === 'perubahan' ? route('admin.standard-decrees.perubahan') : route('admin.standard-decrees.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection