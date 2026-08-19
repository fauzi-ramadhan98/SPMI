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
                        <label class="form-label fw-bold">Standar Mutu Terkait <span class="text-danger">*</span></label>
                        <select name="standar_mutu" id="standarMutu" class="form-select @error('standar_mutu') is-invalid @enderror" required>
                            <option value="">-- Pilih Standar Mutu --</option>
                            @foreach($standards as $std)
                                <option value="{{ $std->name }}" data-description="{{ htmlspecialchars($std->description) }}" {{ old('standar_mutu') == $std->name ? 'selected' : '' }}>
                                    {{ $std->kode_standar ? $std->kode_standar . ' - ' : '' }}{{ $std->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Standar SPMI yang berkaitan dengan risiko ini.</div>
                        @error('standar_mutu') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <label class="form-label fw-bold"><i class="fa-brands fa-google-drive me-1 text-success"></i>Bukti Dokumen (Link GDrive) <span class="text-muted fw-normal">(Opsional)</span></label>
                    <input type="url" name="document_link" class="form-control @error('document_link') is-invalid @enderror"
                        placeholder="Contoh: https://drive.google.com/..." value="{{ old('document_link') }}">
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

            const description = selectedOption.getAttribute('data-description');
            if (description) {
                // Split by newline to break multi-line descriptors
                const lines = description.split('\n');
                lines.forEach(line => {
                    // Extract strings that contain IKU or IKT text
                    const cleanLine = line.replace(/^---\s*$/, '').trim();
                    if (cleanLine && (cleanLine.startsWith('IKU') || cleanLine.startsWith('IKT'))) {
                        const opt = document.createElement('option');
                        opt.value = cleanLine;
                        opt.textContent = cleanLine;
                        if (oldIndikator && oldIndikator === cleanLine) opt.selected = true;
                        indikatorSelect.appendChild(opt);
                    }
                });
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
