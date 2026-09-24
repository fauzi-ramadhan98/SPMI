@extends('layouts.admin')

@section('title', 'Peninjauan & Revisi Standar')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-clock-rotate-left me-2"></i>Peninjauan &amp; Revisi Standar
            <span class="badge bg-warning-subtle text-warning ms-2">P5.1</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary">{{ $stats['total'] }} standar</span>
            <a href="{{ route('admin.quality-standards.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-list-check me-1"></i>Daftar Standar (P1)
            </a>
            <a href="{{ route('admin.standard-decrees.perubahan') }}" class="btn btn-sm btn-primary btn-custom">
                <i class="fa-solid fa-arrows-rotate me-1"></i>SK Perubahan (P5.2)
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="border rounded p-3 flex-fill" style="min-width: 180px;">
                <div class="text-muted small">Total Standar</div>
                <div class="fs-5 fw-bold">{{ $stats['total'] }}</div>
            </div>
            <div class="border rounded p-3 flex-fill" style="min-width: 180px;">
                <div class="text-muted small">Revisi Menunggu SK</div>
                <div class="fs-5 fw-bold {{ $stats['draft'] > 0 ? 'text-warning' : '' }}">{{ $stats['draft'] }}</div>
            </div>
            <div class="border rounded p-3 flex-fill" style="min-width: 180px;">
                <div class="text-muted small">Snapshot Riwayat</div>
                <div class="fs-5 fw-bold">{{ $stats['versi'] }}</div>
            </div>
            <div class="border rounded p-3 flex-fill d-flex align-items-center" style="min-width: 260px;">
                <div class="small text-muted">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Klik <strong>Revisi</strong> pada baris standar untuk mengubah isi (versi naik).
                    Status menjadi <span class="badge bg-warning-subtle text-warning">Revisi Draft</span>
                    sampai SK Perubahan ditandatangani Pimpinan.
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('admin.revisi-standar.index') }}"
               class="btn btn-sm {{ request()->query('status') !== 'draft_revisi' ? 'btn-primary btn-custom' : 'btn-outline-primary' }}">Semua</a>
            <a href="{{ route('admin.revisi-standar.index', ['status' => 'draft_revisi']) }}"
               class="btn btn-sm {{ request()->query('status') === 'draft_revisi' ? 'btn-primary btn-custom' : 'btn-outline-primary' }}">
                <i class="fa-solid fa-pen me-1"></i>Hanya Revisi Draft ({{ $stats['draft'] }})
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th>Kode</th>
                        <th>Standar</th>
                        <th class="text-center">Versi</th>
                        <th>Status Revisi</th>
                        <th>Riwayat</th>
                        <th>SK Terkait</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $std)
                    <tr>
                        <td class="text-nowrap fw-semibold">{{ $std->kode_standar }}</td>
                        <td>
                            <div class="fw-semibold">{{ $std->pernyataan_standar }}</div>
                            @if($std->document)
                            <small class="text-muted d-block">{{ $std->document->code }} — {{ $std->document->title }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-dark rounded-pill px-3">v{{ $std->version }}</span>
                        </td>
                        <td class="text-nowrap">
                            @if($std->revisi_status === 'draft_revisi')
                                <span class="badge bg-warning-subtle text-warning"><i class="fa-solid fa-pen me-1"></i>Revisi Draft</span>
                                <small class="text-warning d-block">Menunggu SK Perubahan</small>
                            @else
                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i>Aktif</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-secondary icon-only-btn admin-table-action"
                                    data-bs-toggle="modal" data-bs-target="#historyModal-{{ $std->id }}" title="Riwayat Versi">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </button>
                            <span class="text-muted small ms-1">{{ $std->versions->count() }} snapshot</span>
                        </td>
                        <td>
                            @if($std->decree)
                                <a href="{{ route('admin.standard-decrees.pdf', $std->decree) }}" target="_blank" class="text-decoration-none">
                                    {{ $std->decree->sk_no }}
                                    @if($std->decree->isPerubahan())<span class="badge bg-warning-subtle text-warning ms-1">Perubahan</span>@endif
                                </a>
                            @else
                                <span class="text-muted">Belum ada SK</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap admin-actions-cell">
                            <div class="admin-table-actions">
                                @hasrole('spmi')
                                <a href="{{ route('admin.quality-standards.edit', [$std->id, 'dari' => 'revisi']) }}"
                                   class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Revisi Standar" aria-label="Revisi">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endhasrole
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Riwayat Versi --}}
                    <div class="modal fade" id="historyModal-{{ $std->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat — {{ $std->kode_standar }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted">Versi berjalan: <strong>v{{ $std->version }}</strong> — status {{ $std->revisi_status === 'draft_revisi' ? 'Revisi Draft (menunggu SK Perubahan)' : 'Aktif' }}.</p>
                                    @if($std->versions->isEmpty())
                                    <p class="text-muted mb-0">Belum ada snapshot. Snapshot dibuat setiap kali standar direvisi.</p>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle small mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Versi</th>
                                                    <th>Isi Lama</th>
                                                    <th>Diubah Oleh</th>
                                                    <th>Waktu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($std->versions as $ver)
                                                <tr>
                                                    <td class="text-nowrap"><span class="badge bg-dark rounded-pill">v{{ $ver->version }}</span></td>
                                                    <td style="max-width: 320px;">
                                                        <div class="fw-semibold">{{ $ver->snapshot['pernyataan_standar'] ?? $ver->snapshot['name'] ?? '-' }}</div>
                                                        <small class="text-muted">{{ $ver->snapshot['kode_standar'] ?? '-' }}</small>
                                                    </td>
                                                    <td>{{ $ver->changer?->name ?? '—' }}</td>
                                                    <td class="text-nowrap">{{ $ver->created_at?->format('d M Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada standar mutu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $standards->links() }}</div>
    </div>
</div>

@if($stats['draft'] > 0)
<div class="alert alert-warning mt-3">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>{{ $stats['draft'] }} standar</strong> berstatus <em>Revisi Draft</em>.
    Setelah isinya benar, buat <strong>SK Perubahan</strong> di menu
    <a href="{{ route('admin.standard-decrees.create', ['kategori' => 'perubahan']) }}" class="alert-link">SK Perubahan Standar (P5.2)</a>
    lalu minta Pimpinan menandatangani agar status kembali <em>Aktif</em>.
</div>
@endif
@endsection
