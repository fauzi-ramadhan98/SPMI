@extends('layouts.admin')

@section('title', 'Edit Profil Risiko')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-warning">
    <div class="card-header-custom mb-3">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Edit Profil Risiko (Risk Register)</h5>
        <p class="text-muted small mb-0 mt-1">Perbarui data profil risiko di bawah ini.</p>
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

        {{-- Status Persetujuan SPMI --}}
        <div class="alert @if($riskRegister->status == 'approved') alert-success @elseif($riskRegister->status == 'revision') alert-danger @else alert-warning @endif border-0 shadow-sm rounded-3 mb-4 d-flex gap-3 align-items-start">
            <i class="fa-solid @if($riskRegister->status == 'approved') fa-circle-check @elseif($riskRegister->status == 'revision') fa-circle-exclamation @else fa-clock @endif fa-fw fs-4 mt-1"></i>
            <div>
                <div class="fw-bold">
                    Status: 
                    @if($riskRegister->status == 'approved')
                        Disetujui SPMI
                    @elseif($riskRegister->status == 'revision')
                        Perlu Revisi
                    @else
                        Menunggu Review
                    @endif
                </div>
                @if($riskRegister->status_note)
                    <div class="small mt-1"><i class="fa-solid fa-message me-1"></i><strong>Catatan SPMI:</strong> {{ $riskRegister->status_note }}</div>
                @endif
                @if($riskRegister->validatedBy)
                    <div class="small mt-1 text-muted">Direview oleh {{ $riskRegister->validatedBy->name }} {{ $riskRegister->validated_at?->format('d M Y') }}</div>
                @endif
                <div class="small mt-1 text-muted"><i class="fa-solid fa-circle-info me-1"></i>Setelah disimpan, status akan kembali menjadi <strong>Menunggu Review</strong> untuk divalidasi ulang oleh SPMI.</div>
            </div>
        </div>

        <form id="update-form" action="{{ route('admin.risk-registers.update', $riskRegister->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                                    <option value="{{ $program->id }}" {{ old('academic_program_id', $riskRegister->academic_program_id) == $program->id ? 'selected' : '' }}>
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
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $riskRegister->unit_id) == $unit->id ? 'selected' : '' }}>
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
                                <option value="{{ $year->name }}" {{ old('academic_year', $riskRegister->academic_year) == $year->name ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                            @if($riskRegister->academic_year && !$academicYears->pluck('name')->contains($riskRegister->academic_year))
                                <option value="{{ $riskRegister->academic_year }}" selected>{{ $riskRegister->academic_year }} (tidak aktif)</option>
                            @endif
                        </select>
                        @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">-- Pilih Semester --</option>
                            <option value="Ganjil" {{ old('semester', $riskRegister->semester ?: $defaultSemester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ old('semester', $riskRegister->semester ?: $defaultSemester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                        @error('semester') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Standar Mutu Terkait <span class="text-danger">*</span></label>
                        <select name="standar_mutu" id="standarMutu" class="form-select @error('standar_mutu') is-invalid @enderror" required>
                            <option value="">-- Pilih Standar Mutu --</option>
                            @foreach($standards as $std)
                                <option value="{{ $std->name }}" data-indicators='@json($std->indicatorData())' 
                                    {{ old('standar_mutu', $riskRegister->standar_mutu) == $std->name ? 'selected' : '' }}>
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
                            <option value="Operasional" {{ old('risk_category', $riskRegister->risk_category) == 'Operasional' ? 'selected' : '' }}>Operasional</option>
                            <option value="SDM" {{ old('risk_category', $riskRegister->risk_category) == 'SDM' ? 'selected' : '' }}>SDM</option>
                            <option value="Keuangan" {{ old('risk_category', $riskRegister->risk_category) == 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                            <option value="Teknologi" {{ old('risk_category', $riskRegister->risk_category) == 'Teknologi' ? 'selected' : '' }}>Teknologi</option>
                            <option value="Kepatuhan" {{ old('risk_category', $riskRegister->risk_category) == 'Kepatuhan' ? 'selected' : '' }}>Kepatuhan</option>
                            <option value="Reputasi" {{ old('risk_category', $riskRegister->risk_category) == 'Reputasi' ? 'selected' : '' }}>Reputasi</option>
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
                        placeholder="Tuliskan kondisi aktual yang menjadi dasar risiko..." required>{{ old('temuan', $riskRegister->temuan) }}</textarea>
                    @error('temuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Akar Masalah <span class="text-danger">*</span></label>
                    <div class="form-text mb-1">Penyebab utama yang melatarbelakangi kondisi ini.</div>
                    <textarea name="akar_masalah" class="form-control @error('akar_masalah') is-invalid @enderror" rows="3"
                        placeholder="Mengapa kondisi ini bisa terjadi?" required>{{ old('akar_masalah', $riskRegister->akar_masalah) }}</textarea>
                    @error('akar_masalah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Deskripsi Risiko <span class="text-danger">*</span></label>
                    <div class="form-text mb-1">Potensi risiko yang mungkin terjadi jika tidak ditangani.</div>
                    <textarea name="risk_description" class="form-control @error('risk_description') is-invalid @enderror" rows="3"
                        placeholder="Sebutkan potensi risiko yang akan dihadapi..." required>{{ old('risk_description', $riskRegister->risk_description) }}</textarea>
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
                            <option value="1" {{ old('impact', $riskRegister->impact) == 1 ? 'selected' : '' }}>1 - Sangat Rendah</option>
                            <option value="2" {{ old('impact', $riskRegister->impact) == 2 ? 'selected' : '' }}>2 - Rendah</option>
                            <option value="3" {{ old('impact', $riskRegister->impact) == 3 ? 'selected' : '' }}>3 - Sedang</option>
                            <option value="4" {{ old('impact', $riskRegister->impact) == 4 ? 'selected' : '' }}>4 - Tinggi</option>
                            <option value="5" {{ old('impact', $riskRegister->impact) == 5 ? 'selected' : '' }}>5 - Sangat Tinggi</option>
                        </select>
                        @error('impact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-dice me-1 text-primary"></i>Likelihood (Kemungkinan) <span class="text-danger">*</span></label>
                        <div class="form-text mt-0 mb-2">Seberapa sering risiko ini diperkirakan terjadi?</div>
                        <select name="probability" class="form-select @error('probability') is-invalid @enderror" required>
                            <option value="">-- Pilih Kemungkinan --</option>
                            <option value="1" {{ old('probability', $riskRegister->probability) == 1 ? 'selected' : '' }}>1 - Sangat Jarang</option>
                            <option value="2" {{ old('probability', $riskRegister->probability) == 2 ? 'selected' : '' }}>2 - Jarang</option>
                            <option value="3" {{ old('probability', $riskRegister->probability) == 3 ? 'selected' : '' }}>3 - Sedang / Kadang</option>
                            <option value="4" {{ old('probability', $riskRegister->probability) == 4 ? 'selected' : '' }}>4 - Sering</option>
                            <option value="5" {{ old('probability', $riskRegister->probability) == 5 ? 'selected' : '' }}>5 - Sangat Sering (Hampir Pasti)</option>
                        </select>
                        @error('probability') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-2 p-3 rounded-3 border bg-white d-flex align-items-center gap-3">
                    <div class="text-muted small fw-semibold">Skor Tersimpan:</div>
                    <span class="badge fs-5 fw-bold px-3 py-2
                        @if($riskRegister->risk_level == 'High') bg-danger
                        @elseif($riskRegister->risk_level == 'Medium') bg-warning text-dark
                        @else bg-success @endif">
                        {{ $riskRegister->risk_score }}
                    </span>
                    <span class="badge rounded-pill px-3 py-2
                        @if($riskRegister->risk_level == 'High') bg-danger
                        @elseif($riskRegister->risk_level == 'Medium') bg-warning text-dark
                        @else bg-success @endif">
                        @if($riskRegister->risk_level == 'High') 🔴 High (Tinggi)
                        @elseif($riskRegister->risk_level == 'Medium') 🟡 Medium (Sedang)
                        @else 🟢 Low (Rendah) @endif
                    </span>
                    <small class="text-muted ms-2">* Skor akan diperbarui otomatis saat disimpan.</small>
                </div>
            </div>

            {{-- BAGIAN 4: Pengendalian --}}
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-shield-halved me-2"></i>Rencana Pengendalian</h6>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mitigasi <span class="text-danger">*</span></label>
                    <textarea name="mitigation_plan" class="form-control @error('mitigation_plan') is-invalid @enderror" rows="3"
                        placeholder="Langkah-langkah untuk meminimalisasi atau mengatasi dampak risiko" required>{{ old('mitigation_plan', $riskRegister->mitigation_plan) }}</textarea>
                    @error('mitigation_plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa-solid fa-cloud-arrow-up me-1 text-success"></i>Upload dokumen pendukung <span class="text-muted fw-normal">(Opsional)</span></label>
                    <input type="url" name="document_link" class="form-control @error('document_link') is-invalid @enderror"
                        placeholder="Tempel link dokumen (Drive / URL) - Contoh: https://drive.google.com/..." value="{{ old('document_link', $riskRegister->document_link) }}">
                    @error('document_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-user-tie me-1 text-primary"></i>PIC (Penanggungjawab) <span class="text-danger">*</span></label>
                        <input type="text" name="pic" class="form-control @error('pic') is-invalid @enderror"
                            placeholder="Nama penanggungjawab penyelesaian risiko ini" value="{{ old('pic', $riskRegister->pic) }}" required>
                        @error('pic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-calendar-check me-1 text-danger"></i>Tanggal Penyelesaian <span class="text-danger">*</span></label>
                        <input type="date" name="target_date" class="form-control @error('target_date') is-invalid @enderror"
                            value="{{ old('target_date', $riskRegister->target_date) }}" required>
                        @error('target_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button type="submit" form="update-form" class="btn btn-warning rounded-pill px-4 shadow-sm fw-semibold text-dark">
                        <i class="fa-solid fa-save me-2"></i>Update Profil Risiko
                    </button>
                    <a href="{{ route('admin.risk-registers.index') }}" class="btn btn-light rounded-pill px-4 ms-2 border">Batal</a>
                </div>
                <button type="submit" form="delete-form" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="fa-solid fa-trash me-2"></i>Hapus Data
                </button>
            </div>
        </form>

        <form id="delete-form" action="{{ route('admin.risk-registers.destroy', $riskRegister->id) }}" method="POST" class="d-none" onsubmit="return confirm('Yakin ingin menghapus entri risiko ini?');">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const standarSelect = document.getElementById('standarMutu');
        const indikatorSelect = document.getElementById('butirTilik');
        const oldIndikator = "{!! addslashes(old('butir_tilik', $riskRegister->butir_tilik)) !!}";

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
    });
</script>
@endpush
