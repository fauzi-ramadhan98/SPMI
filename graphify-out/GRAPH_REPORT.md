# Graph Report - C:\xampp\htdocs\spmi-mardira  (2026-08-07)

## Corpus Check
- 60 files · ~89,634 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 817 nodes · 1351 edges · 168 communities (162 shown, 6 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 72 edges (avg confidence: 0.81)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Controller Web Inti
- Manajemen Pengguna
- Prodi & Unit
- Modul Survei
- Dependensi Composer
- Modul Evaluasi & ED
- Instrumen & Daftar Tilik
- Manajemen Siklus Audit
- Frontend JS Dependencies
- Skrip Setup & Deploy
- Blueprint AMI PPEPP
- Dokumen & E-Filing
- Penugasan Audit
- Risk Register
- Halaman Website
- Temuan Audit
- Instrumen Audit
- Dashboard per Role
- Service Provider
- Unit Tests

## God Nodes (most connected - your core abstractions)
1. `Controller` - 50 edges
2. `AuditAssignment` - 43 edges
3. `User` - 42 edges
4. `Evaluation` - 39 edges
5. `AuditCycle` - 34 edges
6. `Unit` - 33 edges
7. `QualityStandard` - 30 edges
8. `AcademicProgram` - 29 edges
9. `EvaluationController` - 27 edges
10. `AuditFinding` - 24 edges

## Surprising Connections (you probably didn't know these)
- `Tech Stack: Laravel` --conceptually_related_to--> `Laravel Web Framework`  [INFERRED]
  docs/arsitektur_audit.md → README.md
- `setting()` --calls--> `Setting`  [INFERRED]
  app/helpers.php → app/Models/Setting.php
- `AcademicProgramController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/AcademicProgramController.php → app/Http/Controllers/Controller.php
- `AuditAssignmentController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/AuditAssignmentController.php → app/Http/Controllers/Controller.php
- `AuditCycleController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Admin/AuditCycleController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Lima Modul Siklus PPEPP** — docs_arsitektur_audit_modul_penetapan, docs_arsitektur_audit_modul_pelaksanaan, docs_arsitektur_audit_modul_evaluasi, docs_arsitektur_audit_modul_pengendalian, docs_arsitektur_audit_modul_peningkatan [EXTRACTED 1.00]
- **Sumber Data Integrasi Eksternal** — docs_arsitektur_audit_integrasi_siakadmi, docs_arsitektur_audit_integrasi_pddikti, docs_arsitektur_audit_integrasi_sister, docs_arsitektur_audit_integrasi_sinta [EXTRACTED 1.00]

## Communities (168 total, 6 thin omitted)

### Community 0 - "Controller Web Inti"
Cohesion: 0.05
Nodes (19): setting(), ActivityLogController, KertasKerjaController, SettingController, NewsController, ActivityLog, bootLogsActivity(), getLoggableAttributes() (+11 more)

### Community 1 - "Manajemen Pengguna"
Cohesion: 0.06
Nodes (18): UserController, User, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable (+10 more)

### Community 2 - "Prodi & Unit"
Cohesion: 0.06
Nodes (14): AcademicProgramController, UnitController, AcademicProgram, Unit, AcademicProgramSeeder, AdminUserSeeder, DatabaseSeeder, DocumentCategorySeeder (+6 more)

### Community 3 - "Modul Survei"
Cohesion: 0.06
Nodes (19): SurveyAnalyticsController, SurveyController, ConfirmPasswordController, ForgotPasswordController, LoginController, RegisterController, ResetPasswordController, VerificationController (+11 more)

### Community 4 - "Dependensi Composer"
Cohesion: 0.04
Nodes (46): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, files, psr-4, config (+38 more)

### Community 5 - "Modul Evaluasi & ED"
Cohesion: 0.12
Nodes (4): EvaluationController, Evaluation, EvaluationItem, EvaluationSeeder

### Community 6 - "Instrumen & Daftar Tilik"
Cohesion: 0.10
Nodes (7): ImportChecklistFromDescription, ChecklistItemController, QualityStandardController, ChecklistItem, QualityStandard, StandardVersion, Illuminate\Console\Command

### Community 7 - "Manajemen Siklus Audit"
Cohesion: 0.14
Nodes (6): AuditCycleController, PimpinanController, ReportController, SuratTugasController, AuditCycle, Illuminate\Http\Request

### Community 8 - "Frontend JS Dependencies"
Cohesion: 0.08
Nodes (25): axios, bootstrap, concurrently, laravel-vite-plugin, devDependencies, axios, bootstrap, concurrently (+17 more)

### Community 9 - "Skrip Setup & Deploy"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 10 - "Blueprint AMI PPEPP"
Cohesion: 0.10
Nodes (25): Aplikasi AMI Berbasis Risiko & PPEPP, Analytics & Trend Mutu, API Gateway & Sinkronisasi, Dashboard RTM, E-Kertas Kerja (Instrumen Audit), Generator LHA, Integrasi PD DIKTI (Feeder), Integrasi siakadmi (+17 more)

### Community 11 - "Dokumen & E-Filing"
Cohesion: 0.16
Nodes (4): DocumentController, PublicDocumentController, Document, DocumentCategory

### Community 14 - "Halaman Website"
Cohesion: 0.18
Nodes (3): PageController, HomeController, Page

### Community 15 - "Temuan Audit"
Cohesion: 0.26
Nodes (3): AuditFindingController, FindingAttachmentController, AuditFinding

## Knowledge Gaps
- **72 isolated node(s):** `$schema`, `private`, `type`, `build`, `dev` (+67 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **6 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Modul Survei` to `Controller Web Inti`, `Manajemen Pengguna`, `Prodi & Unit`, `Instrumen & Daftar Tilik`, `Manajemen Siklus Audit`, `Dokumen & E-Filing`, `Penugasan Audit`, `Halaman Website`, `Temuan Audit`, `Instrumen Audit`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Why does `User` connect `Manajemen Pengguna` to `Controller Web Inti`, `Prodi & Unit`, `Modul Survei`, `Modul Evaluasi & ED`, `Instrumen & Daftar Tilik`, `Penugasan Audit`, `Dashboard per Role`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `AuditAssignment` connect `Penugasan Audit` to `Controller Web Inti`, `Manajemen Pengguna`, `Manajemen Siklus Audit`, `Risk Register`, `Temuan Audit`, `Instrumen Audit`, `Dashboard per Role`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 8 inferred relationships involving `User` (e.g. with `.genericDashboard()` and `.systemDashboard()`) actually correct?**
  _`User` has 8 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `private`, `type` to the rest of the system?**
  _72 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Controller Web Inti` be split into smaller, more focused modules?**
  _Cohesion score 0.05194805194805195 - nodes in this community are weakly interconnected._
- **Should `Manajemen Pengguna` be split into smaller, more focused modules?**
  _Cohesion score 0.05734767025089606 - nodes in this community are weakly interconnected._