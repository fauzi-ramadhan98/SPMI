@extends('layouts.admin')

@php
    $isPerubahan = ($kategoriMode ?? 'penetapan') === 'perubahan';
@endphp

@section('title', $isPerubahan ? 'SK Perubahan Standar' : 'SK Penetapan Standar')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid {{ $isPerubahan ? 'fa-arrows-rotate' : 'fa-stamp' }} me-2"></i>{{ $isPerubahan ? 'SK Perubahan Standar Mutu' : 'SK Penetapan Standar Mutu' }}
            @if($isPerubahan)
            <span class="badge bg-warning-subtle text-warning ms-2">P5.2</span>
            @endif
        </h6>
        <div class="d-flex align-items-center gap-2">
            @hasrole('spmi')
            <!--<a href="{{ $isPerubahan ? route('admin.revisi-standar.index') : route('admin.standard-decrees.create') }}" class="btn btn-sm btn-outline-secondary">-->
            <!--    <i class="fa-solid {{ $isPerubahan ? 'fa-clock-rotate-left' : 'fa-list-check' }} me-1"></i>{{ $isPerubahan ? 'Lihat Standar Revisi' : 'Daftar Standar' }}-->
            <!--</a>-->
            <a href="{{ route('admin.standard-decrees.create', $isPerubahan ? ['kategori' => 'perubahan'] : []) }}" class="btn btn-sm btn-primary btn-custom">
                <i class="fa-solid fa-plus me-1"></i>{{ $isPerubahan ? 'Buat SK Perubahan' : 'Buat SK' }}
            </a>
            @endhasrole
            <span class="badge bg-secondary">{{ $decrees->total() }} SK</span>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th>Nomor SK</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Dokumen</th>
                        <th>Siklus / Auditor</th>
                        <th>Status</th>
                        <th>Ditetapkan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($decrees as $d)
                    <tr>
                        <td class="text-nowrap fw-semibold">{{ $d->sk_no }}</td>
                        <td>{{ $d->judul }}</td>
                        <td class="text-nowrap">
                            @if($d->isPerubahan())
                                <span class="badge bg-warning-subtle text-warning" title="Hasil revisi standar (P5)"><i class="fa-solid fa-arrows-rotate me-1"></i>Perubahan</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary">Penetapan</span>
                            @endif
                        </td>
                        <td>
                            @if($d->documents_count > 0)
                                <span class="badge bg-info-subtle text-info">{{ $d->documents_count }} dokumen</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($d->cycle)
                                {{ $d->cycle->name }} <small class="text-muted d-block">{{ $d->cycle->academic_year }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($d->isDitetapkan())
                                <span class="badge bg-success-subtle text-success">Ditetapkan</span>
                            @elseif($d->status === 'menunggu_persetujuan')
                                <span class="badge bg-warning-subtle text-warning">Menunggu Persetujuan</span>
                            @elseif($d->isDitolak())
                                <span class="badge bg-danger-subtle text-danger" data-bs-toggle="tooltip" title="{{ $d->reject_reason }}">
                                    <i class="fa-solid fa-times-circle me-1"></i>Ditolak
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Draf</span>
                            @endif
                            @if($d->isDitolak() && $d->reject_reason)
                                <small class="text-danger d-block mt-1" style="max-width:200px;" title="{{ $d->reject_reason }}">
                                    <i class="fa-solid fa-comment-dots me-1"></i>{{ Str::limit($d->reject_reason, 50) }}
                                </small>
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
                        <td class="text-center text-nowrap admin-actions-cell"><div class="admin-table-actions">
                            <a href="{{ route('admin.standard-decrees.pdf', $d) }}" target="_blank" class="btn btn-sm btn-outline-info icon-only-btn admin-table-action" title="PDF" aria-label="PDF"><i aria-hidden="true" class="fa-solid fa-file-pdf"></i></a>
                            @if($d->file_path)
                            <a href="{{ route('admin.standard-decrees.download-file', $d) }}" class="btn btn-sm btn-outline-info icon-only-btn admin-table-action" title="SK" aria-label="SK"><i aria-hidden="true" class="fa-solid fa-download"></i></a>
                            @endif
                            @if($d->isDitetapkan())
                            @else
@hasrole('pimpinan')
                                {{-- <a href="{{ route('admin.standard-decrees.review', $d) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Koreksi" aria-label="Koreksi"><i aria-hidden="true" class="fa-solid fa-pen-nib"></i></a> --}}
                                <form action="{{ route('admin.standard-decrees.verify', $d) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Tetapkan SK ini? Status akan menjadi Ditetapkan dan ditandatangani secara otomatis.')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success icon-only-btn admin-table-action btn-custom" title="Tetapkan" aria-label="Tetapkan"><i aria-hidden="true" class="fa-solid fa-check"></i></button>
                                </form>
                                @if($d->status === 'menunggu_persetujuan')
                                <button type="button" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $d->id }}" title="Tolak/Dikembalikan" aria-label="Tolak"><i aria-hidden="true" class="fa-solid fa-times"></i></button>
                                @endif
                                @endhasrole
                                @hasrole('spmi')
                                <a href="{{ route('admin.standard-decrees.edit', $d) }}" class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Edit" aria-label="Edit"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" data-bs-toggle="modal" data-bs-target="#uploadModal-{{ $d->id }}" title="Upload SK" aria-label="Upload SK"><i aria-hidden="true" class="fa-solid fa-upload"></i></button>
                                <form action="{{ route('admin.standard-decrees.destroy', $d) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus draf SK ini? Standar yang dipilih tidak ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" aria-label="Hapus" title="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                                </form>
                                @endhasrole
                            @endif
                        </div></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">{{ $isPerubahan ? 'Belum ada SK Perubahan. Revisi standar dulu lewat menu "Peninjauan & Revisi Standar" (P5.1), lalu buat SK Perubahan di sini.' : 'Belum ada SK Penetapan. Klik "Buat SK" untuk mulai.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $decrees->links() }}</div>
    </div>
</div>

{{-- Modal Upload & Tolak SK — diletakkan di luar tabel agar HTML valid dan baris tidak "kabur" keluar card --}}
@foreach($decrees as $d)
                    <div class="modal fade" id="uploadModal-{{ $d->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.standard-decrees.upload-file', $d) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Upload File SK</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small mb-0"><strong>{{ $d->sk_no }}</strong> — {{ $d->judul }}</p>
                                        @if($d->file_path)
                                        <p class="small text-muted mb-2">
                                            File saat ini: <a href="{{ route('admin.standard-decrees.download-file', $d) }}">{{ $d->file_name }}</a>
                                        </p>
                                        @else
                                        <p class="small text-muted mb-2">Belum ada file SK diunggah.</p>
                                        @endif
                                        <div class="mb-3">
                                            <label class="form-label">File SK (PDF / Word / Gambar)</label>
                                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-primary btn-custom"><i class="fa-solid fa-upload me-1"></i>Upload</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($d->status === 'menunggu_persetujuan')
                    @hasrole('pimpinan')
                    <div class="modal fade" id="rejectModal-{{ $d->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.standard-decrees.reject', $d) }}" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="fa-solid fa-times-circle me-1"></i> Tolak SK</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-2">Anda akan menolak SK berikut dan mengembalikannya ke SPMI untuk diperbaiki:</p>
                                        <p class="fw-bold mb-3">{{ $d->sk_no }} — {{ $d->judul }}</p>
                                        <div class="mb-3">
                                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                            <textarea name="reject_reason" class="form-control" rows="4" required maxlength="1000"
                                                      placeholder="Tuliskan alasan penolakan, misalnya: data dokumen belum lengkap, judul SK perlu perubahan, dll."></textarea>
                                            <div class="form-text">Wajib diisi. Catatan ini akan terlihat oleh SPMI.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-danger" type="submit"><i class="fa-solid fa-times me-1"></i>Tolak & Kembalikan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endhasrole
                    @endif
@endforeach

@hasrole('pimpinan')
{{-- Catatan alur untuk pimpinan --}}
<div class="alert alert-info mt-3">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>{{ $isPerubahan ? 'Modul Peningkatan Standar (P5.2)' : 'Modul Penetapan (P1)' }}</strong>: {{ $isPerubahan ? 'ini adalah daftar SK Perubahan Standar dari hasil revisi standar yang disiapkan SPMI.' : 'ini adalah daftar SK Penetapan Standar Mutu yang disiapkan SPMI.' }}
    SK dengan status <strong>Menunggu Persetujuan</strong> siap ditinjau. Klik tombol <strong>Koreksi</strong> untuk mengedit isi SK, <strong>Tetapkan</strong> untuk menandatangani (TTD otomatis) dan menetapkan SK, atau <strong>Tolak</strong> untuk mengembalikan ke SPMI dengan catatan perbaikan.
    {{ $isPerubahan ? 'Begitu SK Perubahan ditetapkan, status revisi standar terkait kembali <em>Aktif</em>.' : 'Dokumen Mutu yang tercantum akan otomatis berstatus <em>Aktif</em> begitu SK berstatus <em>Ditetapkan</em>.' }}
    File SK resmi diunggah oleh SPMI setelah ditetapkan.
</div>
@endhasrole
@endsection