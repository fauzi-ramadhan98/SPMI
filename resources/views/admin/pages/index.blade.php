@extends('layouts.admin')

@section('title', 'Manajemen Halaman Publik')

@section('content')
<div class="card card-custom shadow-sm border-0">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Daftar Halaman Statis</h5>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tambah Halaman
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Judul Halaman</th>
                        <th class="py-3">URL Slug</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <h6 class="fw-bold mb-0 text-dark">{{ $page->title }}</h6>
                            @if($page->author)
                                <small class="text-muted"><i class="fa-regular fa-user me-1"></i> {{ $page->author->name }}</small>
                            @endif
                        </td>
                        <td class="py-3">
                            <a href="{{ route('profil') }}" class="text-primary small"><code>/{{ $page->slug }}</code></a>
                        </td>
                        <td class="py-3 text-center">
                            @if($page->is_published)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">Published</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">Draft</span>
                            @endif
                        </td>
                        <td class="py-3 text-center pe-4 admin-actions-cell"><div class="admin-table-actions">
                            <div class="admin-table-action-group">
                                <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Edit" aria-label="Edit"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus halaman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" aria-label="Hapus" title="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Belum ada halaman statis yang dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
