@extends('layouts.admin')

@section('title', 'Detail Generate Laporan')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-file-pdf me-2"></i>Detail Laporan
            <span class="badge bg-info-subtle text-info ms-2">Generate Laporan</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.reports.generated.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-list me-1"></i>Daftar Laporan
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-chart-simple me-1"></i>Ringkasan
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- Error validasi (flash success/error sudah ditampilkan global oleh layout) --}}
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @if(session('info'))
            <div class="alert alert-info py-2"><i class="fa-solid fa-circle-info me-1"></i>{{ session('info') }}</div>
        @endif
        @if(session('warnings'))
            <div class="alert alert-warning">
                <div class="fw-bold small mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Lampiran dilewati saat generate:</div>
                <ul class="mb-0 small">
                    @foreach(session('warnings') as $w)<li>{{ $w }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Info laporan --}}
        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="border rounded p-3 flex-fill" style="min-width: 200px;">
                <div class="text-muted small">Siklus AMI</div>
                <div class="fw-bold">{{ $report->cycle?->name ?? '—' }}</div>
                <div class="text-muted" style="font-size: 11px;">{{ $report->cycle?->academic_year }} Sem. {{ $report->cycle?->semester }}</div>
            </div>
            <div class="border rounded p-3 flex-fill" style="min-width: 170px;">
                <div class="text-muted small">Jenis Laporan</div>
                <div class="fw-bold">{{ $report->jenis_label }}</div>
                <div class="text-muted" style="font-size: 11px;">{{ $report->level === 'prodi' ? ($report->program ? $report->program->degree_level . ' ' . $report->program->name : 'Per Prodi') : 'Rekapitulasi Institusi' }}</div>
            </div>
            <div class="border rounded p-3 flex-fill" style="min-width: 150px;">
                <div class="text-muted small">Jumlah Lampiran</div>
                <div class="fs-5 fw-bold">{{ $report->attachments->count() }}</div>
            </div>
            <div class="border rounded p-3 flex-fill" style="min-width: 180px;">
                <div class="text-muted small">Status</div>
                @if($report->status === 'generated' && $report->file_path)
                    <span class="badge bg-success">Sudah Digenerate</span>
                    <div class="text-muted" style="font-size: 11px;">{{ $report->generated_at?->format('d M Y H:i') }} • {{ $report->file_name }}</div>
                @else
                    <span class="badge bg-secondary">Belum Digenerate</span>
                @endif
            </div>
        </div>

        {{-- Daftar lampiran terurut --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-paperclip me-2 text-primary"></i>Urutan Lampiran (menjadi urutan halaman di PDF)</h6>
            <span class="text-muted small"><i class="fa-solid fa-arrow-up me-1"></i><i class="fa-solid fa-arrow-down me-1"></i>geser untuk mengubah urutan</span>
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle small mb-0">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th style="width: 46px;">No</th>
                        <th>Judul Lampiran</th>
                        <th style="width: 24%;">Sumber</th>
                        <th style="width: 12%;">Jenis</th>
                        <th style="width: 22%;">Berkas</th>
                        <th class="text-center" style="width: 170px;">Urutan &amp; Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report->attachments as $idx => $att)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="fw-semibold">{{ $att->title }}</td>
                            <td>{{ $att->source_label ?? '—' }}</td>
                            <td><span class="badge {{ $att->jenis === 'PDF' ? 'bg-danger' : ($att->jenis === 'Gambar' ? 'bg-info text-dark' : 'bg-secondary') }}">{{ $att->jenis }}</span></td>
                            <td class="text-muted">{{ $att->berkas_label }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <form action="{{ route('admin.reports.generated.attachments.sort', [$report, $att]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button class="btn btn-sm btn-outline-secondary icon-only-btn" title="Naik" aria-label="Naik" {{ $idx === 0 ? 'disabled' : '' }}><i class="fa-solid fa-arrow-up"></i></button>
                                    </form>
                                    <form action="{{ route('admin.reports.generated.attachments.sort', [$report, $att]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button class="btn btn-sm btn-outline-secondary icon-only-btn" title="Turun" aria-label="Turun" {{ $loop->last ? 'disabled' : '' }}><i class="fa-solid fa-arrow-down"></i></button>
                                    </form>
                                    <form action="{{ route('admin.reports.generated.attachments.destroy', [$report, $att]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus lampiran ini dari susunan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger icon-only-btn" title="Hapus" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada lampiran — tambahkan lewat form di bawah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tambah lampiran --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold small mb-2"><i class="fa-solid fa-upload me-1 text-primary"></i>Tambah Lampiran — Upload File</div>
                    <form action="{{ route('admin.reports.generated.attachments.store', $report) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Judul lampiran (mis. Daftar Hadir Rapat Tahunan)" required>
                        </div>
                        <div class="mb-2">
                            <input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.gif" required>
                            <div class="form-text">PDF digabung utuh (teks tetap dapat dicari); JPG/PNG dicetak sebagai halaman gambar. Maks 20 MB.</div>
                        </div>
                        <button class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>Tambahkan</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold small mb-2"><i class="fa-solid fa-box-archive me-1 text-primary"></i>Tambah Lampiran — Dari Data Existing</div>
                    @if($candidates->isEmpty())
                        <div class="text-muted small fst-italic mb-0">Semua file existing (SK, dokumen, bukti ED/temuan) sudah ada di susunan, atau belum ada file pada siklus ini.</div>
                    @else
                        <form action="{{ route('admin.reports.generated.attachments.store', $report) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <select name="source_key" class="form-select form-select-sm" required>
                                    <option value="" disabled selected>Pilih file...</option>
                                    @foreach($candidates as $c)
                                        <option value="{{ $c['disk'] . '|' . $c['file_path'] }}" data-title="{{ $c['title'] }}">
                                            [{{ $c['source_label'] }}] {{ $c['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <input type="text" name="title" id="pickTitle" class="form-control form-control-sm" placeholder="Judul lampiran" required>
                                <div class="form-text">Judul terisi otomatis saat memilih file (bisa disesuaikan).</div>
                            </div>
                            <button class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>Tambahkan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Generate --}}
        <div class="border rounded p-4 bg-light">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="fw-bold"><i class="fa-solid fa-file-pdf me-1 text-danger"></i>Generate PDF Laporan</div>
                    <div class="text-muted small mb-0">Badan laporan + lampiran menyusul sesuai urutan di atas. Hasil disimpan sebagai arsip &amp; bisa diunduh ulang kapan saja.</div>
                </div>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.reports.generated.generate', $report) }}" method="POST">
                        @csrf
                        <button class="btn btn-success fw-bold px-4 rounded-pill"><i class="fa-solid fa-gears me-1"></i>Generate PDF</button>
                    </form>
                    @if($report->file_path)
                        <a href="{{ route('admin.reports.generated.download', $report) }}" class="btn btn-danger fw-bold px-4 rounded-pill"><i class="fa-solid fa-download me-1"></i>Download PDF</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.querySelector('select[name="source_key"]');
    const title = document.getElementById('pickTitle');
    if (sel && title) {
        sel.addEventListener('change', function () {
            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.dataset.title) title.value = opt.dataset.title;
        });
    }
});
</script>
@endpush
