@extends('layouts.admin')

@section('title', 'Evaluasi Diri (ED)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Evaluasi Diri (ED)</h1>
        <p class="text-muted mb-0">Penilaian kepatuhan standar + bukti oleh Prodi/Unit; ditinjau Auditor & SPMI.</p>
    </div>
    @if ($canCreate && !auth()->user()->hasRole('spmi'))
    <a href="{{ route('admin.evaluations.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Buat Evaluasi Diri
    </a>
    @else
    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2">
        <i class="fa-solid fa-eye me-1"></i> Monitoring (Read-only) &mdash; SPMI
    </span>
    @endif
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Target</th>
                    <th>Jenis</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($evaluations as $evaluation)
                    <tr>
                        <td>{{ $evaluation->name }}</td>
                        <td>{{ $evaluation->owner_label }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $evaluation->owner_type_label }}</span></td>
                        <td>{{ $evaluation->academic_year }} {{ $evaluation->semester }}</td>
                        <td>
                            <span class="badge {{ $evaluation->status == 'verified' ? 'bg-success' : ($evaluation->status === 'submitted' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $evaluation->status === 'draft' ? 'Draft' : ucfirst($evaluation->status) }}
                            </span>
                        </td>
                        <td class="text-center admin-actions-cell"><div class="admin-table-actions">
                            <a href="{{ route('admin.evaluations.show', $evaluation) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Lihat" aria-label="Lihat"><i aria-hidden="true" class="fa-solid fa-eye"></i></a>
                            {{-- Prodi/Unit pemilik atau administrator bisa isi/kelola (SPMI monitoring read-only) --}}
                            @if (!auth()->user()->hasRole('spmi') && (auth()->user()->hasAnyRole(['administrator'])
                                || ($evaluation->evaluable_type === App\Models\AcademicProgram::class && auth()->user()->hasRole('prodi') && $evaluation->evaluable_id === auth()->user()->academic_program_id)
                                || ($evaluation->evaluable_type === App\Models\Unit::class && auth()->user()->hasRole('unit') && $evaluation->evaluable_id === auth()->user()->unit_id)))
                            <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Isi / Kelola" aria-label="Isi / Kelola"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                            @endif
                            @if (auth()->user()->hasAnyRole(['administrator'])
    || (($evaluation->evaluable_type === App\Models\AcademicProgram::class && auth()->user()->hasRole('prodi') && $evaluation->evaluable_id === auth()->user()->academic_program_id
        || $evaluation->evaluable_type === App\Models\Unit::class && auth()->user()->hasRole('unit') && $evaluation->evaluable_id === auth()->user()->unit_id) && $evaluation->status === 'draft'))
                            <form action="{{ route('admin.evaluations.destroy', $evaluation) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus evaluasi ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" title="Hapus" aria-label="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                            </form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada evaluasi diri.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">{{ $evaluations->links() }}</div>
@endsection