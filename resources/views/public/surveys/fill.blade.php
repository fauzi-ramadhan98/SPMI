@extends('layouts.public')

@section('title', 'Isi Survei: ' . $survey->title . ' - ' . config('app.name'))

@section('content')
<div class="bg-primary text-white py-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), #059669);">
    <div class="container py-3 text-center">
        <h1 class="fw-bold display-6">{{ $survey->title }}</h1>
        <p class="text-white-50 mt-2 mb-0"><i class="fa-solid fa-list-check me-1"></i> Partisipasi Survei Layanan SPMI</p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="{{ route('public.surveys.submit', $survey->id) }}" method="POST">
                @csrf
                
                @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                    <div class="d-flex">
                        <i class="fa-solid fa-circle-exclamation fs-4 me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Terdapat Kesalahan</h6>
                            <ul class="mb-0 small ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Intro Card -->
                <div class="card card-custom border-top border-4 border-success shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-3">{{ $survey->title }}</h4>
                        <div class="text-muted" style="line-height: 1.6;">
                            {{ $survey->description }}
                        </div>
                        <hr class="my-4">
                        <div class="d-flex align-items-center text-danger small fw-semibold">
                            * Wajib diisi
                        </div>
                    </div>
                </div>

                <!-- Identitas Responden (If Not Anonymous) -->
                @if(!$survey->is_anonymous)
                <div class="card card-custom shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 px-md-5">
                        <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-address-card me-2"></i> Identitas Responden <span class="text-danger">*</span></h6>
                    </div>
                    <div class="card-body p-4 p-md-5 pt-3">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                            <input type="text" name="respondent_name" class="form-control form-control-lg bg-light border-0" placeholder="Masukkan nama lengkap Anda" value="{{ old('respondent_name') }}" required>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Aktor / Peran</label>
                                <select name="respondent_type" class="form-select form-select-lg bg-light border-0" required>
                                    <option value="" disabled selected>Pilih peran Anda...</option>
                                    <option value="Mahasiswa" {{ old('respondent_type') == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    <option value="Dosen" {{ old('respondent_type') == 'Dosen' ? 'selected' : '' }}>Dosen / Tenaga Pendidik</option>
                                    <option value="Tendik" {{ old('respondent_type') == 'Tendik' ? 'selected' : '' }}>Staf Administrasi (Tendik)</option>
                                    <option value="Alumni" {{ old('respondent_type') == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                    <option value="Mitra" {{ old('respondent_type') == 'Mitra' ? 'selected' : '' }}>Mitra / Pengguna Lulusan</option>
                                    <option value="Lainnya" {{ old('respondent_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Email (Opsional)</label>
                                <input type="email" name="respondent_email" class="form-control form-control-lg bg-light border-0" placeholder="alamat@email.com" value="{{ old('respondent_email') }}">
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <input type="hidden" name="respondent_name" value="Anonim">
                <input type="hidden" name="respondent_type" value="Anonim">
                <div class="alert alert-info bg-opacity-10 shadow-sm border-info border-opacity-25 border rounded-3 mb-4 d-flex align-items-center">
                    <i class="fa-solid fa-user-secret fs-3 text-info me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-info">Survei Anonim</h6>
                        <p class="mb-0 small text-muted">Identitas Anda dirahasiakan sepenuhnya. Jawaban Anda murni untuk kebutuhan evaluasi internal kami.</p>
                    </div>
                </div>
                @endif

                <!-- Pertanyaan Survei -->
                @foreach($survey->questions as $index => $q)
                <div class="card card-custom shadow-sm mb-4 {{ $errors->has('q_'.$q->id) ? 'border border-danger' : 'border-0' }}">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4">
                            <h6 class="fw-bold fs-5 text-dark mb-1">{{ $index + 1 }}. {{ $q->question_text }} @if($q->is_required)<span class="text-danger">*</span>@endif</h6>
                            @if($errors->has('q_'.$q->id))
                                <div class="text-danger small mt-1 fw-semibold"><i class="fa-solid fa-circle-exclamation me-1"></i> Pertanyaan ini wajib diisi</div>
                            @endif
                        </div>
                        
                        <div class="question-container ps-1">
                            @if($q->question_type == 'rating')
                                <div class="d-flex justify-content-between align-items-center mb-2 px-2 text-muted small fw-bold">
                                    <span>Sangat Kurang</span>
                                    <span>Sangat Baik</span>
                                </div>
                                <div class="rating-group d-flex justify-content-between gap-2">
                                    @for($i = 1; $i <= 4; $i++)
                                        <input type="radio" class="btn-check" name="q_{{ $q->id }}" id="q_{{ $q->id }}_{{ $i }}" value="{{ $i }}" {{ old('q_'.$q->id) == $i ? 'checked' : '' }} {{ $q->is_required ? 'required' : '' }}>
                                        <label class="btn btn-outline-primary shadow-sm flex-fill py-3 fw-bold fs-5 rounded-3 rating-label" for="q_{{ $q->id }}_{{ $i }}">{{ $i }}</label>
                                    @endfor
                                </div>
                            @elseif($q->question_type == 'text')
                                <textarea name="q_{{ $q->id }}" class="form-control form-control-lg bg-light border-0" rows="3" placeholder="Tulis jawaban Anda di sini..." {{ $q->is_required ? 'required' : '' }}>{{ old('q_'.$q->id) }}</textarea>
                            @elseif($q->question_type == 'multiple_choice')
                                <div class="d-flex flex-column gap-3">
                                    @foreach($q->options as $optIndex => $opt)
                                    <div class="form-check custom-radio align-items-center h-100">
                                        <input class="form-check-input ms-0 shadow-sm" type="radio" name="q_{{ $q->id }}" id="q_{{ $q->id }}_{{ $optIndex }}" value="{{ $opt }}" {{ old('q_'.$q->id) == $opt ? 'checked' : '' }} {{ $q->is_required ? 'required' : '' }} style="width: 1.25em; height: 1.25em; margin-top: 0.2em;">
                                        <label class="form-check-label ps-2 fs-6 w-100 cursor-pointer text-dark" for="q_{{ $q->id }}_{{ $optIndex }}">
                                            {{ $opt }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @elseif($q->question_type == 'checkbox')
                                <div class="d-flex flex-column gap-3">
                                    @foreach($q->options as $optIndex => $opt)
                                    <div class="form-check custom-checkbox align-items-center h-100">
                                        <input class="form-check-input ms-0 shadow-sm" type="checkbox" name="q_{{ $q->id }}[]" value="{{ $opt }}" id="q_{{ $q->id }}_{{ $optIndex }}" {{ (is_array(old('q_'.$q->id)) && in_array($opt, old('q_'.$q->id))) ? 'checked' : '' }} style="width: 1.25em; height: 1.25em; margin-top: 0.2em;">
                                        <label class="form-check-label ps-2 fs-6 w-100 cursor-pointer text-dark" for="q_{{ $q->id }}_{{ $optIndex }}">
                                            {{ $opt }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Submit Button -->
                <div class="d-flex justify-content-between align-items-center mt-5">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm" onclick="history.back()"><i class="fa-solid fa-arrow-left me-2"></i> Batal</button>
                    <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow">
                        Kirim Jawaban <i class="fa-regular fa-paper-plane ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .rating-label { transition: all 0.2s; border-width: 2px; }
    .btn-check:checked + .rating-label { transform: scale(1.05); background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; color: white !important; }
    .custom-radio:hover, .custom-checkbox:hover { background-color: #f8fafc; border-radius: 8px; }
    .form-check-label.cursor-pointer { cursor: pointer; display: block; padding-top: 3px; padding-bottom: 3px; }
</style>
@endpush
