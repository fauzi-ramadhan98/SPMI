@extends('layouts.admin')

@section('title', 'Tambah Dokumen')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.documents.index', ['module' => request('module', 'dokumen_mutu')]) }}" class="btn btn-light border rounded-circle btn-sm px-2 py-2">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-bold">
        <i class="fa-solid fa-plus me-2 text-primary"></i>
        Tambah {{ request('module') == 'dokumen_mutu' ? 'Dokumen Mutu' : (request('module') == 'surat_tugas' ? 'Surat Tugas' : 'RTM') }}
    </h5>
</div>

<div class="card shadow-sm border-0 rounded-4" style="border-top: 4px solid #0d6efd!important;">
    <div class="card-body p-4">
        <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="module" value="{{ request('module', 'dokumen_mutu') }}">
            
            @php
                $autoGenerate = setting('document_auto_generate') === '1';
                $isDokumenMutuModule = request('module', 'dokumen_mutu') === 'dokumen_mutu';
                $useAutoCode = $autoGenerate && $isDokumenMutuModule;
            @endphp

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    @if($useAutoCode)
                        <label class="form-label fw-bold">Kode Dokumen</label>
                        <div class="form-control-plaintext">
                            <span class="badge bg-info text-dark">Otomatis</span>
                            <div class="form-text">Kode dokumen akan dibuat otomatis mengikuti template
                                <code>{{ setting('document_code_format', '{prefix}/{parent_code}.{child_code}.{seq}') }}</code>
                                setelah kategori & sub kategori dipilih. Format dapat diubah pada
                                <a href="{{ route('admin.settings.index') }}">Konfigurasi Aplikasi</a>.</div>
                        </div>
                    @else
                        <label class="form-label fw-bold">Kode Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               placeholder="Contoh: DOK-001" value="{{ old('code') }}" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Judul Dokumen <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                           placeholder="Masukkan judul dokumen" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Nomor Revisi</label>
                    <input type="number" name="version" class="form-control @error('version') is-invalid @enderror" 
                           value="{{ old('version', 0) }}" placeholder="misal: 6">
                    @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status Dokumen</label>
                    <div class="form-control-plaintext">
                        <span class="badge bg-secondary">Draft</span>
                        <div class="form-text">Status otomatis Draft. Akan menjadi Aktif setelah SK ditetapkan oleh Pimpinan.</div>
                    </div>
                </div>

                @if($isDokumenMutuModule)
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kategori Dokumen <span class="text-danger">*</span></label>
                        <select name="document_category_id" class="form-select @error('document_category_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('document_category_id') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                            @foreach($categoryOptions as $cat)
                                <option value="{{ $cat->id }}" {{ old('document_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $cat->treeDepth) }}{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Kategori dengan sub-kategori ditandai garis bawah ("—"), semakin dalam sub-kategori, semakin banyak garisnya.</div>
                        @error('document_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @else
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kategori / Jenis Dokumen <span class="text-danger">*</span></label>
                        <select name="document_category_id" class="form-select @error('document_category_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('document_category_id') ? '' : 'selected' }}>Pilih Kategori...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('document_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('document_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif

                <div class="col-md-6">
                    @if(request('module') === 'dokumen_mutu')
                        <label class="form-label fw-bold">Tahun Terbit / Akademik</label>
                        <input type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" 
                               value="{{ old('academic_year') }}" placeholder="Contoh: 2024/2025">
                        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @else
                        <label class="form-label fw-bold">Siklus AMI</label>
                        <select name="audit_cycle_id" class="form-select @error('audit_cycle_id') is-invalid @enderror">
                            <option value="">-- Pilih Siklus AMI --</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" {{ old('audit_cycle_id') == $cycle->id ? 'selected' : '' }}>{{ $cycle->name }} - {{ $cycle->academic_year }}</option>
                            @endforeach
                        </select>
                        @error('audit_cycle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Deskripsi Dokumen</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Tambahkan deskripsi singkat jika diperlukan...">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">File Dokumen <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                    <div class="form-text">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Maks 10MB)</div>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_public" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold ms-2" for="is_public">Jadikan Dokumen Publik</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('admin.documents.index', ['module' => request('module', 'dokumen_mutu')]) }}" class="btn btn-light rounded-pill px-4 fw-semibold border me-md-2">Batal</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Unggah Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
