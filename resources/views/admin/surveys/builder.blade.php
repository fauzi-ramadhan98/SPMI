@extends('layouts.admin')

@section('title', 'Survey Builder: ' . $survey->title)

@section('content')
<div class="row">
    <!-- Survey Info Box -->
    <div class="col-lg-4 mb-4">
        <div class="card card-custom shadow-sm border-0 h-100">
            <div class="card-header-custom bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-circle-info me-2"></i> Detail Survei</h6>
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-sm btn-light rounded-circle text-muted" title="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
            </div>
            <div class="card-body p-4 pt-3">
                <div class="mb-4">
                    <h5 class="fw-bold text-dark">{{ $survey->title }}</h5>
                    <p class="text-muted small mb-0">{{ Str::limit($survey->description, 150) }}</p>
                </div>
                
                <ul class="list-group list-group-flush small text-muted">
                    <li class="list-group-item px-0 pb-2 border-light d-flex justify-content-between">
                        <span class="fw-semibold">Kategori</span>
                        <span class="text-dark bg-light px-2 py-1 rounded">{{ str_replace('_', ' ', $survey->type) }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2 border-light d-flex justify-content-between">
                        <span class="fw-semibold">Privasi</span>
                        @if($survey->is_anonymous)
                            <span class="text-secondary bg-light px-2 py-1 rounded"><i class="fa-solid fa-user-secret me-1"></i> Anonim</span>
                        @else
                            <span class="text-primary bg-light px-2 py-1 rounded"><i class="fa-solid fa-address-card me-1"></i> Terbuka</span>
                        @endif
                    </li>
                    <li class="list-group-item px-0 py-2 border-light d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Status Live</span>
                        @if($survey->is_active)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">Aktif</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">Non-Aktif</span>
                        @endif
                    </li>
                    <li class="list-group-item px-0 pt-2 border-0 d-flex flex-column">
                        <span class="fw-semibold mb-2">Tautan Publik:</span>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control text-muted bg-light border-0" value="{{ route('public.surveys.fill', $survey->id) }}" id="surveyLnk" readonly>
                            <button class="btn btn-outline-primary shadow-sm rounded-end border" type="button" onclick="copyLink()">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Questions Builder -->
    <div class="col-lg-8 mb-4">
        <!-- Add New Question Form -->
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-success mb-4">
            <div class="card-header-custom bg-white border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0 text-success"><i class="fa-solid fa-plus-circle me-2"></i> Tambah Pertanyaan Baru</h6>
            </div>
            <div class="card-body p-4 pt-3">
                <form action="{{ route('admin.surveys.questions.store', $survey->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Isi Pertanyaan <span class="text-danger">*</span></label>
                        <input type="text" name="question_text" class="form-control bg-light border-0" required placeholder="Tuliskan pertanyaan Anda...">
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Tipe Jawaban <span class="text-danger">*</span></label>
                            <select name="question_type" id="qType" class="form-select bg-light border-0" required onchange="toggleOptions()">
                                <option value="rating">Rating (1-4) LIKERT</option>
                                <option value="text">Teks Bebas (Esai Singkat)</option>
                                <option value="multiple_choice">Pilihan Ganda (Satu Jawaban)</option>
                                <option value="checkbox">Kotak Centang (Lebih dari satu)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end mb-2">
                            <div class="form-check form-switch w-100 bg-light p-2 px-3 rounded ms-1 mt-auto">
                                <input class="form-check-input ms-0 mt-1 shadow-sm" type="checkbox" role="switch" id="isReq" name="is_required" value="1" checked>
                                <label class="form-check-label fw-bold small ms-2 text-dark" for="isReq">Wajib Diisi (Required)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4 d-none" id="optionsBox">
                        <label class="form-label text-muted small fw-bold">Pilihan Jawaban (Pisahkan dengan koma)</label>
                        <textarea name="options" class="form-control bg-light border-0" rows="2" placeholder="Sangat Baik, Baik, Cukup, Kurang"></textarea>
                        <div class="form-text text-info small"><i class="fa-solid fa-circle-info me-1"></i> Contoh: Pilihan A, Pilihan B, Pilihan C</div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success fw-semibold rounded-pill px-4 shadow-sm"><i class="fa-solid fa-plus me-2"></i> Tambahkan ke Form</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview / Question List -->
        <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="fa-solid fa-list-check me-2 text-primary"></i> Preview Kuesioner ({{ $questions->count() }} Pertanyaan)</h6>
        
        <div class="d-flex flex-column gap-3">
            @forelse($questions as $index => $q)
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 position-relative">
                    <!-- Delete floating button -->
                    <form action="{{ route('admin.surveys.questions.destroy', [$survey->id, $q->id]) }}" method="POST" class="position-absolute top-0 end-0 mt-3 me-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" title="Hapus Pertanyaan" onclick="return confirm('Hapus pertanyaan ini?')">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                    
                    <h6 class="fw-bold mb-2">
                        <span class="text-primary me-1">{{ $index + 1 }}.</span> {{ $q->question_text }}
                        @if($q->is_required)
                            <span class="text-danger">*</span>
                        @endif
                    </h6>
                    
                    <div class="mt-3 ps-4 border-start border-2 border-light">
                        @if($q->question_type == 'rating')
                            <div class="d-flex gap-2 text-muted small mt-1">
                                <span class="bg-light px-3 py-1 rounded fw-semibold border text-center flex-fill">1<br><span style="font-size: 0.65rem;">Sangat Kurang</span></span>
                                <span class="bg-light px-3 py-1 rounded fw-semibold border text-center flex-fill">2</span>
                                <span class="bg-light px-3 py-1 rounded fw-semibold border text-center flex-fill">3</span>
                                <span class="bg-light px-3 py-1 rounded fw-semibold border text-center flex-fill">4<br><span style="font-size: 0.65rem;">Sangat Baik</span></span>
                            </div>
                        @elseif($q->question_type == 'text')
                            <div class="form-control bg-light border-0 text-muted opacity-50 py-3">Area teks jawaban responden...</div>
                        @elseif($q->question_type == 'multiple_choice' || $q->question_type == 'checkbox')
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted small">
                                @if(is_array($q->options))
                                    @foreach($q->options as $opt)
                                    <li><i class="fa-regular {{ $q->question_type == 'checkbox' ? 'fa-square' : 'fa-circle' }} me-2 text-primary"></i> {{ $opt }}</li>
                                    @endforeach
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center bg-white p-5 rounded-4 shadow-sm border border-light">
                <i class="fa-solid fa-file-circle-plus text-muted opacity-25 mb-3" style="font-size: 3rem;"></i>
                <h6 class="fw-bold text-dark">Kuesioner Masih Kosong</h6>
                <p class="text-muted small mb-0">Tambahkan pertanyaan pertama Anda melalui form di atas untuk mulai menyusun survei.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleOptions() {
        const type = document.getElementById('qType').value;
        const box = document.getElementById('optionsBox');
        if(type === 'multiple_choice' || type === 'checkbox') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }
    
    function copyLink() {
        const copyText = document.getElementById("surveyLnk");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        
        // Optional: show small toast/alert
        alert("Tautan survei disalin ke clipboard!");
    }
</script>
@endpush
