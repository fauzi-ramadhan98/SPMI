@extends('layouts.admin')

@section('title', 'Unggah Dokumen Mutu')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header-custom border-0 bg-white pt-4 pb-0">
                <div class="d-flex align-items-center mb-3">
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-light rounded-circle me-3"><i class="fa-solid fa-arrow-left"></i></a>
                    <h5 class="mb-0 fw-bold">Unggah Dokumen Baru</h5>
                </div>
                <p class="text-muted small ps-5 mb-0">Lengkapi formulir di bawah ini untuk mengunggah dokumen SPMI, AMI, atau dokumen mutu lainnya.</p>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="module" value="{{ $module }}">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Judul / Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0 @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="Contoh: Standar Penilaian Pembelajaran">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Kategori / Jenis Dokumen <span class="text-danger">*</span></label>
                            <select name="document_category_id" class="form-select form-select-lg bg-light border-0 @error('document_category_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Pilih Kategori...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('document_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('document_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Atribusi Program Studi</label>
                            <select name="academic_program_id" class="form-select form-select-lg bg-light border-0 @error('academic_program_id') is-invalid @enderror">
                                <option value="">-- Dokumen Institusi (Umum) --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('academic_program_id') == $program->id ? 'selected' : '' }}>{{ $program->degree_level }} {{ $program->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Atribusi Unit Kerja</label>
                            <select name="unit_id" class="form-select form-select-lg bg-light border-0 @error('unit_id') is-invalid @enderror">
                                <option value="">-- Dokumen Institusi (Umum) --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tahun Akademik</label>
                            <input type="text" name="academic_year" class="form-control form-control-lg bg-light border-0 @error('academic_year') is-invalid @enderror" value="{{ old('academic_year') }}" placeholder="Contoh: 2024/2025">
                            @error('academic_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Semester</label>
                            <select name="semester" class="form-select form-select-lg bg-light border-0 @error('semester') is-invalid @enderror">
                                <option value="">-- Pilih Semester --</option>
                                <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                                <option value="Tahunan" {{ old('semester') == 'Tahunan' ? 'selected' : '' }}>Satu Tahun Penuh</option>
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Deskripsi Singkat (Opsional)</label>
                        <textarea name="description" class="form-control form-control-lg bg-light border-0 @error('description') is-invalid @enderror" rows="3" placeholder="Tambahkan keterangan tentang dokumen ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Pilih File <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="file" name="file" class="form-control form-control-lg @error('file') is-invalid @enderror" id="file" required accept=".pdf,.doc,.docx,.xls,.xlsx">
                        </div>
                        <div class="form-text text-muted small"><i class="fa-solid fa-circle-info me-1"></i> Format yang diizinkan: PDF, DOC/DOCX, XLS/XLSX. Maksimal ukuran file: 10MB.</div>
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input mt-0 shadow-sm me-3" type="checkbox" role="switch" id="is_public" name="is_public" value="1" style="width: 2.5rem; height: 1.25rem;" {{ old('is_public') ? 'checked' : '' }}>
                            <div>
                                <label class="form-check-label fw-bold" for="is_public">Jadikan Dokumen Publik</label>
                                <p class="text-muted small mb-0">Jika diaktifkan, dokumen ini akan muncul di direktori depan (website publik) dan dapat diunduh oleh siapa saja.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-light rounded-pill px-4 fw-semibold border me-md-2">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Unggah Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
