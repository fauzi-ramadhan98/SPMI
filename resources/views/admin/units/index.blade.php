@extends('layouts.admin')

@section('title', 'Manajemen Unit Kerja')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-building me-2 text-primary"></i>Daftar Unit Kerja</h5>
            <p class="text-muted small mb-0 mt-1">Unit administratif umum, layanan akademik, dan penunjang yang menjadi auditee selain program studi.</p>
        </div>
        <a href="{{ route('admin.units.create') }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tambah Unit
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Kode</th>
                        <th class="py-3">Nama Unit Kerja</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Kepala Unit</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3"><span class="badge bg-light text-dark border">{{ $unit->code }}</span></td>
                        <td class="py-3">
                            <h6 class="fw-bold mb-0 text-dark">{{ $unit->name }}</h6>
                            <div class="text-muted small">{{ $unit->users_count }} pengguna terdaftar</div>
                        </td>
                        <td class="py-3">
                            @if($unit->category == 'administratif_umum')
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">Administratif Umum</span>
                            @elseif($unit->category == 'layanan_akademik')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">Layanan Akademik</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">Penunjang</span>
                            @endif
                        </td>
                        <td class="py-3 text-muted small">{{ $unit->head_name ?? '-' }}</td>
                        <td class="py-3">
                            @if($unit->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;"></i> Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill"><i class="fa-solid fa-circle me-1" style="font-size:0.4rem;"></i> Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 text-center pe-4 admin-actions-cell"><div class="admin-table-actions">
                            <div class="admin-table-action-group">
                                <a href="{{ route('admin.units.edit', $unit->id) }}" class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Edit" aria-label="Edit"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit kerja ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" title="Hapus" aria-label="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-building-circle-xmark fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada unit kerja yang terdaftar.</p>
                                <a href="{{ route('admin.units.create') }}" class="btn btn-sm btn-outline-primary mt-3 rounded-pill px-4">Tambah Unit Pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($units->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $units->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
