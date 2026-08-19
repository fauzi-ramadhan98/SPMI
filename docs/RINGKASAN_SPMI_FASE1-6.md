# Ringkasan Implementasi SPMI Mardira — Fase 1–7

Dokumen ringkasan perbaikan aplikasi agar selaras dengan arsitektur Role & Menu
(Permendiktisaintek No. 39 Tahun 2025). Seluruh fase sudah terverifikasi lewat
test fitur dan pengujian manual di browser (`http://127.0.0.1:8000`).

## Status Verifikasi

- **Test: 24/24 PASS** (64 asersi) — `php artisan test`
- **Verifikasi per role**: semua 7 akun login, dashboard sesuai role render `200`,
  dan halaman menu terkait render `200`.

| Role | Akun | Dashboard | Cek menu (200) |
|------|------|-----------|----------------|
| administrator | `admin@spmi.ac.id` | Dashboard Sistem | `/admin/settings` |
| spmi | `spmi@spmi.ac.id` | Dashboard Mutu Institusi | `/admin/activity-logs` |
| pimpinan (ketua) | `ketua@spmi.ac.id` | Executive Dashboard | `/admin/pimpinan/rtl` |
| pimpinan (wakil) | `wakil@spmi.ac.id` | Executive Dashboard | `/admin/pimpinan/rtm` |
| auditor | `auditor@spmi.ac.id` | Dashboard Auditor | `/admin/audit/assignments` |
| prodi | `ti@spmi.ac.id` | Dashboard Kinerja | `/admin/evaluations` |
| unit | `unitpuslia@spmi.ac.id` | Dashboard Kinerja | `/admin/documents` |

Password semua akun: `password`.

> Catatan: akun unit di DB bernama `unitpuslia@spmi.ac.id` (bukan `unit@spmi.ac.id`
> yang ada di `AdminUserSeeder`) karena DB ini masih menyimpan data seeder lama.

## Ringkasan per Fase

### Fase 1A — Konfigurasi Aplikasi (Settings)
- Migration `create_settings_table`, model `Setting` (`value()`, `set()`), helper
  `setting()` di `app/helpers.php` (registrasi `files` di `composer.json`).
- `SettingController` + view `admin/settings/index.blade.php`, `SettingSeeder`,
  route `admin.settings.index/update`, menu sidebar.
- Logo & nama institusi di-wire ke `layouts/{admin,public}.blade.php`.

### Fase 1B — Audit Trail
- Migration `create_activity_logs_table`, model `ActivityLog`.
- Trait `app/Models/Concerns/LogsActivity.php` diterapkan ke **12 model**
  (User, AcademicProgram, Unit, Document, AuditCycle, AuditAssignment,
  AuditFinding, Evaluation, QualityStandard, RiskRegister, Survey, ChecklistItem).
- `ActivityLogController` + view `admin/log_activity/index.blade.php`,
  route `admin.activity-logs.index` (role administrator|spmi).

### Fase 1C + 2 — Dashboard per Role
- `DashboardController` di-refactor menjadi `systemDashboard`,
  `institusiDashboard`, `prodiDashboard`, `auditorDashboard`,
  `executiveDashboard`, `genericDashboard`.
- View di `resources/views/admin/dashboards/{system,institusi,prodi,auditor,executive}.blade.php`.
- Relasi morphMany `evaluations()` ditambahkan ke `AcademicProgram` & `Unit`.

### Fase 3 — Risk Heatmap + BI Pimpinan
- `executiveDashboard` menyediakan `riskMatrix` 5×5 (probabilitas × dampak)
  dan `findingsByProgram` (join `audit_assignments`).
- Bar chart temuan per unit/prodi + heatmap merah/kuning/hijau (Chart.js CDN 4.4.1).

### Fase 4 — Tanggapan Temuan (Setuju / Tolak)
- Migration `add_prodi_decision_to_audit_findings_table`
  (prodi_decision, prodi_decision_note, prodi_decision_at).
- `AuditFindingController::update` — auditee mengisi keputusan; catatan **wajib** saat tolak.
- Selector keputusan di view `findings/index.blade.php`.

### Fase 5 — Versioning Standar
- Migration `add_version_to_quality_standards_and_create_standard_versions` +
  model `StandardVersion`.
- `QualityStandard.version` + relasi `versions()`; snapshot dibuat saat
  `QualityStandardController::update`.
- Badge "Rev." di index, tabel riwayat revisi di edit.

### Fase 6 — ED Prodi dari Instrumen SPMI + Panduan Etik
- Migration `add_checklist_item_id_to_evaluation_items_table`.
- `EvaluationController::generateFromChecklist` (dedup via `checklist_item_id`,
  filter standar aktif + parameter).
- Route `admin.evaluations.generate-from-checklist` + tombol di `evaluations/edit.blade.php`.
- `KertasKerjaController::panduanEtik` + route `admin.panduan-etik.index` +
  menu sidebar + view `admin/panduan_etik/index.blade.php`.

### Fase 7 — SK Penetapan Standar (Modul Penetapan P1)
- Entitas `StandardDecree` (tabel `standard_decrees`): `sk_no`, `judul`, `deskripsi`,
  `status` (draft/ditetapkan), `issued_by`, `issued_at`, `prepared_by`, plus field
  manual SK: `audit_cycle_id`, `nama_standar`, `lokasi`, `tanggal_sk`, `kop_path`,
  `signature_path`.
- Kolom `standard_decree_id` pada `quality_standards` — satu SK meratifikasi banyak standar.
- Alur sesuai praktik SPMI:
  - **SPMI** membuat/mengedit draf SK: isi Nomor, Tentang, Nama Standar, Tanggal,
    Lokasi, pilih Siklus AMI (sumber Lampiran Daftar Nama Auditor) & standar yang dicakup.
  - **Pimpinan (Ketua)** memeriksa halaman Koreksi (`review`), menyimpan koreksi isi
    (`review-update`), lalu **Tandatangani & Tetapkan** (`verify`) → TTD ditempel otomatis
    dari `images/TTD-KETUA.jpg`, `status=ditetapkan`, terkunci dari edit/hapus.
  - **PDF SK** dihasilkan DomPDF (`standard-decrees/{decree}/pdf`): kop `images/kopstmik.jpg`,
    isi manual, klausul Menimbang/Mengingat/Menetapkan, blok tanda tangan + TTD,
    halaman Lampiran tabel NO/NIK/NAMA/JABATAN dari auditor siklus terpilih.
- Menu sidebar: spmi "SK Penetapan Standar"; pimpinan seksi **Modul Penetapan (P1)**.
- Badge "SK: [sk_no]" pada daftar Standar Mutu + tombol PDF di index.
- Test: `StandardDecreeTest` (8 asersi alur + otorisasi + guard + generate PDF).

## Migration
5 tabel baru + 3 kolom + 6 kolom SK (sudah jalan via `php artisan migrate --force`):
- `settings`
- `activity_logs`
- `standard_versions` (+ kolom `version` pada `quality_standards`)
- kolom `prodi_decision*` pada `audit_findings`
- kolom `checklist_item_id` pada `evaluation_items`
- `standard_decrees`
- kolom `standard_decree_id` pada `quality_standards`
- 6 kolom manual SK pada `standard_decrees`
  (`audit_cycle_id`, `nama_standar`, `lokasi`, `tanggal_sk`, `kop_path`, `signature_path`)

## Test Fitur (tests/Feature)
- `SettingsPageTest` — admin lihat & update settings
- `ActivityLogTest` — log dibuat pada model
- `DashboardTest` — 5 peran dashboard
- `FindingDecisionTest` — setuju/tolak auditee, tolak wajib catatan
- `StandardVersioningTest` — snapshot & bump versi
- `EvaluationFromChecklistTest` — generate ED + panduan etik
- `StandardDecreeTest` — alur SK Penetapan (SPMI buat draf → pimpinan koreksi/tandatangani/tetapkan → PDF)

## Catatan Operasional
- `composer dump-autoload` menggantung >2 mnt → gunakan
  `composer dump-autoload --no-scripts -q` lalu bypass.
- Test pakai SQLite `:memory:` + `RefreshDatabase`; harness wajib seed
  `RoleSeeder` di `setUp`.
- `ExampleTest` bawaan sempat gagal (GET / tanpa data) → sudah diperbaiki dengan
  `RefreshDatabase + $this->seed()`.

## Output graphify
- `graphify-out/graph.html` — visual interaktif
- `graphify-out/graph.json` — 817 node, 1351 edge, 168 komunitas
- `graphify-out/GRAPH_REPORT.md` — laporan audit