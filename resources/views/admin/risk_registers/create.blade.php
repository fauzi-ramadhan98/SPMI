@extends('layouts.admin')

@section('title', 'Tambah Profil Risiko')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-danger">
    <div class="card-header-custom mb-3">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-plus me-2 text-danger"></i>Tambah Profil Risiko (Risk Register)</h5>
        <p class="text-muted small mb-0 mt-1">Isi formulir berikut untuk mendaftarkan potensi risiko pada siklus audit mendatang.</p>
    </div>
    <div class="card-body">

        @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Terdapat kesalahan input:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
        @endif

        {{-- Opsi Bulk: Download Template & Upload Excel --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4" style="background:#f0f9ff;">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="fa-solid fa-file-excel text-success me-1"></i>Upload Excel / CSV (Banyak Risiko Sekaligus)</h6>
                        <div class="small text-muted">1) Download template yang sudah ada dropdown Standar & Indikator dari sistem → 2) Isi puluhan baris → 3) Upload kembali. <span class="badge bg-success bg-opacity-10 text-success border">Dropdown anti-typo</span></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.risk-registers.template') }}" class="btn btn-outline-success btn-sm rounded-pill px-3"><i class="fa-solid fa-download me-1"></i>Download Template Excel</a>
                    </div>
                </div>
                <form action="{{ route('admin.risk-registers.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 mt-3 align-items-end flex-wrap">
                    @csrf
                    <div class="flex-grow-1" style="max-width:360px;">
                        <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button class="btn btn-success btn-sm rounded-pill px-3"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload & Import</button>
                </form>
            </div>
        </div>

        {{-- Tabel Input Dinamis --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-table-list text-primary me-1"></i>Tabel Input Dinamis (Tambah Baris)</h6>
                <button type="button" id="btnAddRow" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="fa-solid fa-plus me-1"></i>Add Row</button>
            </div>
            <div class="card-body p-0">
                <form id="formBulk" action="{{ route('admin.risk-registers.bulk') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0" id="tblDinamis" style="font-size:12px; min-width:1400px;">
                            <thead class="table-light text-center" style="font-size:11px;">
                                <tr>
                                    <th style="width:30px;">#</th>
                                    <th>Tahun</th>
                                    <th>Semester</th>
                                    <th>Standar</th>
                                    <th>Indikator</th>
                                    <th>Kondisi</th>
                                    <th>Risiko</th>
                                    <th>Akar Masalah</th>
                                    <th>Kategori</th>
                                    <th>P</th>
                                    <th>D</th>
                                    <th>Mitigasi</th>
                                    <th>PIC</th>
                                    <th>Target</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDinamis">
                                {{-- baris akan di-generate JS --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4"><i class="fa-solid fa-floppy-disk me-1"></i>Simpan Semua</button>
                        <button type="button" id="btnClearRows" class="btn btn-light btn-sm border rounded-pill px-3">Bersihkan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center my-2"><span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3">— atau isi formulir tunggal di bawah —</span></div>

        <form action="{{ route('admin.risk-registers.store') }}" method="POST">
            @csrf

            {{-- BAGIAN 1: Identitas --}}
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-id-card me-2"></i>Identitas</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Program Studi</label>
                        @if($lockedProgramId)
                            <input type="hidden" name="academic_program_id" value="{{ $lockedProgramId }}">
                            <select class="form-select bg-light" disabled>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" selected>{{ $program->name }} ({{ $program->degree_level }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-success"><i class="fa-solid fa-lock me-1"></i>Otomatis diisi sesuai akun Anda.</div>
                        @else
                            <select name="academic_program_id" class="form-select @error('academic_program_id') is-invalid @enderror">
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('academic_program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} ({{ $program->degree_level }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('academic_program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Unit Kerja</label>
                        @if($lockedUnitId)
                            <input type="hidden" name="unit_id" value="{{ $lockedUnitId }}">
                            <select class="form-select bg-light" disabled>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" selected>{{ $unit->name }} ({{ $unit->category_label }})</option>
                                @endforeach
                            </select>
                            <div class="form-text text-success"><i class="fa-solid fa-lock me-1"></i>Otomatis diisi sesuai akun Anda.</div>
                        @else
                            <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                <option value="">-- Pilih Unit Kerja --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->category_label }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @if(!$lockedProgramId && !$lockedUnitId)
                            <div class="form-text">Pilih salah satu: Program Studi ATAU Unit Kerja.</div>
                        @endif
                        @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tahun Akademik <span class="text-danger">*</span></label>
                        <select name="academic_year" class="form-select @error('academic_year') is-invalid @enderror" required>
                            <option value="">-- Pilih Tahun Akademik --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->name }}" {{ old('academic_year') == $year->name ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                        @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">-- Pilih Semester --</option>
                            <option value="Ganjil" {{ old('semester', $defaultSemester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ old('semester', $defaultSemester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                        @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Risiko dicatat per semester sesuai penugasan SPMI.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Standar Mutu Terkait <span class="text-danger">*</span></label>
                        <select name="standar_mutu" id="standarMutu" class="form-select @error('standar_mutu') is-invalid @enderror" required>
                            <option value="">-- Pilih Standar Mutu --</option>
                            @foreach($standards as $std)
                                <option value="{{ $std->name }}" data-indicators='@json($std->indicatorData())' {{ old('standar_mutu') == $std->name ? 'selected' : '' }}>
                                    {{ $std->kode_standar ? $std->kode_standar . ' - ' : '' }}{{ $std->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Standar SPMI yang berkaitan dengan risiko ini.</div>
                        @error('standar_mutu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kategori Risiko <span class="text-danger">*</span></label>
                        <select name="risk_category" class="form-select @error('risk_category') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori Risiko --</option>
                            <option value="Operasional" {{ old('risk_category') == 'Operasional' ? 'selected' : '' }}>Operasional</option>
                            <option value="SDM" {{ old('risk_category') == 'SDM' ? 'selected' : '' }}>SDM</option>
                            <option value="Keuangan" {{ old('risk_category') == 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                            <option value="Teknologi" {{ old('risk_category') == 'Teknologi' ? 'selected' : '' }}>Teknologi</option>
                            <option value="Kepatuhan" {{ old('risk_category') == 'Kepatuhan' ? 'selected' : '' }}>Kepatuhan</option>
                            <option value="Reputasi" {{ old('risk_category') == 'Reputasi' ? 'selected' : '' }}>Reputasi</option>
                        </select>
                        <div class="form-text">Kategori klasifikasi risiko.</div>
                        @error('risk_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: Butir, Kondisi Saat Ini & Risiko --}}
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>Detail Risiko</h6>
                <div class="mb-3">
                    <label class="form-label fw-bold">Indikator <span class="text-danger">*</span></label>
                    <div class="form-text mb-1">Indikator yang akan dievaluasi (pilih standar terlebih dahulu).</div>
                    <select name="butir_tilik" id="butirTilik" class="form-select @error('butir_tilik') is-invalid @enderror" required>
                        <option value="">-- Pilih Standar Terlebih Dahulu --</option>
                    </select>
                    @error('butir_tilik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kondisi Saat Ini <span class="text-danger">*</span></label>
                    <div class="form-text mb-1">Catatan kondisi aktual yang ditemukan saat ini.</div>
                    <textarea name="temuan" class="form-control @error('temuan') is-invalid @enderror" rows="3"
                        placeholder="Tuliskan kondisi aktual yang menjadi dasar risiko..." required>{{ old('temuan') }}</textarea>
                    @error('temuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Akar Masalah <span class="text-danger">*</span></label>
                    <div class="form-text mb-1">Penyebab utama yang melatarbelakangi kondisi ini.</div>
                    <textarea name="akar_masalah" class="form-control @error('akar_masalah') is-invalid @enderror" rows="3"
                        placeholder="Mengapa kondisi ini bisa terjadi?" required>{{ old('akar_masalah') }}</textarea>
                    @error('akar_masalah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Deskripsi Risiko <span class="text-danger">*</span></label>
                    <div class="form-text mb-1">Potensi risiko yang mungkin terjadi jika tidak ditangani.</div>
                    <textarea name="risk_description" class="form-control @error('risk_description') is-invalid @enderror" rows="3"
                        placeholder="Sebutkan potensi risiko yang akan dihadapi..." required>{{ old('risk_description') }}</textarea>
                    @error('risk_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- BAGIAN 3: Analisis Risiko --}}
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-warning mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Analisis Risiko</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-meteor me-1 text-danger"></i>Impact (Dampak) <span class="text-danger">*</span></label>
                        <div class="form-text mt-0 mb-2">Seberapa besar dampak/kerugian yang ditimbulkan?</div>
                        <select name="impact" class="form-select @error('impact') is-invalid @enderror" required>
                            <option value="">-- Pilih Tingkat Dampak --</option>
                            <option value="1" {{ old('impact') == 1 ? 'selected' : '' }}>1 - Sangat Rendah</option>
                            <option value="2" {{ old('impact') == 2 ? 'selected' : '' }}>2 - Rendah</option>
                            <option value="3" {{ old('impact') == 3 ? 'selected' : '' }}>3 - Sedang</option>
                            <option value="4" {{ old('impact') == 4 ? 'selected' : '' }}>4 - Tinggi</option>
                            <option value="5" {{ old('impact') == 5 ? 'selected' : '' }}>5 - Sangat Tinggi</option>
                        </select>
                        @error('impact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-dice me-1 text-primary"></i>Likelihood (Kemungkinan) <span class="text-danger">*</span></label>
                        <div class="form-text mt-0 mb-2">Seberapa sering risiko ini diperkirakan terjadi?</div>
                        <select name="probability" class="form-select @error('probability') is-invalid @enderror" required>
                            <option value="">-- Pilih Kemungkinan --</option>
                            <option value="1" {{ old('probability') == 1 ? 'selected' : '' }}>1 - Sangat Jarang</option>
                            <option value="2" {{ old('probability') == 2 ? 'selected' : '' }}>2 - Jarang</option>
                            <option value="3" {{ old('probability') == 3 ? 'selected' : '' }}>3 - Sedang / Kadang</option>
                            <option value="4" {{ old('probability') == 4 ? 'selected' : '' }}>4 - Sering</option>
                            <option value="5" {{ old('probability') == 5 ? 'selected' : '' }}>5 - Sangat Sering (Hampir Pasti)</option>
                        </select>
                        @error('probability') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- BAGIAN 4: Pengendalian --}}
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-shield-halved me-2"></i>Rencana Pengendalian</h6>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mitigasi <span class="text-danger">*</span></label>
                    <textarea name="mitigation_plan" class="form-control @error('mitigation_plan') is-invalid @enderror" rows="3"
                        placeholder="Langkah-langkah untuk meminimalisasi atau mengatasi dampak risiko" required>{{ old('mitigation_plan') }}</textarea>
                    @error('mitigation_plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa-solid fa-cloud-arrow-up me-1 text-success"></i>Upload dokumen pendukung <span class="text-muted fw-normal">(Opsional)</span></label>
                    <input type="url" name="document_link" class="form-control @error('document_link') is-invalid @enderror"
                        placeholder="Tempel link dokumen (Drive / URL) - Contoh: https://drive.google.com/..." value="{{ old('document_link') }}">
                    @error('document_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-user-tie me-1 text-primary"></i>PIC (Penanggungjawab) <span class="text-danger">*</span></label>
                        <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror"
                            placeholder="Nama penanggungjawab penyelesaian risiko ini" value="{{ old('pic') }}" required>
                        @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-calendar-check me-1 text-danger"></i>Tanggal Penyelesaian <span class="text-danger">*</span></label>
                        <input type="date" name="target_date" class="form-control @error('target_date') is-invalid @enderror"
                            value="{{ old('target_date') }}" required>
                        @error('target_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-semibold">
                <i class="fa-solid fa-save me-2"></i>Simpan Profil Risiko
            </button>
            <a href="{{ route('admin.risk-registers.index') }}" class="btn btn-light rounded-pill px-4 ms-2 border">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const standarSelect = document.getElementById('standarMutu');
        const indikatorSelect = document.getElementById('butirTilik');
        const oldIndikator = "{!! addslashes(old('butir_tilik')) !!}";

        function updateIndikator() {
            const selectedOption = standarSelect.options[standarSelect.selectedIndex];
            indikatorSelect.innerHTML = '<option value="">-- Pilih Indikator --</option>';
            
            if (!selectedOption || !selectedOption.value) return;

            const indicatorsJson = selectedOption.getAttribute('data-indicators');
            if (indicatorsJson) {
                try {
                    const data = JSON.parse(indicatorsJson);
                    const optionsSet = new Set();

                    // Add IKU indicators
                    if (data.iku && Array.isArray(data.iku)) {
                        data.iku.forEach((item, index) => {
                            const text = item.text || '';
                            const target = item.target ? ' (Target: ' + item.target + ')' : '';
                            const displayText = 'IKU ' + (index + 1) + ': ' + text + target;
                            optionsSet.add(displayText);
                        });
                    }

                    // Add IKT indicators
                    if (data.ikt && Array.isArray(data.ikt)) {
                        data.ikt.forEach((item, index) => {
                            const text = item.text || '';
                            const target = item.target ? ' (Target: ' + item.target + ')' : '';
                            const displayText = 'IKT ' + (index + 1) + ': ' + text + target;
                            optionsSet.add(displayText);
                        });
                    }

                    // Add options to DOM
                    optionsSet.forEach(optVal => {
                        const opt = document.createElement('option');
                        opt.value = optVal;
                        opt.textContent = optVal;
                        if (oldIndikator && oldIndikator === optVal) opt.selected = true;
                        indikatorSelect.appendChild(opt);
                    });

                    // If oldIndikator exists but not in options, add it
                    if (oldIndikator && !optionsSet.has(oldIndikator)) {
                        const opt = document.createElement('option');
                        opt.value = oldIndikator;
                        opt.textContent = oldIndikator;
                        opt.selected = true;
                        indikatorSelect.appendChild(opt);
                    }
                } catch (e) {
                    console.error('Error parsing indicators:', e);
                }
            }
        }

        standarSelect.addEventListener('change', updateIndikator);
        
        // Trigger on load for prepopulating from old() validation redirects
        if (standarSelect.value) {
            updateIndikator();
        }

        // ===== Tabel Dinamis =====
        const tbody = document.getElementById('tbodyDinamis');
        const btnAdd = document.getElementById('btnAddRow');
        const btnClear = document.getElementById('btnClearRows');
        const standardsData = @json($standards->map(fn($s) => ['name'=>$s->name, 'kode'=>$s->kode_standar, 'indicators'=>$s->indicatorData()])->values());
        const tahunOptions = @json($academicYears->pluck('name'));
        const kategoriOptions = ['Operasional','SDM','Keuangan','Teknologi','Kepatuhan','Reputasi'];
        let rowIdx = 0;
        function makeRow(idx){
            const tahunOpts = tahunOptions.map(t=>`<option value="${t}">${t}</option>`).join('');
            const standarOpts = standardsData.map(s=>`<option value="${s.name}">${s.kode? s.kode+' - ':''}${s.name}</option>`).join('');
            const kategoriOpts = kategoriOptions.map(k=>`<option value="${k}">${k}</option>`).join('');
            return `<tr>
                <td class="text-center small">${idx+1}</td>
                <td><select name="rows[${idx}][academic_year]" class="form-select form-select-sm" required><option value="">--</option>${tahunOpts}</select></td>
                <td><select name="rows[${idx}][semester]" class="form-select form-select-sm" required><option value="">--</option><option value="Ganjil">Ganjil</option><option value="Genap">Genap</option></select></td>
                <td><select name="rows[${idx}][standar_mutu]" class="form-select form-select-sm sel-standar" data-row="${idx}" required><option value="">--</option>${standarOpts}</select></td>
                <td><select name="rows[${idx}][butir_tilik]" class="form-select form-select-sm sel-indikator" data-row="${idx}" required><option value="">-- Pilih Standar dulu --</option></select></td>
                <td><textarea name="rows[${idx}][temuan]" class="form-control form-control-sm" rows="2" required placeholder="Kondisi Saat Ini"></textarea></td>
                <td><textarea name="rows[${idx}][risk_description]" class="form-control form-control-sm" rows="2" required placeholder="Risiko"></textarea></td>
                <td><textarea name="rows[${idx}][akar_masalah]" class="form-control form-control-sm" rows="2" required placeholder="Akar"></textarea></td>
                <td><select name="rows[${idx}][risk_category]" class="form-select form-select-sm" required><option value="">--</option>${kategoriOpts}</select></td>
                <td><select name="rows[${idx}][impact]" class="form-select form-select-sm" required><option value="">-</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option></select></td>
                <td><select name="rows[${idx}][probability]" class="form-select form-select-sm" required><option value="">-</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option></select></td>
                <td><textarea name="rows[${idx}][mitigation_plan]" class="form-control form-control-sm" rows="2" required placeholder="Mitigasi"></textarea></td>
                <td><input name="rows[${idx}][pic]" class="form-control form-control-sm" required placeholder="PIC"></td>
                <td><input type="date" name="rows[${idx}][target_date]" class="form-control form-control-sm" required></td>
                <td class="text-center admin-actions-cell"><div class="admin-table-actions"><button type="button" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action btnDelRow" aria-label="Hapus" title="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button></div></td>
            </tr>`;
        }
        function renumber(){ tbody.querySelectorAll('tr').forEach((tr,i)=>{ tr.querySelector('td:first-child').textContent=i+1; }); }
        function updateRowIndikator(sel){
            const idx = sel.dataset.row;
            const val = sel.value;
            const sd = standardsData.find(s=>s.name===val);
            const target = tbody.querySelector(`select.sel-indikator[data-row="${idx}"]`);
            if(!target) return;
            target.innerHTML='<option value="">-- Pilih Indikator --</option>';
            if(!sd) return;
            const add = (arr, prefix)=> arr.forEach((it,i)=>{ const txt = it.text||''; const tgt = it.target? ' (Target: '+it.target+')':''; const disp = prefix+' '+(i+1)+': '+txt+tgt; const opt=document.createElement('option'); opt.value=disp; opt.textContent=disp; target.appendChild(opt); });
            add(sd.indicators.iku||[], 'IKU');
            add(sd.indicators.ikt||[], 'IKT');
        }
        if(btnAdd && tbody){
            // init 1 row
            tbody.insertAdjacentHTML('beforeend', makeRow(rowIdx++));
            btnAdd.addEventListener('click', ()=>{ tbody.insertAdjacentHTML('beforeend', makeRow(rowIdx++)); });
            btnClear.addEventListener('click', ()=>{ tbody.innerHTML=''; rowIdx=0; tbody.insertAdjacentHTML('beforeend', makeRow(rowIdx++)); });
            tbody.addEventListener('click', (e)=>{ if(e.target.closest('.btnDelRow')){ e.target.closest('tr').remove(); renumber(); }});
            tbody.addEventListener('change', (e)=>{ if(e.target.classList.contains('sel-standar')) updateRowIndikator(e.target); });
        }
    });
</script>
@endpush
