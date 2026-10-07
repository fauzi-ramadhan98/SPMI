{{-- Partial rekursif untuk render satu baris kategori + seluruh descendant-nya. Mendukung kedalaman tak terbatas. --}}
@php
    $depth = $depth ?? 0;
    $hasChildren = $category->children->count() > 0;
    $indent = 1 + $depth * 1.5; // rem
@endphp
<tr class="border-bottom {{ $depth > 0 ? 'category-child-row' : '' }}"
    data-parent-id="{{ $category->parent_id ?? '' }}"
    data-category-id="{{ $category->id }}"
    style="{{ $depth > 0 ? 'display:none;' : '' }}">
    <td class="py-{{ $depth > 0 ? '2' : '3' }}" style="padding-left: {{ $indent }}rem !important;">
        <div class="d-flex align-items-center">
            @if($hasChildren)
                <button type="button" class="btn btn-sm btn-link expand-btn p-0 me-2 text-primary"
                        data-target-category="{{ $category->id }}"
                        onclick="toggleCategoryChildren({{ $category->id }})" title="Tampilkan sub-kategori">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @else
                <span class="me-2 text-muted" style="width:18px; display:inline-block; text-align:center;">·</span>
            @endif
            <span class="badge bg-light text-dark border">{{ $category->code }}</span>
        </div>
    </td>
    <td class="py-{{ $depth > 0 ? '2' : '3' }}">
        @if($hasChildren)
            <button type="button" class="btn btn-link p-0 fw-bold text-dark text-decoration-none"
                    onclick="toggleCategoryChildren({{ $category->id }})" title="Klik untuk menampilkan sub-kategori">
                {{ $category->name }}
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 ms-1">{{ $category->children->count() }} sub</span>
            </button>
        @else
            <span>{{ $category->name }}</span>
        @endif
    </td>
    <td class="py-{{ $depth > 0 ? '2' : '3' }}">
        <span class="badge bg-{{ $depth === 0 ? 'info' : 'secondary' }} bg-opacity-10 text-{{ $depth === 0 ? 'info' : 'secondary' }} border rounded-pill">
            Level {{ $depth + 1 }}
        </span>
    </td>
    <td class="py-{{ $depth > 0 ? '2' : '3' }}">
        @if($category->is_active)
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;"></i> Aktif</span>
        @else
            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;"></i> Nonaktif</span>
        @endif
    </td>
    <td class="py-{{ $depth > 0 ? '2' : '3' }} text-center pe-4 admin-actions-cell">
        <div class="admin-table-actions">
            <div class="admin-table-action-group">
                <a href="{{ route('admin.document-categories.create', ['module' => $module, 'parent_id' => $category->id]) }}"
                   class="btn btn-sm btn-outline-success icon-only-btn admin-table-action" title="Tambah Sub-Kategori" aria-label="Tambah Sub-Kategori"><i aria-hidden="true" class="fa-solid fa-plus"></i></a>
                <a href="{{ route('admin.document-categories.edit', $category->id) }}"
                   class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Edit" aria-label="Edit"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                <form action="{{ route('admin.document-categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini? Kategori yang masih punya sub-kategori tidak bisa dihapus.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" title="Hapus" aria-label="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
    </td>
</tr>
@foreach($category->children as $child)
    @include('admin.document-categories._tree_row', ['category' => $child, 'depth' => $depth + 1, 'module' => $module])
@endforeach