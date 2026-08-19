@extends('layouts.admin')

@section('title', 'SK Penetapan Standar')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-stamp me-2"></i>SK Penetapan Standar Mutu
        </h6>
        <div class="d-flex align-items-center gap-2">
            @hasrole('spmi')
            <a href="{{ route('admin.standard-decrees.create') }}" class="btn btn-sm btn-primary btn-custom">
                <i class="fa-solid fa-plus me-1"></i>Buat SK
            </a>
            @endhasrole
            <span class="badge bg-secondary">{{ $decrees->total() }} SK</span>
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
                        <th>Nomor SK</th>
                        <th>Judul</th>
                        <th>Standar</th>
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
                        <td>
                            <span class="badge bg-primary-subtle text-primary">{{ $d->standards_count }} standar</span>
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
                            @if($d->isDitetapkan())
                            @else
@hasrole('pimpinan')
                                <form action="{{ route('admin.standard-decrees.verify', $d) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Tetapkan SK ini? Status akan menjadi Ditetapkan dan ditandatangani secara otomatis.')">
                                    @csrf
                                    <button class="btn btn-sm btn-success btn-custom"><i class="fa-solid fa-check me-1"></i>Tetapkan</button>
                                </form>
                                @endhasrole
                                @hasrole('spmi')
                                <a href="{{ route('admin.standard-decrees.edit', $d) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-pen me-1"></i>Edit
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#uploadModal-{{ $d->id }}">
                                    <i class="fa-solid fa-upload me-1"></i>Upload SK
                                </button>
                                <form action="{{ route('admin.standard-decrees.destroy', $d) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus draf SK ini? Standar yang dipilih tidak ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                                @endhasrole
                            @endif
                        </td>
                    </tr>

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
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada SK Penetapan. @hasrole('spmi') Klik "Buat SK" untuk mulai. @endhasrole</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $decrees->links() }}</div>
    </div>
</div>

@hasrole('pimpinan')
{{-- Catatan alur untuk pimpinan --}}
<div class="alert alert-info mt-3">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Modul Penetapan (P1)</strong>: ini adalah daftar SK Penetapan Standar Mutu yang disiapkan SPMI.
    Tombol <strong>Tetapkan</strong> hanya aktif bila <strong>Standar</strong> dan <strong>Siklus/Auditor</strong> sudah
    terisi; klik tombol tersebut untuk langsung menandatangani (TTD otomatis) dan menetapkan SK. Standar yang tercantum
    akan dianggap ditetapkan begitu SK berstatus <em>Ditetapkan</em>. File SK resmi diunggah oleh SPMI setelah ditetapkan.
</div>
@endhasrole
@endsection