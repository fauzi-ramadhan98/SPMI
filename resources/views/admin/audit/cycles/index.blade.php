@extends('layouts.admin')

@section('title', 'Siklus Audit Mutu Internal (AMI)')

@section('content')
<div class="card card-custom shadow-sm mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-0">
        <div>
            <h5 class="mb-0 fw-bold">Daftar Siklus AMI</h5>
            <p class="text-muted small mb-0 mt-1">Kelola dan pantau periode Audit Mutu Internal.</p>
        </div>
        @hasrole('spmi')
        <a href="{{ route('admin.audit.cycles.create') }}" class="btn btn-success fw-semibold rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> Buat Siklus Baru
        </a>
        @endhasrole
    </div>
    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Nama Siklus</th>
                        <th class="py-3 border-0">Tahun Akademik</th>
                        <th class="py-3 border-0">Periode Pelaksanaan</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="py-3 border-0 text-center">Jml Auditee</th>
                        <th class="py-3 border-0 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cycles as $cycle)
                    <tr class="border-bottom">
                        <td class="ps-4 py-4">
                            <h6 class="fw-bold mb-1 text-dark">{{ $cycle->name }}</h6>
                            <div class="text-muted small lh-sm">{{ Str::limit($cycle->description, 50) }}</div>
                        </td>
                        <td class="py-4">
                            <div class="fw-bold text-dark">{{ $cycle->academic_year }}</div>
                            <div class="text-muted small">Semester {{ $cycle->semester }}</div>
                        </td>
                        <td class="py-4">
                            <div class="d-flex align-items-center text-muted small">
                                <i class="fa-regular fa-calendar-plus me-2 text-success"></i> {{ $cycle->start_date->format('d M Y') }}
                            </div>
                            <div class="d-flex align-items-center text-muted small mt-1">
                                <i class="fa-regular fa-calendar-check me-2 text-danger"></i> {{ $cycle->end_date->format('d M Y') }}
                            </div>
                        </td>
                        <td class="py-4 text-center">
                            @if($cycle->status == 'aktif')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-spinner fa-spin-pulse me-1"></i> Aktif</span>
                            @elseif($cycle->status == 'selesai')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-check-double me-1"></i> Selesai</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-file-pen me-1"></i> Draft</span>
                            @endif
                        </td>
                        <td class="py-4 text-center">
                            <span class="badge bg-light text-dark border fs-6 rounded px-3 py-2">{{ $cycle->assignments_count }}</span>
                        </td>
                        <td class="py-4 text-center pe-4">
                            <div class="btn-group shadow-sm border rounded p-1 btn-group-sm">
                                <a href="{{ route('admin.audit.assignments.index', ['cycle_id' => $cycle->id]) }}" class="btn btn-light text-info border-0" title="Kelola Alokasi">
                                    <i class="fa-solid fa-users-gear"></i>
                                </a>
                                @hasrole('spmi')
                                @if($cycle->status == 'draft')
                                <a href="{{ route('admin.audit.cycles.edit', $cycle->id) }}" class="btn btn-light text-warning border-0" title="Edit Siklus">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.audit.cycles.destroy', $cycle->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus siklus AMI ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light text-danger border-0" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <span class="btn btn-light text-muted border-0" title="Dikunci (riwayat)">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                @endif
                                @endhasrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-rotate fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada siklus AMI yang dibuat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cycles->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $cycles->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
