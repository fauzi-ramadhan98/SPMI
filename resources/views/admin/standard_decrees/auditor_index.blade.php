@extends('layouts.admin')

@section('title', 'SK Penugasan Auditor')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-clipboard-user me-2"></i>SK Penugasan Auditor
        </h6>
        <span class="badge bg-secondary">{{ $decrees->total() }} SK</span>
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
                        <th>Nomor SK</th>
                        <th>Judul</th>
                        <th>Siklus / Auditor</th>
                        <th>Status</th>
                        <th>Ditetapkan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($decrees as $d)
                    <tr>
                        <td class="text-nowrap fw-semibold">{{ $d->sk_no }}</td>
                        <td>{{ $d->judul }}</td>
                        <td class="text-nowrap">
                            @if($d->cycle)
                                {{ $d->cycle->name }} <small class="text-muted d-block">{{ $d->cycle->academic_year }}</small>
                                <small class="text-muted d-block">{{ count($d->auditors()) }} auditor</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($d->isDitetapkan())
                                <span class="badge bg-success-subtle text-success">Ditetapkan</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">Draf</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($d->isDitetapkan())
                                {{ $d->issued_at?->format('d M Y') }}
                                <small class="text-muted d-block">{{ $d->issuedBy?->name }}</small>
                            @else
                                <span class="text-muted">Belum</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.standard-decrees.pdf', $d) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-file-pdf me-1"></i>PDF
                            </a>
                            @if($d->file_path)
                            <a href="{{ route('admin.standard-decrees.download-file', $d) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa-solid fa-download"></i> SK
                            </a>
                            @endif
                            @if(!$d->isDitetapkan())
                                @hasrole('pimpinan')
                                <form action="{{ route('admin.standard-decrees.verify', $d) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Tetapkan SK ini? Status akan menjadi Ditetapkan dan ditandatangani secara otomatis.')">
                                    @csrf
                                    <button class="btn btn-sm btn-success btn-custom"><i class="fa-solid fa-check me-1"></i>Tetapkan</button>
                                </form>
                                @endhasrole
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada SK Penugasan Auditor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $decrees->links() }}</div>
    </div>
</div>

@hasrole('pimpinan')
<div class="alert alert-info mt-3">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Modul Pengendalian (P3)</strong>: daftar SK Penugasan Auditor yang disiapkan SPMI untuk siklus AMI.
    Setelah siklus dan daftar auditor terisi, klik <strong>Tetapkan</strong> untuk menandatangani (TTD otomatis)
    dan menetapkan SK. SK Auditor dipisah dari SK Penetapan Standar (P1).
</div>
@endhasrole
@endsection
