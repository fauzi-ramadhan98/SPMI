@extends('layouts.admin')

@section('title', 'Instrumen Borang Audit: ' . $assignment->auditee_label)

@section('content')
<div class="card card-custom shadow-sm mb-4 border-0 border-top border-4 border-primary">
    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-sm btn-light rounded-circle me-3"><i class="fa-solid fa-arrow-left"></i></a>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Lembar Kerja Profiling & Borang Audit Mutu</h5>
                <p class="text-muted small mb-0">Auditee: <strong>{{ $assignment->auditee_label }}</strong> ({{ $assignment->auditee_type_label }}) | Siklus: <strong>{{ $assignment->cycle->name }}</strong></p>
            </div>
        </div>
    </div>
    
    <div class="card-body p-4 pt-3">

        {{-- ===== PANEL RISK REGISTER ===== --}}
        <div class="mb-5">
            <h6 class="fw-bold mb-3 d-flex align-items-center">
                <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>
                Profil Risiko Prodi Ini (Prioritas Tinggi)
                <span class="ms-2 badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">
                    {{ $risks->count() }} Entri Risiko Tinggi
                </span>
                @hasrole('prodi|unit')
                <a href="{{ route('admin.risk-registers.create') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-auto fw-semibold">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Risiko
                </a>
                @endhasrole
            </h6>

            @if($risks->count() > 0)
            <div class="table-responsive border rounded-3 shadow-sm">
                <table class="table table-hover table-bordered align-top mb-0 small">
                    <thead class="table-dark text-center text-uppercase" style="font-size: 0.7rem;">
                        <tr>
                            <th class="py-2 px-2" style="min-width: 40px;">No</th>
                            <th class="py-2" style="min-width: 120px;">Standar</th>
                            <th class="py-2" style="min-width: 180px;">Indikator</th>
                            <th class="py-2" style="min-width: 150px;">Kondisi Saat Ini</th>
                            <th class="py-2" style="min-width: 150px;">Akar Masalah</th>
                            <th class="py-2" style="min-width: 150px;">Risiko</th>
                            <th class="py-2" style="min-width: 150px;">Mitigasi</th>
                            <th class="py-2 text-center" style="min-width: 190px;">Bukti Dokumen (GDrive)</th>
                            <th class="py-2 text-center" style="min-width: 60px;">Imp.</th>
                            <th class="py-2 text-center" style="min-width: 60px;">Like.</th>
                            <th class="py-2 text-center" style="min-width: 60px;">Skor</th>
                            <th class="py-2 text-center" style="min-width: 80px;">Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($risks as $i => $risk)
                        <tr class="border-bottom">
                            <td class="px-2 py-2 text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                            <td class="px-2 py-2">
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-2 px-2 d-block text-wrap text-start">
                                    {{ $risk->standar_mutu ?? '—' }}
                                </span>
                                <div class="text-muted mt-1" style="font-size: 0.68rem;">{{ $risk->academic_year }}</div>
                            </td>
                            <td class="px-2 py-2" style="white-space: pre-wrap; max-width: 180px;">
                                <span class="text-dark">{{ $risk->butir_tilik ?? '—' }}</span>
                            </td>
                            <td class="px-2 py-2" style="white-space: pre-wrap; max-width: 160px;">
                                <span class="text-muted">{{ $risk->temuan ?? '—' }}</span>
                            </td>
                            <td class="px-2 py-2" style="white-space: pre-wrap; max-width: 160px;">
                                <span class="text-muted">{{ $risk->akar_masalah ?? '—' }}</span>
                            </td>
                            <td class="px-2 py-2" style="white-space: pre-wrap; max-width: 160px;">
                                <span class="fw-semibold text-dark">{{ $risk->risk_description }}</span>
                            </td>
                            <td class="px-2 py-2" style="white-space: pre-wrap; max-width: 160px;">
                                <span class="text-muted">{{ $risk->mitigation_plan ?? '—' }}</span>
                            </td>
                            <td class="px-2 py-2">
                                @if($risk->document_link)
                                    <a href="{{ $risk->document_link }}" target="_blank" class="d-inline-flex align-items-center gap-1 text-primary fw-semibold text-break"
                                        style="word-break: break-word;" title="Buka Bukti Dokumen (Google Drive)">
                                        <i class="fa-brands fa-google-drive me-1"></i>
                                        {{ $risk->document_link }}
                                    </a>
                                @else
                                    <div class="text-center"><span class="text-muted small">—</span></div>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded fs-6">{{ $risk->impact }}</span>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded fs-6">{{ $risk->probability }}</span>
                            </td>
                            <td class="px-2 py-2 text-center">
                                <span class="badge fw-bold rounded px-2 py-1
                                    @if($risk->risk_level == 'High') bg-danger
                                    @elseif($risk->risk_level == 'Medium') bg-warning text-dark
                                    @else bg-success @endif">
                                    {{ $risk->risk_score }}
                                </span>
                            </td>
                            <td class="px-2 py-2 text-center">
                                @if($risk->risk_level == 'High')
                                    <span class="badge bg-danger rounded-pill px-2 py-1 fw-bold">🔴 Tinggi</span>
                                @elseif($risk->risk_level == 'Medium')
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fw-bold">🟡 Sedang</span>
                                @else
                                    <span class="badge bg-success rounded-pill px-2 py-1 fw-bold">🟢 Rendah</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info border-0 shadow-sm rounded-3 small">
                <i class="fa-solid fa-circle-info me-2"></i>
                Belum ada Risk Register untuk <strong>{{ $assignment->auditee_label }}</strong>.
                @hasrole('prodi|unit')
                <a href="{{ route('admin.risk-registers.create') }}" class="fw-bold">Tambahkan sekarang &rarr;</a>
                @endhasrole
            </div>
            @endif
        </div>
        {{-- ===== END PANEL RISK REGISTER ===== --}}


        @if(in_array($assignment->status, ['finalisasi', 'selesai']))
        <div class="alert alert-warning border-0 shadow-sm rounded-3 small mb-5 d-flex align-items-center gap-2">
            <i class="fa-solid fa-lock fa-fw text-dark fs-5"></i>
            <div>
                <strong>Kertas kerja terkunci</strong> &mdash; status audit: <span class="fw-bold text-uppercase">{{ $assignment->status }}</span>.
                Borang, temuan, dan skor bersifat <strong>read-only</strong>. Untuk melakukan perbaikan, gunakan tombol <em>Buka Kembali</em> oleh SPMI.
            </div>
        </div>
        @endif


        <!-- Synchronization action -->
        @if(!in_array($assignment->status, ['finalisasi', 'selesai']))
        @hasrole('spmi|auditor')
        <div class="card bg-primary bg-opacity-10 border-0 rounded-3 shadow-sm mb-5 p-4">
            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-link me-2"></i>Generate Daftar Tilik</h6>
            <p class="text-muted small mb-4">Tarik butir penilaian ke dalam borang. <strong>Daftar Tilik</strong> diambil dari master Standar Mutu (baku untuk semua auditee), sedangkan <strong>Tarik Data Otomatis</strong> menambahkan indikator dari Profil Risiko level tinggi milik auditee. Data yang sudah ada tidak akan menjadi ganda.</p>
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('admin.audit.instruments.generate-master', $assignment->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                        <i class="fa-solid fa-list-check me-2"></i> Generate Daftar Tilik
                    </button>
                </form>
                <form action="{{ route('admin.audit.instruments.sync-risk', $assignment->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary rounded-pill px-4 shadow-sm fw-bold">
                        <i class="fa-solid fa-cloud-arrow-down me-2"></i> Tarik Data dari Profil Risiko
                    </button>
                </form>
            </div>
        </div>
        @endhasrole
        @endif

        <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="fa-solid fa-list-check me-2 text-primary"></i> Borang Penilaian ({{ $instruments->count() }} Kriteria)</h6>

        <div class="alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 rounded-3 small py-2 px-3 mb-3">
            <div class="fw-bold mb-1"><i class="fa-solid fa-scale-balanced me-1"></i>Skala Penilaian Kategori Temuan</div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gx-3 gy-1">
                <div class="col"><strong>4</strong> &mdash; Melampaui Standar Nasional (Sangat Baik / Best Practice)</div>
                <div class="col"><strong>3</strong> &mdash; Sesuai dengan Standar (Terpenuhi)</div>
                <div class="col"><strong>2</strong> &mdash; Tidak tercapai ringan (KTS Minor / Ada dokumen yang kurang)</div>
                <div class="col"><strong>1</strong> &mdash; Tidak tercapai sedang (KTS Mayor / Sistem tidak berjalan)</div>
                <div class="col"><strong>0</strong> &mdash; Pelanggaran fatal atau tidak ada bukti sama sekali</div>
            </div>
        </div>
        
        <div class="table-responsive border rounded-3 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="py-3 px-3 border-0" style="width: 13%;">Standar Penilaian</th>
                        <th class="py-3 px-3 border-0 border-start" style="width: 20%;">Indikator</th>
                        <th class="py-3 px-3 border-0 border-start" style="width: 25%;">Klaim Evaluasi Diri Prodi</th>
                        <th class="py-3 px-3 border-0 border-start" style="width: 24%;">Temuan & Bukti Audit</th>
                        <th class="py-3 px-2 text-center border-0 border-start" style="width: 7%;">Nilai</th>
                        <th class="py-3 px-3 border-0 border-start text-center" style="width: 11%;">Kategori Temuan</th>
                        <th class="py-3 px-3 border-0 border-start text-center" style="width: 8%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instruments as $inst)
                    <tr>
                        <td class="px-3 py-3 align-top">
                            <h6 class="fw-bold fs-6 mb-0 text-dark">{{ $inst->criteria }}</h6>
                        </td>
                        <td class="px-3 py-3 align-top border-start">
                            <p class="text-muted small mb-1 lh-base">{{ $inst->indicator }}</p>
                            @if($inst->audit_question)
                            <p class="small fst-italic text-primary mb-1 lh-base"><i class="fa-solid fa-circle-question me-1"></i>{{ $inst->audit_question }}</p>
                            @endif
                            @if($inst->evidence_document)
                            <div class="small mb-1">
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 text-wrap text-start">
                                    <i class="fa-solid fa-file-circle-check me-1"></i>Bukti: {{ $inst->evidence_document }}
                                </span>
                            </div>
                            @endif
                            @if($inst->rubric_4 || $inst->rubric_3 || $inst->rubric_2 || $inst->rubric_1)
                            <details class="small">
                                <summary class="text-muted fw-semibold" style="cursor:pointer;"><i class="fa-solid fa-list-ol me-1"></i>Rubrik Penilaian</summary>
                                <div class="mt-1">
                                    @if($inst->rubric_4)<div class="mb-1"><span class="badge bg-success bg-opacity-10 text-success me-1">4</span><span class="text-muted">{{ $inst->rubric_4 }}</span></div>@endif
                                    @if($inst->rubric_3)<div class="mb-1"><span class="badge bg-info bg-opacity-10 text-info me-1">3</span><span class="text-muted">{{ $inst->rubric_3 }}</span></div>@endif
                                    @if($inst->rubric_2)<div class="mb-1"><span class="badge bg-warning bg-opacity-10 text-warning me-1">2</span><span class="text-muted">{{ $inst->rubric_2 }}</span></div>@endif
                                    @if($inst->rubric_1)<div class="mb-1"><span class="badge bg-danger bg-opacity-10 text-danger me-1">1</span><span class="text-muted">{{ $inst->rubric_1 }}</span></div>@endif
                                </div>
                            </details>
                            @endif
                        </td>
                        <td class="px-3 py-3 align-top border-start">
                            @php
                                $edItem = $edByIndicator->get(mb_strtolower(trim($inst->indicator)));
                            @endphp
                            @if($edItem)
                                <div class="mb-1">
                                    <span class="badge {{ $edItem->self_assessment == 'Tercapai' ? 'bg-success' : ($edItem->self_assessment == 'Belum Tercapai' ? 'bg-danger' : 'bg-secondary') }} rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        {{ $edItem->self_assessment ?? 'Belum dinilai' }}
                                    </span>
                                </div>
                                <div class="text-dark small border rounded-2 p-2 bg-white mb-2" style="white-space: pre-wrap;">
                                    {{ $edItem->narasi ?? '—' }}
                                </div>
                                @forelse($edItem->attachments as $b)
                                    <div class="mb-1">
                                        @if ($b->file_path)
                                            <a href="{{ asset('storage/' . $b->file_path) }}" target="_blank" class="small text-primary"><i class="fa-solid fa-file me-1"></i>{{ $b->file_name}}</a>
                                        @elseif ($b->link)
                                            <a href="{{ $b->link }}" target="_blank" class="small text-primary text-break"><i class="fa-brands fa-google-drive me-1"></i>{{ $b->link }}</a>
                                        @endif
                                        @if ($b->title)<span class="text-muted small ms-1">— {{ $b->title }}</span>@endif
                                    </div>
                                @empty
                                    <div class="text-muted small">Belum ada bukti Prodi.</div>
                                @endforelse
                            @else
                                <div class="text-muted small"><i class="fa-solid fa-circle-info me-1"></i>Indikator belum diisi Prodi pada menu Evaluasi Diri (ED).</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 align-top border-start bg-light bg-opacity-50">
                            @php
                                $findingsData = $inst->findings_data ?? [];
                                if (empty($findingsData)) {
                                    if ($inst->finding || $inst->document_link) {
                                        $findingsData = [['finding' => $inst->finding, 'document_link' => $inst->document_link]];
                                    } else {
                                        $findingsData = [['finding' => '', 'document_link' => '']];
                                    }
                                }
                            @endphp

                            <div class="findings-wrapper" id="findings-wrapper-{{ $inst->id }}">
                                @foreach($findingsData as $idx => $fdata)
                                <div class="finding-row mb-3 pb-3 {{ !$loop->last ? 'border-bottom border-secondary border-opacity-25' : '' }}">
                                    <div class="d-flex justify-content-between mb-1 align-items-center">
                                        <span class="badge bg-secondary text-white finding-label">Temuan {{ $idx + 1 }}</span>
                                        @if($idx > 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-remove-finding border-0" title="Hapus"><i class="fa-solid fa-times"></i></button>
                                        @endif
                                    </div>
                                    
                                    <textarea name="findings[]" form="update-inst-{{ $inst->id }}" class="form-control form-control-sm border-secondary shadow-sm bg-white mb-2" rows="3" placeholder="Tuliskan temuan auditor...">{{ $fdata['finding'] ?? '' }}</textarea>
                                    
                                    <div class="small fw-semibold text-muted mb-1"><i class="fa-solid fa-camera me-1"></i>Lampiran Bukti Lapangan Auditor</div>
                                    <div class="input-group input-group-sm mb-1">
                                        <span class="input-group-text bg-white border-secondary" title="Lampiran Bukti Lapangan Auditor (mis. foto visitasi)"><i class="fa-solid fa-link text-muted"></i></span>
                                        <input type="url" name="document_links[]" form="update-inst-{{ $inst->id }}" value="{{ $fdata['document_link'] ?? '' }}" class="form-control form-control-sm border-secondary shadow-sm bg-white" placeholder="Lampiran Bukti Lapangan Auditor — Link GDrive (Opsional)...">
                                    </div>
                                    
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text bg-white border-secondary" title="Lampiran Bukti Lapangan Auditor (mis. foto visitasi)"><i class="fa-solid fa-upload text-muted"></i></span>
                                        <input type="file" name="document_files[]" form="update-inst-{{ $inst->id }}" class="form-control form-control-sm border-secondary shadow-sm bg-white" aria-label="Lampiran Bukti Lapangan Auditor — file bukti lapangan Anda">
                                    </div>

                                    @if(!empty($fdata['file_path']))
                                        <a href="{{ asset('storage/' . $fdata['file_path']) }}" target="_blank" class="btn btn-sm btn-info w-100 shadow-sm mb-1 text-white"><i class="fa-solid fa-file-arrow-down"></i> Lihat File Tersimpan</a>
                                    @endif
                                    @if(!empty($fdata['document_link']))
                                        <a href="{{ $fdata['document_link'] }}" target="_blank" class="btn btn-sm btn-success w-100 shadow-sm"><i class="fa-brands fa-google-drive"></i> Buka Link</a>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 border-dashed btn-add-finding" style="border-style: dashed;" data-inst-id="{{ $inst->id }}">
                                <i class="fa-solid fa-plus me-1"></i> Tambah Temuan
                            </button>
                        </td>
                        <td class="px-2 py-3 text-center border-start bg-light bg-opacity-50">
                            <select name="score" form="update-inst-{{ $inst->id }}" class="form-select text-center fw-bold shadow-sm form-select-sm {{ $inst->score ? 'border-success text-success' : '' }}" style="width: 70px; margin: 0 auto; min-height: 35px; font-size: 1rem;">
                                <option value="">-</option>
                                @for($i=4; $i>=0; $i--)
                                    <option value="{{ $i }}" {{ $inst->score == $i && $inst->score !== null ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </td>
                        <td class="px-3 py-3 text-center border-start bg-light bg-opacity-50 align-middle">
                            <input type="hidden" name="finding_category" form="update-inst-{{ $inst->id }}" value="{{ $inst->finding_category }}">
                            <span class="badge category-badge shadow-sm px-2 py-2 text-wrap lh-base w-100" style="font-size: 0.75rem;">
                                @if($inst->finding_category == 'Melampaui Standar Nasional') <span class="bg-success text-white rounded-pill px-2 py-1">✓ Melampaui</span>
                                @elseif($inst->finding_category == 'Sesuai dengan Standar') <span class="bg-success text-white rounded-pill px-2 py-1">✓ Sesuai</span>
                                @elseif($inst->finding_category == 'Tidak Tercapai Ringan (KTS Minor)') <span class="bg-warning text-dark rounded-pill px-2 py-1">⚠️ KTS Minor</span>
                                @elseif($inst->finding_category == 'Tidak Tercapai Sedang (KTS Mayor)') <span class="bg-danger rounded-pill px-2 py-1 text-white">❌ KTS Mayor</span>
                                @elseif($inst->finding_category == 'Pelanggaran Fatal') <span class="bg-dark rounded-pill px-2 py-1 text-white">❌ Pelanggaran Fatal</span>
                                @else <span class="bg-secondary text-white rounded-pill px-2 py-1">Pilih Kategori...</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center border-start">
                            <form action="{{ route('admin.audit.instruments.update', $inst->id) }}" method="POST" id="update-inst-{{ $inst->id }}" class="d-none" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                            </form>
                            <form action="{{ route('admin.audit.instruments.destroy', $inst->id) }}" method="POST" id="delete-inst-{{ $inst->id }}" class="d-none" onsubmit="return confirm('Yakin ingin menghapus instrumen borang ini?');">
                                @csrf
                                @method('DELETE')
                            </form>
                            
                            <div class="d-flex flex-column gap-2">
                                @if(!in_array($assignment->status, ['finalisasi', 'selesai']))
                                @hasrole('spmi|auditor')
                                <button type="submit" form="update-inst-{{ $inst->id }}" class="btn btn-success btn-sm shadow-sm fw-bold rounded-3">
                                    <i class="fa-solid fa-check me-1"></i>Simpan
                                </button>
                                <button type="submit" form="update-inst-{{ $inst->id }}" class="btn btn-warning btn-sm shadow-sm fw-bold rounded-3 text-dark">
                                    <i class="fa-solid fa-save me-1"></i>Edit
                                </button>
                                <button type="submit" form="delete-inst-{{ $inst->id }}" class="btn btn-danger btn-sm shadow-sm fw-bold rounded-3">
                                    <i class="fa-solid fa-trash me-1"></i>Hapus
                                </button>
                                @endhasrole
                                @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-2"><i class="fa-solid fa-lock me-1"></i> Read-only</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-folder-open fs-1 text-muted opacity-25 mb-3"></i>
                            <h6 class="fw-bold text-dark">Belum ada Instrumen</h6>
                            <p class="small text-muted mb-0">Silakan tambahkan kriteria/indikator pertama menggunakan tombol panel di atas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic for dynamic findings
    document.querySelectorAll('.btn-add-finding').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const instId = this.getAttribute('data-inst-id');
            const wrapper = document.getElementById('findings-wrapper-' + instId);
            const rowCount = wrapper.querySelectorAll('.finding-row').length;
            const newIndex = rowCount + 1;
            
            // Normalize bottom border of the previous last item
            const lastRow = wrapper.lastElementChild;
            if (lastRow) {
                lastRow.classList.add('border-bottom', 'border-secondary', 'border-opacity-25');
            }
            
            const html = `
                <div class="finding-row mb-3 pb-3 border-opacity-25">
                    <div class="d-flex justify-content-between mb-1 align-items-center">
                        <span class="badge bg-secondary text-white finding-label">Temuan ${newIndex}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-remove-finding border-0" title="Hapus"><i class="fa-solid fa-times"></i></button>
                    </div>
                    
                    <textarea name="findings[]" form="update-inst-${instId}" class="form-control form-control-sm border-secondary shadow-sm bg-white mb-2" rows="3" placeholder="Tuliskan temuan auditor..."></textarea>
                    
                    <div class="small fw-semibold text-muted mb-1"><i class="fa-solid fa-camera me-1"></i>Lampiran Bukti Lapangan Auditor</div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-white border-secondary" title="Lampiran Bukti Lapangan Auditor (mis. foto visitasi)"><i class="fa-solid fa-link text-muted"></i></span>
                        <input type="url" name="document_links[]" form="update-inst-${instId}" class="form-control form-control-sm border-secondary shadow-sm bg-white" placeholder="Lampiran Bukti Lapangan Auditor — Link GDrive (Opsional)...">
                    </div>
                    
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white border-secondary" title="Lampiran Bukti Lapangan Auditor (mis. foto visitasi)"><i class="fa-solid fa-upload text-muted"></i></span>
                        <input type="file" name="document_files[]" form="update-inst-${instId}" class="form-control form-control-sm border-secondary shadow-sm bg-white" aria-label="Lampiran Bukti Lapangan Auditor — file bukti lapangan Anda">
                    </div>
                </div>
            `;
            
            wrapper.insertAdjacentHTML('beforeend', html);
        });
    });

    // Event delegation for remove buttons as they are added dynamically
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-finding')) {
            const btn = e.target.closest('.btn-remove-finding');
            const row = btn.closest('.finding-row');
            const wrapper = row.closest('.findings-wrapper');
            row.remove();
            
            // Re-index remaining labels
            const rows = wrapper.querySelectorAll('.finding-row');
            rows.forEach(function(r, index) {
                const label = r.querySelector('.finding-label');
                if (label) {
                    label.textContent = 'Temuan ' + (index + 1);
                }
                
                // Adjust borders
                if (index === rows.length - 1) {
                    r.classList.remove('border-bottom', 'border-secondary', 'border-opacity-25');
                } else {
                    r.classList.add('border-bottom', 'border-secondary', 'border-opacity-25');
                }
            });
        }
    });

    document.querySelectorAll('select[name="score"]').forEach(function(select) {
        select.addEventListener('change', function() {
            const score = this.value;
            const row = this.closest('tr');
            if (!row) return;
            
            const hiddenInput = row.querySelector('input[name="finding_category"]');
            const badgeSpan = row.querySelector('.category-badge');
            
            if (hiddenInput && badgeSpan && score !== "") {
                const numScore = parseInt(score);
                let badgeHtml = '';
                
                if (numScore === 4) {
                    hiddenInput.value = 'Melampaui Standar Nasional';
                    badgeHtml = '<span class="bg-success text-white rounded-pill px-3 py-1">✓ Melampaui</span>';
                } else if (numScore === 3) {
                    hiddenInput.value = 'Sesuai dengan Standar';
                    badgeHtml = '<span class="bg-success text-white rounded-pill px-3 py-1">✓ Sesuai</span>';
                } else if (numScore === 2) {
                    hiddenInput.value = 'Tidak Tercapai Ringan (KTS Minor)';
                    badgeHtml = '<span class="bg-warning text-dark rounded-pill px-3 py-1">⚠️ KTS Minor</span>';
                } else if (numScore === 1) {
                    hiddenInput.value = 'Tidak Tercapai Sedang (KTS Mayor)';
                    badgeHtml = '<span class="bg-danger text-white rounded-pill px-3 py-1">❌ KTS Mayor</span>';
                } else if (numScore === 0) {
                    hiddenInput.value = 'Pelanggaran Fatal';
                    badgeHtml = '<span class="bg-dark text-white rounded-pill px-3 py-1">❌ Pelanggaran Fatal</span>';
                }
                badgeSpan.innerHTML = badgeHtml;
            } else if (hiddenInput && badgeSpan) {
                hiddenInput.value = '';
                badgeSpan.innerHTML = '<span class="bg-secondary text-white rounded-pill px-3 py-1">Pilih Kategori...</span>';
            }
        });
    });
});
</script>
@endpush
