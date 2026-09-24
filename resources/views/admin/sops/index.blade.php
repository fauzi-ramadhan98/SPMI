@extends('layouts.admin')

@section('title', 'Manajemen SOP Unit')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-file-contract me-2"></i>Manajemen SOP Unit
        </h6>
        {{-- Tombol & badge dikelompokkan di satu sisi agar tombol tidak "melayang" di tengah header --}}
        <div class="d-flex align-items-center gap-2">
            @hasrole('unit')
            <a href="{{ route('admin.sops.create') }}" class="btn btn-sm btn-primary btn-custom">
                <i class="fa-solid fa-plus me-1"></i>Ajukan SOP Baru
            </a>
            @endhasrole
            <span class="badge bg-secondary">{{ $sops->total() }} SOP</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success m-3 alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger m-3 alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th>Judul SOP</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>File</th>
                        <th>Diajukan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sops as $sop)
                    <tr>
                        <td>
                            <strong>{{ $sop->title }}</strong>
                            @if($sop->description)
                                <br><small class="text-muted">{{ mb_strimwidth($sop->description, 0, 80, '…') }}</small>
                            @endif
                        </td>
                        <td>{{ $sop->unit?->name ?? '—' }}</td>
                        <td>{!! $sop->statusBadge() !!}</td>
                        <td class="text-nowrap">
                            @if($sop->file_path)
                                <small>{{ $sop->file_name }} ({{ number_format($sop->file_size / 1024, 1) }} KB)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            {{ $sop->created_at->format('d M Y H:i') }}
                            <br><small class="text-muted">{{ $sop->creator?->name }}</small>
                        </td>
                        <td class="text-center text-nowrap admin-actions-cell"><div class="admin-table-actions">
                            <a href="{{ route('admin.sops.show', $sop) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Detail" aria-label="Detail"><i aria-hidden="true" class="fa-solid fa-eye"></i></a>
                            @if($sop->file_path)
                            <a href="{{ route('admin.sops.download', $sop) }}" class="btn btn-sm btn-outline-info icon-only-btn admin-table-action" title="Unduh" aria-label="Unduh"><i aria-hidden="true" class="fa-solid fa-download"></i></a>
                            @endif
                            @hasrole('unit')
                            @if($sop->isRevisi())
                            <a href="{{ route('admin.sops.show', $sop) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Upload Revisi" aria-label="Upload Revisi"><i aria-hidden="true" class="fa-solid fa-upload"></i></a>
                            @endif
                            @endhasrole
                            @hasrole('spmi|administrator|super_admin')
                            @if($sop->isPending())
                            <a href="{{ route('admin.sops.review', $sop) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Review" aria-label="Review"><i aria-hidden="true" class="fa-solid fa-clipboard-check"></i></a>
                            @endif
                            @endhasrole
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada SOP.
                            @hasrole('unit')
                            <br><a href="{{ route('admin.sops.create') }}" class="btn btn-sm btn-primary mt-2">Ajukan SOP Pertama</a>
                            @endhasrole
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $sops->links() }}</div>
    </div>
</div>

@hasrole('unit')
<div class="alert alert-info mt-3">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Alur SOP Unit:</strong>
    1) Ajukan SOP baru → Status <strong>Menunggu Review</strong><br>
    2) SPMI review → <strong>Disetujui</strong> atau <strong>Revisi</strong><br>
    3) Jika <strong>Revisi</strong>: baca catatan, upload file perbaikan → Status kembali <strong>Menunggu Review</strong>
</div>
@endhasrole

@hasrole('spmi|administrator|super_admin')
<div class="alert alert-info mt-3">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Review SOP SPMI:</strong> Klik tombol <strong>Review</strong> pada SOP dengan status <strong>Menunggu Review</strong>.
    Anda dapat <strong>Menyetujui</strong> (status → Disetujui) atau <strong>Menolak/Revisi</strong> dengan catatan wajib.
</div>
@endhasrole
@endsection