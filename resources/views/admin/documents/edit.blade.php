@extends('layouts.admin')

@section('title', 'Edit Dokumen')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-warning">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Edit Informasi Dokumen</h5>
                        <p class="text-muted small mb-0">Perbarui metadata dokumen untuk: <strong>{{ $document->title }}</strong></p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.documents.update', $document->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Judul / Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0 @error('title') is-invalid @enderror"
                            value="{{ old('title', $document->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Kategori / Jenis Dokumen <span class="text-danger">*</span></label>
                            <select name="document_category_id" class="form-select form-select-lg bg-light border-0 @error('document_category_id') is-invalid @enderror" required>
                                <option value="" disabled>Pilih Kategori...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('document_category_id', $document->document_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('document_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Program Studi</label>
                            <select name="academic_program_id" class="form-select form-select-lg bg-light border-0">
                                <option value="">-- Dokumen Institusi Umum --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('academic_program_id', $document->academic_program_id) == $program->id ? 'selected' : '' }}>
                                        {{ $program->degree_level }} {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Unit Kerja</label>
                            <select name="unit_id" class="form-select form-select-lg bg-light border-0">
                                <option value="">-- Dokumen Institusi Umum --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $document->unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tahun Akademik</label>
                            <input type="text" name="academic_year" class="form-control form-control-lg bg-light border-0"
                                value="{{ old('academic_year', $document->academic_year) }}" placeholder="2024/2025">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Semester</label>
                            <select name="semester" class="form-select form-select-lg bg-light border-0">
                                <option value="">-- Pilih Semester --</option>
                                <option value="Ganjil" {{ old('semester', $document->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester', $document->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                                <option value="Tahunan" {{ old('semester', $document->semester) == 'Tahunan' ? 'selected' : '' }}>Satu Tahun Penuh</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Ganti File (Opsional)</label>
                        <input type="file" name="file" class="form-control form-control-lg @error('file') is-invalid @enderror"
                            accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <div class="form-text text-muted small"><i class="fa-solid fa-circle-info me-1"></i> Kosongkan jika tidak ingin mengganti file yang ada.</div>
                        @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0" type="checkbox" role="switch" id="is_public" name="is_public" value="1"
                                style="width: 2.5rem; height: 1.25rem;" {{ old('is_public', $document->is_public) ? 'checked' : '' }}>
                            <div>
                                <label class="form-check-label fw-bold" for="is_public">Dokumen Publik</label>
                                <p class="text-muted small mb-0">Tampil di halaman unduhan publik website SPMI.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.documents.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
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
