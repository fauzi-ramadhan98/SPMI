@extends('layouts.admin')

@section('title', 'Tambah Tahun Akademik')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.academic_years.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Tambah Tahun Akademik Baru</h5>
                        <p class="text-muted small mb-0">Tahun akademik aktif dapat dipilih pada Risk Register.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.academic_years.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Tahun Akademik <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required placeholder="Contoh: 2025/2026">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input mt-0 shadow-sm me-3" type="checkbox" role="switch" id="is_active" name="is_active" value="1" style="width: 2.5rem; height: 1.25rem;" checked>
                            <div>
                                <label class="form-check-label fw-bold" for="is_active">Aktif</label>
                                <p class="text-muted small mb-0">Tahun akademik aktif akan muncul pada dropdown Risk Register.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.academic_years.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-calendar-days me-2"></i> Simpan Tahun Akademik
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
