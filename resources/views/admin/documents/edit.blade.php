@extends('layouts.admin')

@section('title', 'Edit Dokumen')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.documents.index', ['module' => request('module', 'dokumen_mutu')]) }}" class="btn btn-light border rounded-circle btn-sm px-2 py-2">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h5 class="mb-0 fw-bold">
        <i class="fa-solid fa-pen-to-square me-2 text-warning"></i>
        Edit {{ request('module') == 'dokumen_mutu' ? 'Dokumen Mutu' : (request('module') == 'surat_tugas' ? 'Surat Tugas' : 'RTM') }}
    </h5>
</div>

<div class="card shadow-sm border-0 rounded-4" style="border-top: 4px solid #ffc107!important;">
    <div class="card-body p-4">
        <form action="{{ route('admin.documents.update', $document->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="module" value="{{ request('module', 'dokumen_mutu') }}">
            
            <div class="row g-4 mb-4">
                <!-- Kode Dokumen (read‑only) -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kode Dokumen <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                           placeholder="Contoh: DOK-001" value="{{ old('code', $document->code) }}" required readonly>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold">Judul Dokumen <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                           placeholder="Masukkan judul dokumen" value="{{ old('title', $document->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Nomor Revisi</label>
                    <input type="number" name="version" class="form-control @error('version') is-invalid @enderror" 
                           value="{{ old('version', $document->version) }}" placeholder="misal: 6">
                    @error('version')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Status tidak bisa di‑edit, jadi kita tampilkan saja sebagai badge -->
                <div class="col-md-3 d-flex align-items-center">
                    <span class="badge bg-{{ $document->status == 'aktif' ? 'success' : 'warning' }}">
                        {{ ucfirst($document->status) }}
                    </span>
                </div>

                @if($document->status === 'aktif')
                <div class="col-12">
                    <div class="alert alert-warning py-2 px-3 mb-0 small">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        Dokumen ini berstatus <strong>Aktif</strong>. Jika diubah, status akan otomatis kembali ke <strong>Draft</strong> dan memerlukan persetujuan ulang oleh Pimpinan.
                    </div>
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label fw-bold">Kategori / Jenis Dokumen <span class="text-danger">*</span></label>
                    <select name="document_category_id" class="form-select @error('document_category_id') is-invalid @enderror" required>
                        <option value="" disabled {{ old('document_category_id', $document->document_category_id) ? '' : 'selected' }}>Pilih Kategori...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('document_category_id', $document->document_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('document_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    @if(request('module') === 'dokumen_mutu')
                        <label class="form-label fw-bold">Tahun Terbit / Akademik</label>
                        <input type="text" name="academic_year" class="form-control @error('academic_year') is-invalid @enderror" 
                               value="{{ old('academic_year', $document->academic_year) }}" placeholder="Contoh: 2024/2025">
                        @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @else
                        <label class="form-label fw-bold">Siklus AMI</label>
                        <select name="audit_cycle_id" class="form-select @error('audit_cycle_id') is-invalid @enderror">
                            <option value="">-- Pilih Siklus AMI --</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" {{ old('audit_cycle_id', $document->audit_cycle_id) == $cycle->id ? 'selected' : '' }}>{{ $cycle->name }} - {{ $cycle->academic_year }}</option>
                            @endforeach
                        </select>
                        @error('audit_cycle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Deskripsi Dokumen</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Tambahkan deskripsi singkat jika diperlukan...">{{ old('description', $document->description) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">File Dokumen</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <div class="form-text">Ganti file jika perlu (Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG)</div>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_public" name="is_public" value="1" {{ old('is_public', $document->is_public) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold ms-2" for="is_public">Jadikan Dokumen Publik</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('admin.documents.index', ['module' => request('module', 'dokumen_mutu')]) }}" class="btn btn-light rounded-pill px-4 fw-semibold border me-md-2">Batal</a>
                <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Riwayat Perubahan --}}
@if($activityLogs && $activityLogs->count())
<div class="card shadow-sm border-0 rounded-4 mt-4" style="border-top: 4px solid #17a2b8!important;">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-clock-rotate-left me-2 text-info"></i>Riwayat Perubahan
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Waktu</th>
                        <th width="15%">Oleh</th>
                        <th width="12%">Aksi</th>
                        <th>Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activityLogs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->diffForHumans() }}<br><small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small></td>
                        <td>{{ $log->user_name ?? '-' }}</td>
                        <td>
                            @if($log->action === 'created')
                                <span class="badge bg-success">Dibuat</span>
                            @elseif($log->action === 'updated')
                                <span class="badge bg-warning text-dark">Diperbarui</span>
                            @elseif($log->action === 'deleted')
                                <span class="badge bg-danger">Dihapus</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($log->action) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($log->action === 'updated' && $log->old_values && $log->new_values)
                                <ul class="mb-0 ps-3">
                                    @foreach($log->new_values as $key => $newVal)
                                        @if(isset($log->old_values[$key]) && $log->old_values[$key] != $newVal)
                                            <li>
                                                <strong>{{ str_replace('_', ' ', ucfirst($key)) }}:</strong>
                                                <span class="text-decoration-line-through text-muted">{{ \Illuminate\Support\Str::limit((string) ($log->old_values[$key] ?? ''), 80) }}</span>
                                                <i class="fa-solid fa-arrow-right mx-1 text-muted"></i>
                                                <span class="text-success">{{ \Illuminate\Support\Str::limit((string) ($newVal), 80) }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @elseif($log->action === 'created' && $log->new_values)
                                <span class="text-muted">Data baru dibuat</span>
                            @else
                                <span class="text-muted">{{ $log->description }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
