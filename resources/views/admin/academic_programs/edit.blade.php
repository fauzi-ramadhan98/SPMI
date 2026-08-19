@extends('layouts.admin')

@section('title', 'Edit Program Studi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('admin.academic_programs.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill me-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h4 class="fw-bold mb-0 text-dark">Edit Program Studi</h4>
                <p class="text-muted small mb-0">{{ $academicProgram->degree_level }} {{ $academicProgram->name }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-body p-4">
                <form action="{{ route('admin.academic_programs.update', $academicProgram->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Nama Prodi --}}
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold">Nama Program Studi <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $academicProgram->name) }}"
                                   class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                                   required placeholder="Contoh: Teknik Informatika">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Instansi/Fakultas --}}
                        <div class="col-md-8">
                            <label for="faculty" class="form-label fw-semibold">Instansi / Institusi</label>
                            <input type="text" id="faculty" name="faculty" value="{{ old('faculty', $academicProgram->faculty) }}"
                                   class="form-control bg-light border-0 @error('faculty') is-invalid @enderror"
                                   placeholder="Contoh: STMIK Mardira Indonesia">
                            @error('faculty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Jenjang --}}
                        <div class="col-md-4">
                            <label for="degree_level" class="form-label fw-semibold">Jenjang <span class="text-danger">*</span></label>
                            <select id="degree_level" name="degree_level"
                                    class="form-select bg-light border-0 @error('degree_level') is-invalid @enderror" required>
                                @foreach(['D3','S1','S2','S3','Profesi'] as $level)
                                <option value="{{ $level }}" {{ old('degree_level', $academicProgram->degree_level) == $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                                @endforeach
                            </select>
                            @error('degree_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- ===== KETUA PRODI ===== --}}
                        <div class="col-md-8">
                            <label for="head_name" class="form-label fw-semibold">
                                <i class="fa-solid fa-user-tie me-1 text-primary"></i>
                                Nama Ketua Program Studi
                            </label>
                            <input type="text" id="head_name" name="head_name"
                                   value="{{ old('head_name', $academicProgram->head_name) }}"
                                   class="form-control form-control-lg bg-light border-0 @error('head_name') is-invalid @enderror"
                                   placeholder="Contoh: Dr. Nama Ketua, M.T">
                            @error('head_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="head_nidn" class="form-label fw-semibold">
                                <i class="fa-solid fa-id-card me-1 text-primary"></i>
                                NIDN / NIK Ketua Prodi
                            </label>
                            <input type="text" id="head_nidn" name="head_nidn"
                                   value="{{ old('head_nidn', $academicProgram->head_nidn) }}"
                                   class="form-control form-control-lg bg-light border-0 @error('head_nidn') is-invalid @enderror"
                                   placeholder="Contoh: 0412345678">
                            @error('head_nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="form-text text-muted">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Nama dan NIDN Ketua Prodi akan muncul sebagai <strong>Auditee</strong> (tanda tangan) pada laporan PDF hasil audit.
                            </div>
                        </div>

                        {{-- Akreditasi --}}
                        <div class="col-md-4">
                            <label for="accreditation" class="form-label fw-semibold">Akreditasi</label>
                            <input type="text" id="accreditation" name="accreditation"
                                   value="{{ old('accreditation', $academicProgram->accreditation) }}"
                                   class="form-control bg-light border-0 @error('accreditation') is-invalid @enderror"
                                   placeholder="Contoh: A, B, C, Baik Sekali">
                            @error('accreditation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-md-8 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       {{ old('is_active', $academicProgram->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Program Studi Aktif
                                </label>
                            </div>
                        </div>

                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.academic_programs.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection
