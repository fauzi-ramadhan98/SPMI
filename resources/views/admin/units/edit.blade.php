@extends('layouts.admin')

@section('title', 'Edit Unit Kerja: ' . $unit->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-warning">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.units.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border icon-only-btn" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Edit Unit Kerja</h5>
                        <p class="text-muted small mb-0">Perbarui data unit: <strong>{{ $unit->name }}</strong></p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.units.update', $unit->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-lg bg-light border-0 @error('code') is-invalid @enderror"
                            value="{{ old('code', $unit->code) }}" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Nama Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name', $unit->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-select form-select-lg bg-light border-0 @error('category') is-invalid @enderror" required>
                            <option value="administratif_umum" {{ old('category', $unit->category) == 'administratif_umum' ? 'selected' : '' }}>Administratif Umum</option>
                            <option value="layanan_akademik" {{ old('category', $unit->category) == 'layanan_akademik' ? 'selected' : '' }}>Layanan Akademik</option>
                            <option value="penunjang" {{ old('category', $unit->category) == 'penunjang' ? 'selected' : '' }}>Penunjang</option>
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Kepala Unit</label>
                            <input type="text" name="head_name" class="form-control form-control-lg bg-light border-0" value="{{ old('head_name', $unit->head_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">NIDN/NIK Kepala</label>
                            <input type="text" name="head_nidn" class="form-control form-control-lg bg-light border-0" value="{{ old('head_nidn', $unit->head_nidn) }}">
                        </div>
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input mt-0 shadow-sm me-3" type="checkbox" role="switch" id="is_active" name="is_active" value="1" style="width: 2.5rem; height: 1.25rem;" {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
                            <div>
                                <label class="form-check-label fw-bold" for="is_active">Unit Aktif</label>
                                <p class="text-muted small mb-0">Unit aktif dapat dipilih pada penugasan audit dan memiliki user.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.units.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm text-dark">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
