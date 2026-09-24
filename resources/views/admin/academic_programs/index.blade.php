@extends('layouts.admin')

@section('title', 'Manajemen Program Studi')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-building-columns me-2 text-primary"></i>Daftar Program Studi</h5>
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
                        <th class="ps-4 py-3">Kode</th>
                        <th class="py-3">Program Studi</th>
                        <th class="py-3">Jenjang</th>
                        <th class="py-3">Ketua Program Studi</th>
                        <th class="py-3">Akreditasi</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                {{ $program->code }}
                            </span>
                        </td>
                        <td class="py-3">
                            <h6 class="fw-bold mb-0 text-dark">{{ $program->name }}</h6>
                            <small class="text-muted">{{ $program->faculty }}</small>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                {{ $program->degree_level }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($program->head_name)
                                <i class="fa-solid fa-user-tie me-1 text-muted"></i>
                                {{ $program->head_name }}
                            @else
                                <span class="text-muted fst-italic small">Belum diisi</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($program->accreditation)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                    {{ $program->accreditation }}
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($program->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3 text-center pe-4 admin-actions-cell"><div class="admin-table-actions">
                            <a href="{{ route('admin.academic_programs.edit', $program->id) }}"
                               class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Edit Program Studi" aria-label="Edit Program Studi"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-building-columns fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada data program studi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
