@extends('layouts.admin')

@section('title', 'Manajemen Dokumen SPMI')

@section('content')
{{-- <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <ul class="nav nav-pills">
        @foreach(['dokumen_mutu' => 'Dokumen SPMI', 'surat_tugas' => 'Surat Tugas', 'rtm' => 'RTM'] as $key => $label)
            <li class="nav-item">
                <a href="{{ route('admin.documents.index', ['module' => $key]) }}" class="nav-link {{ $module === $key ? 'active fw-semibold' : '' }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>
</div> --}}

<form method="GET" action="{{ route('admin.documents.index') }}" class="row g-2 mb-3 align-items-end">
    <input type="hidden" name="module" value="{{ $module }}">
    <div class="col-md-4">
        <label class="form-label small mb-1">Kategori</label>
        <select name="category_id" class="form-select form-select-sm">
            <option value="">Semua Kategori</option>
            @foreach ($categoryOptions as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ str_repeat('— ', $cat->treeDepth) }}{{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        @if($module === 'dokumen_mutu')
            <label class="form-label small mb-1">Tahun Terbit</label>
            <select name="academic_year" class="form-select form-select-sm">
                <option value="">Semua Tahun</option>
                @foreach ($terbitYears as $tahun)
                    <option value="{{ $tahun }}" {{ request('academic_year') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                @endforeach
            </select>
        @else
            <label class="form-label small mb-1">Siklus AMI</label>
            <select name="cycle_id" class="form-select form-select-sm">
                <option value="">Semua Siklus</option>
                @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" {{ request('cycle_id') == $cycle->id ? 'selected' : '' }}>{{ $cycle->name }} — {{ $cycle->academic_year }}</option>
                @endforeach
            </select>
        @endif
    </div>
    <div class="col-md-4">
        <button class="btn btn-sm btn-outline-primary">Terapkan Filter</button>
        <a href="{{ route('admin.documents.index', ['module' => $module]) }}" class="btn btn-sm btn-link">Reset</a>
    </div>
</form>

<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Daftar {{ $module === 'dokumen_mutu' ? 'Dokumen SPMI' : ucwords(str_replace('_', ' ', $module)) }}</h5>
        <a href="{{ route('admin.documents.create', ['module' => $module]) }}" class="btn btn-primary fw-semibold rounded-pill px-4">
            <i class="fa-solid fa-plus me-1"></i> Unggah Dokumen
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Kode Dokumen</th>
                        <th class="ps-4 py-3">Informasi Dokumen</th>
                <th class="py-3">Kategori</th>
                <th class="py-3">Status</th>
                        <th class="py-3">Akses</th>
                        <th class="py-3">Pengunggah</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            {{ $doc->code ?? '-' }}
                        </td>
                        <td class="ps-4 py-3">
                            <h6 class="fw-bold mb-1 text-dark">{{ $doc->title }}</h6>
                            <div class="text-muted small">
                                <span class="me-3 fw-semibold text-primary"><i class="fa-solid fa-tag me-1"></i> {{ $doc->owner_label }}</span>
                                <span><i class="fa-regular fa-calendar me-1"></i> {{ $doc->created_at->format('d/m/Y') }}</span>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $doc->category?->name ?? $doc->document_type }}</span>
                            @if($doc->academic_year)
                                <div class="small text-muted mt-1">{{ $doc->academic_year }} - {{ $doc->semester }}</div>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($doc->status == 'aktif')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill">Aktif</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill">Draft</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($doc->is_public)
                                <span class="badge bg-success bg-opacity-10 text-success border rounded-pill"><i class="fa-solid fa-globe me-1"></i> Publik</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border rounded-pill"><i class="fa-solid fa-lock me-1"></i> Internal</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="text-muted small"><i class="fa-regular fa-user me-1"></i> {{ $doc->uploader?->name ?? '-' }}</span>
                        </td>
                        <td class="py-3 text-center pe-4 admin-actions-cell">
                            <div class="admin-table-actions">
                                <div class="admin-table-action-group">
                                    <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-info icon-only-btn" title="Unduh"><i class="fa-solid fa-download"></i></a>
                                    @if(Auth::user()->hasAnyRole(['administrator','spmi']) || Auth::id() == $doc->uploaded_by)
                                    <a href="{{ route('admin.documents.edit', [$doc->id, 'module' => $module]) }}" class="btn btn-sm btn-outline-warning icon-only-btn" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <form action="{{ route('admin.documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted"><i class="fa-solid fa-folder-open fs-1 mb-3 opacity-25"></i><p class="mb-0">Belum ada dokumen yang diunggah.</p></div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($documents->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0">
        {{ $documents->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
