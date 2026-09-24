@extends('layouts.admin')

@section('title', 'Edit Siklus AMI')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-warning">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.audit.cycles.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border icon-only-btn" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Edit Siklus AMI</h5>
                        <p class="text-muted small mb-0">Perbarui informasi dan status siklus: <strong>{{ $cycle->name }}</strong></p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.audit.cycles.update', $cycle->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Nama Siklus AMI <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name', $cycle->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tahun Akademik <span class="text-danger">*</span></label>
                            <input type="text" name="academic_year" class="form-control form-control-lg bg-light border-0 @error('academic_year') is-invalid @enderror"
                                value="{{ old('academic_year', $cycle->academic_year) }}" required>
                            @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select form-select-lg bg-light border-0" required>
                                <option value="Ganjil" {{ old('semester', $cycle->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester', $cycle->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control form-control-lg bg-light border-0"
                                value="{{ old('start_date', $cycle->start_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control form-control-lg bg-light border-0"
                                value="{{ old('end_date', $cycle->end_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Status Siklus <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-lg bg-light border-0" required>
                            <option value="draft" {{ old('status', $cycle->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="aktif" {{ old('status', $cycle->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ old('status', $cycle->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-bold text-muted small">Deskripsi / Catatan (Opsional)</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3">{{ old('description', $cycle->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.audit.cycles.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
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
