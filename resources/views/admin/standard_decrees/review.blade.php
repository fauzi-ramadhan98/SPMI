@extends('layouts.admin')

@section('title', 'Koreksi & Tetapkan SK')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-pen-nib me-2"></i>Koreksi Isi SK Penetapan — {{ $decree->sk_no }}
        </h6>
    </div>
    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success m-3">{{ session('success') }}</div>
    @endif

    <div class="card-body">
        <form method="POST" action="{{ route('admin.standard-decrees.review-update', $decree->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nomor SK</label>
                    <input type="text" name="sk_no" class="form-control" value="{{ old('sk_no', $decree->sk_no) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tentang (Judul SK)</label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul', $decree->judul) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Standar / Lingkup Audit</label>
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
            </div>

            <h6 class="mt-4 fw-bold text-primary">Lampiran — Daftar Nama Tim Auditor (dari Siklus AMI)</h6>
            @if($decree->cycle)
                <p class="small text-muted mb-2">
                    Sumber: {{ $decree->cycle->name }} — {{ $decree->cycle->academic_year }}
                    ({{ $decree->cycle->semester }}). Jumlah auditor unik: {{ count($decree->auditors()) }}.
                </p>
            @else
                <p class="small text-muted mb-2">Belum ada Siklus AMI dipilih pada draf ini.</p>
            @endif
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="bg-light">
                        <tr><th>NO</th><th>NIK / NIDN</th><th>NAMA</th><th>JABATAN</th></tr>
                    </thead>
                    <tbody>
                        @forelse($decree->auditors() as $i => $a)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $a['nik'] }}</td>
                            <td>{{ $a['nama'] }}</td>
                            <td>{{ $a['jabatan'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada auditor pada siklus terpilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Koreksi</button>
                <a href="{{ route('admin.standard-decrees.pdf', $decree) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fa-solid fa-file-pdf me-1"></i>Preview PDF
                </a>
                <form action="{{ route('admin.standard-decrees.verify', $decree->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Tandatangani & tetapkan SK \"{{ $decree->sk_no }}\"? TTD Ketua akan ditempel otomatis dan SK terkunci.');">
                    @csrf
                    <button class="btn btn-success btn-custom"><i class="fa-solid fa-stamp me-1"></i>Tandatangani & Tetapkan</button>
                </form>
                <a href="{{ route('admin.standard-decrees.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection