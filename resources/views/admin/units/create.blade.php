@extends('layouts.admin')

@section('title', 'Tambah Unit Kerja')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.units.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Tambah Unit Kerja Baru</h5>
                        <p class="text-muted small mb-0">Daftarkan unit kerja yang akan menjadi auditee (selain prodi) dalam AMI.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.units.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-lg bg-light border-0 @error('code') is-invalid @enderror"
                            value="{{ old('code') }}" required placeholder="Contoh: TIK, LAM, HUM">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Nama Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required placeholder="Contoh: Teknologi Informasi & Komunikasi">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-select form-select-lg bg-light border-0 @error('category') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih kategori unit...</option>
                            <option value="administratif_umum" {{ old('category') == 'administratif_umum' ? 'selected' : '' }}>Administratif Umum</option>
                            <option value="layanan_akademik" {{ old('category') == 'layanan_akademik' ? 'selected' : '' }}>Layanan Akademik</option>
                            <option value="penunjang" {{ old('category') == 'penunjang' ? 'selected' : '' }}>Penunjang</option>
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Kepala Unit</label>
                            <input type="text" name="head_name" class="form-control form-control-lg bg-light border-0" value="{{ old('head_name') }}" placeholder="Nama kepala unit (opsional)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">NIDN/NIK Kepala</label>
                            <input type="text" name="head_nidn" class="form-control form-control-lg bg-light border-0" value="{{ old('head_nidn') }}" placeholder="NIDN/NIK (opsional)">
                        </div>
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input mt-0 shadow-sm me-3" type="checkbox" role="switch" id="is_active" name="is_active" value="1" style="width: 2.5rem; height: 1.25rem;" checked>
                            <div>
                                <label class="form-check-label fw-bold" for="is_active">Unit Aktif</label>
                                <p class="text-muted small mb-0">Unit aktif dapat dipilih pada penugasan audit dan memiliki user.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.units.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-building me-2"></i> Simpan Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
