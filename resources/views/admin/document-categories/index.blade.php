@extends('layouts.admin')

@section('title', 'Kategori Dokumen')

@section('content')
<style>
    .category-child-row { background-color: #f8f9fa; }
    .category-child-row td { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
    .expand-btn { transition: transform 0.2s; min-width: 24px; cursor: pointer; }
    .expand-btn.expanded { transform: rotate(90deg); }
</style>

<div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-tags me-2 text-primary"></i>Kategori Dokumen</h5>
            <p class="text-muted small mb-0 mt-1">Kelola kategori & sub-kategori dokumen (K-M-S-F). <strong>Klik nama kategori induk untuk membuka sub-kategori</strong>, lalu klik ikon pensil untuk mengeditnya.</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm w-auto" onchange="if(this.value) window.location.href='{{ route('admin.document-categories.index') }}?module='+this.value">
                @foreach($validModules as $mod)
                    <option value="{{ $mod }}" @selected($module === $mod)>{{ ucfirst(str_replace('_', ' ', $mod)) }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.document-categories.create', ['module' => $module]) }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Tambah Kategori
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Kode</th>
                        <th class="py-3">Nama Kategori</th>
                        <th class="py-3">Level</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parentCategories as $category)
                    @include('admin.document-categories._tree_row', ['category' => $category, 'depth' => 0, 'module' => $module])
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-tags fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada kategori dokumen untuk modul ini.</p>
                                <a href="{{ route('admin.document-categories.create', ['module' => $module]) }}" class="btn btn-sm btn-outline-primary mt-3 rounded-pill px-4">Tambah Kategori Pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($parentCategories->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $parentCategories->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<script>
// Sembunyikan seluruh descendant (anak, cucu, dst) dari suatu kategori secara rekursif,
// dan reset ikon chevron-nya. Dipakai saat kategori induk di-collapse.
function hideDescendants(categoryId) {
    const childRows = document.querySelectorAll('tr[data-parent-id="' + categoryId + '"]');
    childRows.forEach(function (row) {
        row.style.display = 'none';
        const childBtn = document.querySelector('.expand-btn[data-target-category="' + row.dataset.categoryId + '"]');
        if (childBtn) {
            childBtn.classList.remove('expanded');
        }
        hideDescendants(row.dataset.categoryId);
    });
}

// Toggle tampil/sembunyi anak langsung dari suatu kategori. Mendukung kedalaman tak terbatas
// karena setiap baris cukup tahu parent-nya sendiri lewat atribut data-parent-id.
function toggleCategoryChildren(categoryId) {
    const directRows = document.querySelectorAll('tr[data-parent-id="' + categoryId + '"]');
    if (!directRows.length) return;

    const isExpanding = directRows[0].style.display === 'none' || directRows[0].style.display === '';

    directRows.forEach(function (row) {
        row.style.display = isExpanding ? 'table-row' : 'none';
        if (!isExpanding) {
            hideDescendants(row.dataset.categoryId);
        }
    });

    const btn = document.querySelector('.expand-btn[data-target-category="' + categoryId + '"]');
    if (btn) {
        btn.classList.toggle('expanded', isExpanding);
    }
}
</script>
@endsection