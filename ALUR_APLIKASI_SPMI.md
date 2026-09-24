# Alur Aplikasi SPMI - Sistem Penjaminan Mutu Internal

## 🎯 Ringkasan Arsitektur
- **Tech Stack**: Laravel 12, PHP 8.2, MySQL/SQLite, Bootstrap 5, FontAwesome
- **Pattern**: RBAC (spatie/laravel-permission) + PPEPP Cycle (5 modul)
- **God Nodes**: `Controller` (50 edges), `AuditAssignment` (43), `User` (42), `Evaluation` (39), `AuditCycle` (34)

---

## 👥 Role & Akses Menu (Sidebar Dinamis)

| Role | Deskripsi | Modul Utama (PPEPP) |
|------|-----------|---------------------|
| **super_admin** | Akses penuh | Semua |
| **administrator** | Kelola master data | Pengaturan: Prodi, Unit, Tahun Akademik, User, Konfigurasi, Halaman, Audit Trail |
| **spmi** | Operator SPMI penuh | **P1-P5 lengkap**: Standar, Siklus AMI, Alokasi Auditor, Kertas Kerja, Survei, Laporan, RTM/RTL, SK Penetapan |
| **pimpinan** | Ketua/Wakil - Persetujuan | **P1**: SK Penetapan Standar<br>**P3**: SK Penugasan Auditor<br>**P4**: Laporan AMI & RTM, RTM, RTL |
| **prodi** | Kaprodi - Isi ED | Evaluasi Diri (Isi ED), Risk Register, Info Jadwal Audit |
| **unit** | Kepala Unit - Isi ED | Evaluasi Diri (Isi ED), Risk Register, Info Jadwal Audit |
| **auditor** | Auditor - Kertas Kerja | Jadwal Penugasan, Kertas Kerja, Panduan Etik, Lihat ED (read-only), Risk Register (read-only) |

---

## 🔄 Siklus PPEPP (5 Modul)

```
┌─────────────────────────────────────────────────────────────────────┐
│                    SIKLUS PPEPP - SPMI                              │
├─────────────┬─────────────┬─────────────┬─────────────┬─────────────┤
│   P1        │   P2        │   E         │   P3        │   P4/P5     │
│  PENETAPAN  │ PELAKSANAAN │  EVALUASI   │ PENGENDALIAN│ PENINGKATAN │
├─────────────┼─────────────┼─────────────┼─────────────┼─────────────┤
│ SK Standar  │ Alokasi     │ Isi ED      │ SK Auditor  │ RTM & RTL   │
│ Mutu        │ Auditor     │ (Prodi/Unit)│ (Pimpinan)  │ Laporan AMI │
│ Risk Register│ Surat Tugas│ Generator  │ Kertas Kerja│ Dashboard   │
│ SK Perubahan│ Instrumen   │ LHA         │ Survei      │ Revisi      │
│             │ Evidence    │ Temuan OB/KTS│             │ Standar     │
└─────────────┴─────────────┴─────────────┴─────────────┴─────────────┘
```

---

## 📋 Workflow Data Utama

### 1. Penetapan Standar (P1)
```
SPMI: Buat SK Draf → Pilih Standar → Simpan Draf
  ↓
Pimpinan: Koreksi (Review) → Tetapkan (TTD otomatis) → Status: Ditetapkan
  ↓
SPMI: Upload File SK Resmi (PDF) → Tersedia untuk Download
```

### 2. Pelaksanaan AMI (P2)
```
SPMI: Buat Siklus AMI → Alokasi Auditor per Prodi/Unit
  ↓
SPMI: Generate Surat Tugas & Jadwal Visitasi (PDF)
  ↓
Auditor: Isi Kertas Kerja (Checklist, Temuan, Evidence)
  ↓
SPMI: Pantau Kinerja Auditor (Progress, Temuan)
```

### 3. Evaluasi Diri (E)
```
SPMI: Setup Daftar Tilik (Checklist) per Standar
  ↓
Prodi/Unit: Isi Evaluasi Diri (ED) per item checklist
  ↓
Sistem: Generate LHA (Laporan Hasil Evaluasi) otomatis
  ↓
Pimpinan/SPMI: Lihat & Validasi LHA
```

### 4. Pengendalian (P3) - AMI
```
SPMI: Rencanakan Audit → SK Penugasan Auditor (Pimpinan tetapkan)
  ↓
Auditor: Lakukan Audit → Input Temuan (KTS/OB) di Kertas Kerja
  ↓
SPMI: Tarik Temuan AMI → Buat RTM (Rapat Tinjauan Manajemen)
  ↓
Pimpinan: Sahkan RTM → Instruksi Tindak Lanjut
```

### 5. Peningkatan (P4/P5)
```
Pimpinan: RTL (Rencana Tindak Lanjut) → Catatan per Temuan
  ↓
SPMI/Prodi: Lakukan Perbaikan → Update Status RTL
  ↓
SPMI: Revisi Standar Mutu (P5) → SK Perubahan Standar
```

---

## 🗂️ Entitas Inti (Models & Relasi)

| Model | Deskripsi | Relasi Kunci |
|-------|-----------|--------------|
| `User` | Pengguna + Roles | `hasRoles`, `academicProgram`, `unit` |
| `AcademicProgram` | Prodi/S1/S2/D3 | `hasMany(User)`, `hasMany(AuditAssignment)` |
| `Unit` | Unit Kerja | `hasMany(User)`, `hasMany(AuditAssignment)` |
| `AuditCycle` | Siklus AMI (Tahun/Semester) | `hasMany(AuditAssignment)`, `hasMany(RtmMeeting)` |
| `AuditAssignment` | Penugasan Auditor | `belongsTo(AuditCycle, Prodi, Unit, User)` |
| `AuditInstrument` | Instrumen/Checklist per Assignment | `belongsTo(AuditAssignment)` |
| `AuditFinding` | Temuan (KTS/OB) | `belongsTo(AuditAssignment)` |
| `QualityStandard` | Standar Mutu (IKU/IKT) | `belongsTo(StandardDecree)` |
| `StandardDecree` | SK Penetapan (jenis: standar/auditor) | `hasMany(QualityStandard)`, `belongsTo(AuditCycle)` |
| `RiskRegister` | Profil Risiko per Prodi | `belongsTo(AcademicProgram)` |
| `Evaluation` | Evaluasi Diri (ED) | `belongsTo(AcademicProgram/Unit)`, `hasMany(EvaluationItem)` |
| `Document` | E-Filing (Kategori: dokumen_mutu, dll) | `belongsTo(DocumentCategory, AuditCycle)` |
| `RtmMeeting` | Rapat Tinjauan Manajemen | `belongsTo(AuditCycle)`, `hasMany(RtmInstruction)` |

---

## 🛣️ Route Utama (web.php)

```php
// Dashboard per Role
/admin/dashboard → DashboardController@index (role-aware)

// P1 - Penetapan
/admin/standard-decrees → StandardDecreeController@index (spmi|pimpinan|admin)
/admin/standard-decrees/auditor → StandardDecreeController@auditorIndex (spmi|pimpinan)

// P2 - Pelaksanaan
/admin/audit/cycles → AuditCycleController
/admin/audit/assignments → AuditAssignmentController
/admin/surat-tugas → SuratTugasController
/admin/kertas-kerja → KertasKerjaController

// E - Evaluasi
/admin/evaluations → EvaluationController (spmi|auditor|prodi|unit|pimpinan)
/admin/risk-registers → RiskRegisterController
/admin/checklist-items → ChecklistItemController

// P3 - Pengendalian
/admin/rtm → RtmController
/admin/pimpinan/rtl → PimpinanController@rtl

// P4/P5 - Peningkatan
/admin/reports → ReportController
/admin/quality-standards → QualityStandardController

// Dokumen & E-Filing
/admin/documents → DocumentController
/admin/public/documents → PublicDocumentController

// Survei
/admin/surveys → SurveyController
```

---

## 🔐 Middleware & Permission

```php
// Role-based middleware di routes/web.php
'spmi' → Kelola draf SK, CRUD Standar, Alokasi Auditor, RTM
'pimpinan' → Review SK, Tetapkan SK, Sahkan RTM, RTL, Lihat Laporan
'prodi|unit' → Isi ED, Risk Register (milik sendiri)
'auditor' → Kertas Kerja, Lihat ED (read-only), Risk Register (read-only)
'administrator' → Master data, User management, Konfigurasi
```

---

## 📊 Dashboard per Role (DashboardController)

| Role | View | Fitur Utama |
|------|------|-------------|
| spmi | `admin.dashboards.spmi` | Statistik SK, Siklus AMI, Temuan, RTM, Survei |
| pimpinan | `admin.dashboards.pimpinan` | SK Menunggu TTD, RTM Belum Disahkan, RTL Open |
| prodi | `admin.dashboards.prodi` | ED Progress, Risk Register, Jadwal Audit Prodi |
| unit | `admin.dashboards.unit` | ED Progress, Risk Register, Jadwal Audit Unit |
| auditor | `admin.dashboards.auditor` | Penugasan Saya, Kertas Kerja Progress, Temuan |
| administrator | `admin.dashboards.administrator` | User Stats, System Health, Audit Trail |

---

## ⚡ Fitur Khusus Baru (Perbaikan Terbaru)

1. **SK Auditor Terpisah** — Menu `SK Penugasan Auditor` di P3, filter `jenis=auditor` di `standard_decrees`
2. **Upload SK Hanya SPMI** — Pimpinan hanya `Tetapkan`, SPMI yang upload file resmi
3. **Sidebar Pimpinan Ramping** — Sembunyikan "Lihat ED", tambah "Laporan AMI & RTM" di P4
4. **Tarik Temuan AMI** — SPMI tarik temuan KTS dari siklus ke RTM via AJAX
5. **Agenda RTM Hierarkis** — Format per auditee dengan badge KTS/OB

---

## 🚀 Cara Jalankan

```bash
cd C:\xampp\htdocs\spmi-mardira
php artisan serve --port=8000
# Akses: http://127.0.0.1:8000
# Login pimpinan: ketua@spmi.ac.id / password
```

---

**Graphify Insight**: Node `Controller` (50 edges) & `User` (42 edges) jadi bridge terpenting — semua role & modul lewat sini. `AuditAssignment` (43 edges) menghubungkan siklus audit, penugasan, temuan, hingga RTL.