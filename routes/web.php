<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicDocumentController;
use App\Http\Controllers\PublicSurveyController;
use App\Http\Controllers\NewsController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DocumentCategoryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\AuditCycleController;
use App\Http\Controllers\Admin\AuditAssignmentController;
use App\Http\Controllers\Admin\AuditInstrumentController;
use App\Http\Controllers\Admin\AuditFindingController;
use App\Http\Controllers\Admin\SurveyController;
use App\Http\Controllers\Admin\SurveyAnalyticsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReportBuilderController;
use App\Http\Controllers\Admin\AcademicProgramController;
use App\Http\Controllers\Admin\QualityStandardController;
use App\Http\Controllers\Admin\ChecklistItemController;
use App\Http\Controllers\Admin\RiskRegisterController;
use App\Http\Controllers\Admin\PimpinanController;
use App\Http\Controllers\Admin\RtmController;
use App\Http\Controllers\Admin\SopController;
use App\Http\Controllers\Admin\SuratTugasController;
use App\Http\Controllers\Admin\KertasKerjaController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\FindingAttachmentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\StandardDecreeController;
use App\Http\Controllers\Admin\RevisiStandarController;
use App\Http\Controllers\Admin\AcademicYearController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sambutan-kepala', [HomeController::class, 'sambutan'])->name('sambutan');
Route::get('/profil', [HomeController::class, 'profil'])->name('profil');
Route::get('/halaman/{slug}', [HomeController::class, 'generic'])->name('page.generic');

Route::get('/dokumen-publik', [PublicDocumentController::class, 'index'])->name('public.documents.index');
Route::get('/dokumen-publik/download/{document}', [PublicDocumentController::class, 'download'])->name('public.documents.download');

Route::get('/berita', [NewsController::class, 'index'])->name('public.news.index');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('public.news.show');

Route::get('/survei', [PublicSurveyController::class, 'index'])->name('public.surveys.index');
Route::get('/survei/{id}/isi', [PublicSurveyController::class, 'fill'])->name('public.surveys.fill');
Route::post('/survei/{id}/submit', [PublicSurveyController::class, 'submit'])->name('public.surveys.submit');


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
// Auth routes without registration
Auth::routes([
    'register' => false,
]);

/*
|--------------------------------------------------------------------------
| Admin / Authenticated Routes
|
| Struktur peran (Permendiktisaintek No. 39 Tahun 2025):
|   administrator : pengelola sistem (user, master prodi/unit, halaman)
|   spmi          : LPM/SPMI (standar mutu, siklus, alokasi, survei, laporan)
|   pimpinan      : ketua & wakil (persetujuan/pemantauan mutu)
|   auditor       : tim auditor (borang/instrumen, temuan, verifikasi)
|   prodi / unit  : auditee (profil risiko, tindak lanjut temuan, dokumen)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Dashboard (semua role terautentikasi)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dokumen SPMI — semua role terautentikasi melihat; prodi/unit dibatasi ke miliknya
    Route::resource('documents', DocumentController::class)->except(['show'])->middleware(['role:administrator|spmi|auditor|prodi|unit|pimpinan']);
    // Approval route for leader (pimpinan)
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])
        ->name('documents.approve')
        ->middleware(['role:pimpinan|administrator']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download')
        ->middleware(['role:administrator|spmi|auditor|prodi|unit|pimpinan']);

    // Surat Tugas & Jadwal Visitasi AMI (index/jadwal: SPMI; generate: + auditor utk miliknya)
    Route::get('surat-tugas', [SuratTugasController::class, 'index'])->name('surat-tugas.index')->middleware(['role:spmi|administrator|auditor']);
    Route::get('surat-tugas/generate/{assignment}', [SuratTugasController::class, 'generate'])->name('surat-tugas.generate')->middleware(['role:spmi|administrator|auditor']);
    Route::get('surat-tugas/jadwal/{cycle}', [SuratTugasController::class, 'generateSchedule'])->name('surat-tugas.jadwal')->middleware(['role:spmi|administrator']);

    // ADMINISTRATOR — master data & konfigurasi sistem
    Route::middleware(['role:administrator'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('units', UnitController::class);
        Route::resource('document-categories', DocumentCategoryController::class)->except(['show']);
        Route::resource('pages', PageController::class);
        Route::resource('academic-years', AcademicYearController::class)->names([
            'index'   => 'academic_years.index',
            'create'  => 'academic_years.create',
            'store'   => 'academic_years.store',
            'edit'    => 'academic_years.edit',
            'update'  => 'academic_years.update',
            'destroy' => 'academic_years.destroy',
        ]);

        // Konfigurasi Aplikasi (Super Admin)
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('academic-programs', [AcademicProgramController::class, 'index'])->name('academic_programs.index');
        Route::get('academic-programs/{academicProgram}/edit', [AcademicProgramController::class, 'edit'])->name('academic_programs.edit');
        Route::put('academic-programs/{academicProgram}', [AcademicProgramController::class, 'update'])->name('academic_programs.update');
    });

    // Audit Trail (Super Admin & SPMI)
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index')
        ->middleware(['role:administrator|spmi']);

    // SPMI — modul penjaminan mutu (P1-P4)
    Route::middleware(['role:spmi'])->group(function () {
        // Modul Penetapan (P1): Standar Mutu (index & download ada di grup semua role)
        Route::get('quality-standards/download-template', [QualityStandardController::class, 'downloadTemplate'])->name('quality-standards.download-template');

        // Modul Peningkatan Standar (P5.1): Peninjauan versi + aksi Revisi
        Route::get('revisi-standar', [RevisiStandarController::class, 'index'])->name('revisi-standar.index');
        Route::resource('quality-standards', QualityStandardController::class)->except(['index']);

        // Modul Penetapan (P1): SK Penetapan Standar — CRUD draf oleh SPMI (index/sign di grup peran)
        Route::resource('standard-decrees', StandardDecreeController::class)
            ->except(['show', 'index'])
            ->parameters(['standard-decrees' => 'decree']);

        // Modul Pelaksanaan (P2): Siklus AMI (index ada di grup semua role)
        Route::resource('audit/cycles', AuditCycleController::class)->names([
            'create'  => 'audit.cycles.create',
            'store'   => 'audit.cycles.store',
            'show'    => 'audit.cycles.show',
            'edit'    => 'audit.cycles.edit',
            'update'  => 'audit.cycles.update',
            'destroy' => 'audit.cycles.destroy',
        ])->except(['index']);

        // Modul Evaluasi (E): Master Daftar Tilik (index & generate ada di grup lain)
        Route::post('checklist-items', [ChecklistItemController::class, 'store'])->name('checklist-items.store');
        Route::put('checklist-items/{checklistItem}', [ChecklistItemController::class, 'update'])->name('checklist-items.update');
        Route::delete('checklist-items/{checklistItem}', [ChecklistItemController::class, 'destroy'])->name('checklist-items.destroy');

        // Modul Pelaksanaan (P2): Alokasi Auditor (CRUD oleh SPMI/LPM)
        Route::get('audit/assignments/create', [AuditAssignmentController::class, 'create'])->name('audit.assignments.create');
        Route::post('audit/assignments', [AuditAssignmentController::class, 'store'])->name('audit.assignments.store');
        Route::delete('audit/assignments/{assignment}', [AuditAssignmentController::class, 'destroy'])->name('audit.assignments.destroy');

        // Survei & Analisis
        Route::resource('surveys', SurveyController::class);
        Route::get('surveys/{survey}/builder', [SurveyController::class, 'builder'])->name('surveys.builder');
        Route::post('surveys/{survey}/questions', [SurveyController::class, 'storeQuestion'])->name('surveys.questions.store');
        Route::delete('surveys/{survey}/questions/{question}', [SurveyController::class, 'destroyQuestion'])->name('surveys.questions.destroy');
        Route::get('surveys/{survey}/analytics', [SurveyAnalyticsController::class, 'show'])->name('surveys.analytics');
    });

    // AUDITOR & SPMI — pengisian borang, temuan, sinkronisasi risiko
    Route::middleware(['role:spmi|auditor'])->group(function () {
        Route::post('audit/assignments/{assignment}/status', [AuditAssignmentController::class, 'updateStatus'])->name('audit.assignments.update-status');
        Route::post('audit/instruments/{assignment}/generate-master', [AuditInstrumentController::class, 'generateFromMaster'])->name('audit.instruments.generate-master');
        Route::post('audit/instruments/{assignment}/sync-risk', [AuditInstrumentController::class, 'syncFromRisk'])->name('audit.instruments.sync-risk');
        Route::post('audit/instruments/{assignment}', [AuditInstrumentController::class, 'store'])->name('audit.instruments.store');
        Route::put('audit/instruments/{instrument}', [AuditInstrumentController::class, 'update'])->name('audit.instruments.update');
        Route::delete('audit/instruments/{instrument}', [AuditInstrumentController::class, 'destroy'])->name('audit.instruments.destroy');

        Route::post('audit/findings/{assignment}', [AuditFindingController::class, 'store'])->name('audit.findings.store');
        Route::delete('audit/findings/{finding}', [AuditFindingController::class, 'destroy'])->name('audit.findings.destroy');
    });

    // SPMI, AUDITOR, PRODI, UNIT, PIMPINAN — ED (SPMI isi; prodi/auditor/unit/pimpinan lihat), risiko & tindak lanjut temuan
    Route::middleware(['role:spmi|auditor|prodi|unit|pimpinan'])->group(function () {
        Route::put('audit/findings/{finding}', [AuditFindingController::class, 'update'])->name('audit.findings.update');
        Route::patch('risk-registers/{risk_register}/document-link', [RiskRegisterController::class, 'updateDocumentLink'])->name('risk-registers.update-document-link');
        Route::get('risk-registers/template', [RiskRegisterController::class, 'downloadTemplate'])->name('risk-registers.template');
        Route::post('risk-registers/import', [RiskRegisterController::class, 'import'])->name('risk-registers.import');
        Route::post('risk-registers/bulk', [RiskRegisterController::class, 'bulkStore'])->name('risk-registers.bulk');
        Route::get('risk-registers/create', [RiskRegisterController::class, 'create'])->name('risk-registers.create');
        Route::post('risk-registers', [RiskRegisterController::class, 'store'])->name('risk-registers.store');
        Route::get('risk-registers/{riskRegister}/edit', [RiskRegisterController::class, 'edit'])->name('risk-registers.edit');
        Route::put('risk-registers/{riskRegister}', [RiskRegisterController::class, 'update'])->name('risk-registers.update');
        Route::delete('risk-registers/{riskRegister}', [RiskRegisterController::class, 'destroy'])->name('risk-registers.destroy');

        // SPMI — validasi (approve) & beri catatan revisi pada profil risiko Risk Owner
        Route::post('risk-registers/{riskRegister}/validate', [RiskRegisterController::class, 'validateRisk'])->name('risk-registers.validate')->middleware('role:spmi');
        Route::post('risk-registers/{riskRegister}/note', [RiskRegisterController::class, 'note'])->name('risk-registers.note')->middleware('role:spmi');

        // Poin catatan client — SPMI menugaskan Prodi/Unit isi Risk Register tiap semester
        Route::post('risk-registers/assignments', [RiskRegisterController::class, 'assignStore'])->name('risk-registers.assignments.store')->middleware('role:spmi');
        Route::delete('risk-registers/assignments/{assignment}', [RiskRegisterController::class, 'assignDestroy'])->name('risk-registers.assignments.destroy')->middleware('role:spmi');

        // Bukti perbaikan per temuan (RTL)
        Route::post('audit/findings/{finding}/attachments', [FindingAttachmentController::class, 'store'])->name('audit.findings.attachments.store');
        Route::delete('audit/findings/{finding}/attachments/{attachment}', [FindingAttachmentController::class, 'destroy'])->name('audit.findings.attachments.destroy');

        // Evaluasi Diri (Prodi/Unit isi; SPMI/auditor verifikasi & lihat)
        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('evaluations/create', [EvaluationController::class, 'create'])->name('evaluations.create');
        Route::post('evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
        Route::get('evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('evaluations/{evaluation}/items', [EvaluationController::class, 'updateItems'])->name('evaluations.items.update');
        Route::post('evaluations/{evaluation}/items', [EvaluationController::class, 'storeItem'])->name('evaluations.items.store');
        Route::post('evaluations/{evaluation}/generate-from-checklist', [EvaluationController::class, 'generateFromChecklist'])->name('evaluations.generate-from-checklist');
        Route::delete('evaluations/{evaluation}/items/{item}', [EvaluationController::class, 'destroyItem'])->name('evaluations.items.destroy');
        Route::post('evaluations/{evaluation}/attachments', [EvaluationController::class, 'storeAttachment'])->name('evaluations.attachments.store');
        Route::delete('evaluations/{evaluation}/attachments/{attachment}', [EvaluationController::class, 'destroyAttachment'])->name('evaluations.attachments.destroy');
        Route::post('evaluations/{evaluation}/submit', [EvaluationController::class, 'submit'])->name('evaluations.submit');
        Route::post('evaluations/{evaluation}/verify', [EvaluationController::class, 'verify'])->name('evaluations.verify');
        Route::delete('evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');
    });

    // Kertas Kerja Auditor — daftar penugasan + bukti ED + Surat Tugas
    Route::get('kertas-kerja', [KertasKerjaController::class, 'index'])->name('kertas-kerja.index')->middleware(['role:spmi|auditor|administrator']);
    Route::get('panduan-etik', [KertasKerjaController::class, 'panduanEtik'])->name('panduan-etik.index')->middleware(['role:spmi|auditor|administrator']);

    // Semua peran (kecuali guest) — pemantauan & halaman index
    Route::middleware(['role:spmi|auditor|prodi|unit|pimpinan'])->group(function () {
        Route::get('audit/assignments', [AuditAssignmentController::class, 'index'])->name('audit.assignments.index');
        Route::get('audit/instruments/{assignment}', [AuditInstrumentController::class, 'index'])->name('audit.instruments.index');
        Route::get('audit/findings/{assignment}', [AuditFindingController::class, 'index'])->name('audit.findings.index');
        Route::get('risk-registers', [RiskRegisterController::class, 'index'])->name('risk-registers.index');
        Route::get('audit/cycles', [AuditCycleController::class, 'index'])->name('audit.cycles.index');
        Route::get('checklist-items', [ChecklistItemController::class, 'index'])->name('checklist-items.index');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/ami-pdf', [ReportController::class, 'generateAmiPdf'])->name('reports.ami_pdf');
        // Generate Laporan AMI — daftar laporan, lampiran terurut, arsip PDF
        Route::get('reports/generated', [ReportBuilderController::class, 'index'])->name('reports.generated.index');
        Route::post('reports/generated', [ReportBuilderController::class, 'store'])->name('reports.generated.store');
        Route::get('reports/generated/{report}', [ReportBuilderController::class, 'show'])->name('reports.generated.show');
        Route::post('reports/generated/{report}/attachments', [ReportBuilderController::class, 'storeAttachment'])->name('reports.generated.attachments.store');
        Route::post('reports/generated/{report}/attachments/{attachment}/sort', [ReportBuilderController::class, 'sort'])->name('reports.generated.attachments.sort');
        Route::delete('reports/generated/{report}/attachments/{attachment}', [ReportBuilderController::class, 'destroyAttachment'])->name('reports.generated.attachments.destroy');
        Route::post('reports/generated/{report}/generate', [ReportBuilderController::class, 'generate'])->name('reports.generated.generate');
        Route::get('reports/generated/{report}/download', [ReportBuilderController::class, 'download'])->name('reports.generated.download');
        Route::delete('reports/generated/{report}', [ReportBuilderController::class, 'destroy'])->name('reports.generated.destroy');
        // Level 3 review detail untuk SPMI (atasan audit)
        Route::get('reports/{assignment}/review', [ReportController::class, 'reviewDetail'])->name('reports.review')->middleware('role:spmi|administrator');
        Route::post('reports/{assignment}/approve', [ReportController::class, 'approveLha'])->name('reports.approve')->middleware('role:spmi|administrator');
        Route::post('reports/{assignment}/reminder', [ReportController::class, 'reminderAuditor'])->name('reports.reminder')->middleware('role:spmi|administrator');
        Route::post('reports/{assignment}/decide', [ReportController::class, 'decideRtl'])->name('reports.decide')->middleware('role:spmi|administrator');
    });

    // Standar Mutu — index dapat dilihat (kecuali prodi); CRUD khusus spmi
    Route::middleware(['role:spmi|auditor|unit|pimpinan'])->group(function () {
        Route::get('quality-standards', [QualityStandardController::class, 'index'])->name('quality-standards.index');
        Route::get('quality-standards/{qualityStandard}/download-file', [QualityStandardController::class, 'downloadFile'])->name('quality-standards.download-file');
    });

    // PIMPINAN & SPMI — Modul Pengendalian (P3): RTM & RTL (SPMI lihat read-only, tulis khusus pimpinan)
    Route::get('pimpinan/rtl', [PimpinanController::class, 'rtl'])->name('pimpinan.rtl')->middleware(['role:pimpinan|spmi']);
    Route::middleware(['role:pimpinan'])->group(function () {
        Route::put('pimpinan/rtl/{finding}/note', [PimpinanController::class, 'rtlNote'])->name('pimpinan.rtl.note');

        // Modul Penetapan (P1): koreksi isi & tanda tangani/tetapkan SK (TTD otomatis)
        Route::get('standard-decrees/{decree}/review', [StandardDecreeController::class, 'review'])->name('standard-decrees.review');
        Route::put('standard-decrees/{decree}/review', [StandardDecreeController::class, 'reviewUpdate'])->name('standard-decrees.review-update');
        Route::post('standard-decrees/{decree}/verify', [StandardDecreeController::class, 'verify'])->name('standard-decrees.verify');
        Route::post('standard-decrees/{decree}/reject', [StandardDecreeController::class, 'reject'])->name('standard-decrees.reject');
    });

    // RTM — Rapat Tinjauan Manajemen (role-aware):
    //   SPMI      : operator penuh (CRUD jadwal, peserta, notulensi, instruksi)
    //   Pimpinan  : read-only + executive summary + Sahkan/Approve
    //   Prodi/Unit: read-only, hanya risalah DISAHKAN berisi instruksi utk dirinya
    Route::middleware(['role:spmi'])->group(function () {
        Route::get('rtm/create', [RtmController::class, 'create'])->name('rtm.create');
        Route::get('rtm/findings/{cycle}', [RtmController::class, 'cycleFindings'])->name('rtm.findings');
        Route::post('rtm', [RtmController::class, 'store'])->name('rtm.store');
        Route::get('rtm/{rtm}/edit', [RtmController::class, 'edit'])->name('rtm.edit');
        Route::put('rtm/{rtm}', [RtmController::class, 'update'])->name('rtm.update');
        Route::delete('rtm/{rtm}', [RtmController::class, 'destroy'])->name('rtm.destroy');
        Route::post('rtm/{rtm}/notulensi', [RtmController::class, 'updateNotulensi'])->name('rtm.notulensi');
        Route::post('rtm/{rtm}/instructions', [RtmController::class, 'storeInstruction'])->name('rtm.instructions.store');
        Route::delete('rtm/instructions/{instruction}', [RtmController::class, 'destroyInstruction'])->name('rtm.instructions.destroy');
    });
    Route::middleware(['role:spmi|pimpinan|prodi|unit|auditor'])->group(function () {
        Route::get('rtm', [RtmController::class, 'index'])->name('rtm.index');
        Route::get('rtm/{rtm}/pdf', [RtmController::class, 'pdf'])->name('rtm.pdf');
        Route::get('rtm/{rtm}', [RtmController::class, 'show'])->name('rtm.show');
    });
    Route::middleware(['role:pimpinan'])->group(function () {
        Route::post('rtm/{rtm}/approve', [RtmController::class, 'approve'])->name('rtm.approve');
    });

    // SK PENETAPAN — index, PDF & unduh (SPMI, Pimpinan, Administrator)
    Route::middleware(['role:spmi|pimpinan|administrator'])->group(function () {
        Route::get('standard-decrees', [StandardDecreeController::class, 'index'])->name('standard-decrees.index');
        Route::get('standard-decrees/auditor', [StandardDecreeController::class, 'auditorIndex'])->name('standard-decrees.auditor');

        // Modul Peningkatan Standar (P5.2): daftar SK Perubahan (hasil revisi)
        Route::get('standard-decrees/perubahan', [StandardDecreeController::class, 'perubahanIndex'])->name('standard-decrees.perubahan');
        Route::get('standard-decrees/{decree}/pdf', [StandardDecreeController::class, 'pdf'])->name('standard-decrees.pdf');
        Route::get('standard-decrees/{decree}/download-file', [StandardDecreeController::class, 'downloadFile'])->name('standard-decrees.download-file');
    });
    // Unggah file SK — khusus SPMI & Administrator (upload dokumen final bukan beban pimpinan)
    Route::middleware(['role:spmi|administrator'])->group(function () {
        Route::post('standard-decrees/{decree}/upload-file', [StandardDecreeController::class, 'uploadFile'])->name('standard-decrees.upload-file');
    });

    // SOP UNIT — Pengajuan & Review SOP (Unit buat, SPMI review)
    Route::middleware(['role:unit|spmi|administrator|super_admin'])->group(function () {
        Route::get('sops', [SopController::class, 'index'])->name('sops.index');
    });
    Route::middleware(['role:unit'])->group(function () {
        Route::get('sops/create', [SopController::class, 'create'])->name('sops.create');
        Route::post('sops', [SopController::class, 'store'])->name('sops.store');
        Route::post('sops/{sop}/upload-revision', [SopController::class, 'uploadRevision'])->name('sops.upload-revision');
    });
    Route::middleware(['role:unit|spmi|administrator|super_admin'])->group(function () {
        Route::get('sops/{sop}', [SopController::class, 'show'])->name('sops.show');
    });
    Route::middleware(['role:spmi|administrator|super_admin'])->group(function () {
        Route::get('sops/{sop}/review', [SopController::class, 'review'])->name('sops.review');
        Route::post('sops/{sop}/review', [SopController::class, 'reviewStore'])->name('sops.review');
    });
    // Download/Preview SOP — unit (milik sendiri), spmi|admin (semua)
    Route::middleware(['role:unit|spmi|administrator|super_admin'])->group(function () {
        Route::get('sops/{sop}/download', [SopController::class, 'download'])->name('sops.download');
        Route::get('sops/{sop}/preview', [SopController::class, 'preview'])->name('sops.preview');
    });

});
