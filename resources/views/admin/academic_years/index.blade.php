@extends('layouts.admin')

@section('title', 'Master Tahun Akademik')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Master Tahun Akademik</h5>
            <p class="text-muted small mb-0 mt-1">Daftar tahun akademik yang tersedia sebagai pilihan pada Risk Register.</p>
        </div>
        <a href="{{ route('admin.academic_years.create') }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tambah Tahun Akademik
        </a>
    </div>
    <div class="card-body p-0">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-4 mb-0 rounded-3 shadow-sm border-0" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Tahun Akademik</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($years as $year)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-bold fs-6">
                                {{ $year->name }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($year->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-center pe-4">
                            <div class="btn-group shadow-sm border rounded p-1 btn-group-sm">
                                <a href="{{ route('admin.academic_years.edit', $year->id) }}" class="btn btn-light text-warning border-0" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.academic_years.destroy', $year->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun akademik ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light text-danger border-0" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-calendar-days fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada tahun akademik yang terdaftar.</p>
                                <a href="{{ route('admin.academic_years.create') }}" class="btn btn-sm btn-outline-primary mt-3 rounded-pill px-4">Tambah Tahun Akademik Pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($years->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $years->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
