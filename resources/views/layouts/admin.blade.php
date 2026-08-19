<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') - SPMI</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Admin CSS -->
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-bg: #f3f4f6;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-text: #e2e8f0;
            --topbar-bg: #ffffff;
            --accent: #3b82f6;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-bg);
            overflow-x: hidden;
        }
        #wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar-wrapper {
            height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: margin .25s ease-out;
            z-index: 1000;
            position: fixed;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .sidebar-heading {
            padding: 1rem 1.25rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }
        .list-group-item {
            background-color: transparent;
            color: var(--sidebar-text);
            border: none;
            padding: 8px 18px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .list-group-item:hover, .list-group-item.active {
            background-color: var(--sidebar-hover);
            color: white;
            border-left: 3px solid var(--accent);
        }
        .list-group-item i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            color: #94a3b8;
        }
        #sidebar-wrapper .text-xs.text-uppercase.fw-bold.text-muted {
            padding-top: 0.75rem !important;
            padding-bottom: 0.35rem !important;
            font-size: 0.65rem !important;
        }
        .sidebar-toggle {
            cursor: pointer;
            user-select: none;
        }
        .sidebar-toggle .toggle-icon {
            float: right;
            transition: transform 0.25s ease;
        }
        .sidebar-toggle:not(.collapsed) .toggle-icon {
            transform: rotate(180deg);
        }
        .submenu-item {
            padding-left: 42px !important;
            font-size: 0.85rem;
        }
        .submenu-wrapper .list-group-item {
            border-left: 3px solid transparent;
        }
        .list-group-item.active i, .list-group-item:hover i {
            color: var(--accent);
        }
        #page-content-wrapper {
            min-width: 100vw;
            padding-left: var(--sidebar-width);
            transition: padding-left .25s ease-out;
        }
        .topbar {
            background-color: var(--topbar-bg);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 30px;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .card-header-custom {
            background-color: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 24px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 16px;
        }
        
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            #page-content-wrapper {
                padding-left: 0;
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading text-center d-flex align-items-center justify-content-center">
                <img src="{{ asset(setting('logo', 'images/logo.png')) }}" alt="Logo" class="me-2" style="height: 30px; width: auto;">
                {{ setting('institution_short_name', 'SPMI') }}
            </div>
            <div class="list-group list-group-flush mt-3">
                @hasrole('spmi')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Dashboard & Dokumen</div>
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Dashboard Mutu
                </a>
                <a href="{{ route('admin.documents.index', ['module' => 'dokumen_mutu']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/documents*') && request('module') == 'dokumen_mutu' ? 'active' : '' }}">
                    <i class="fa-solid fa-file-pdf"></i> Dokumen Mutu (Kebijakan & Manual)
                </a>

                {{-- P1 — Penetapan --}}
                @php
                    $p1Active = request()->is('admin/standard-decrees*') || request()->is('admin/quality-standards*') || request()->is('admin/checklist-items*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p1Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p1" aria-expanded="{{ $p1Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-stamp"></i> P1 — Penetapan <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p1Active ? 'show' : '' }}" id="spmi-p1">
                    <a href="{{ route('admin.standard-decrees.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/standard-decrees*') ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp"></i> SK Penetapan Standar
                    </a>
                    <a href="{{ route('admin.quality-standards.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/quality-standards*') ? 'active' : '' }}">
                        <i class="fa-solid fa-star"></i> Standar Mutu & Indikator
                    </a>
                    <a href="{{ route('admin.checklist-items.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/checklist-items*') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i> Daftar Tilik (Instrumen)
                    </a>
                </div>

                {{-- P2 — Pelaksanaan --}}
                @php
                    $p2Active = request()->is('admin/risk-registers*') || request()->is('admin/evaluations*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p2Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p2" aria-expanded="{{ $p2Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-arrows-spin"></i> P2 — Pelaksanaan <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p2Active ? 'show' : '' }}" id="spmi-p2">
                    <a href="{{ route('admin.risk-registers.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/risk-registers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-triangle-exclamation"></i> Profil Risiko Institusi
                    </a>
                    <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i> Monitoring Evaluasi Diri (ED)
                    </a>
                </div>

                {{-- P3 — Pengendalian (AMI) --}}
                @php
                    $p3Active = request()->is('admin/audit/cycles*') || request()->is('admin/audit/assignments*') || request()->is('admin/surat-tugas*') || request()->is('admin/kertas-kerja*') || request()->is('admin/surveys*') || request()->is('admin/standard-decrees/auditor*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p3Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p3" aria-expanded="{{ $p3Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-shield-halved"></i> P3 — Pengendalian (AMI) <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p3Active ? 'show' : '' }}" id="spmi-p3">
                    <a href="{{ route('admin.audit.cycles.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/audit/cycles*') ? 'active' : '' }}">
                        <i class="fa-solid fa-rotate"></i> Siklus AMI
                    </a>
                    <a href="{{ route('admin.audit.assignments.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/audit/assignments*') || request()->is('admin/surat-tugas*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-user"></i> Alokasi Auditor & Surat Tugas
                    </a>
                    <a href="{{ route('admin.kertas-kerja.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/kertas-kerja*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i> Pantau Kinerja Auditor
                    </a>
                    <a href="{{ route('admin.surveys.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/surveys*') ? 'active' : '' }}">
                        <i class="fa-solid fa-square-poll-vertical"></i> Manajemen Survei
                    </a>
                    <a href="{{ route('admin.standard-decrees.auditor') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/standard-decrees/auditor*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-user"></i> SK Penugasan Auditor
                    </a>
                </div>

                {{-- P4 — Peningkatan Mutu --}}
                @php
                    $p4Active = request()->is('admin/reports*') || request()->is('admin/pimpinan/rtl*') || request()->is('admin/rtm*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p4Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p4" aria-expanded="{{ $p4Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-chart-line"></i> P4 — Peningkatan Mutu <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p4Active ? 'show' : '' }}" id="spmi-p4">
                    <a href="{{ route('admin.reports.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Laporan AMI
                    </a>
                    <a href="{{ route('admin.pimpinan.rtl') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/pimpinan/rtl*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-check"></i> Tindak Lanjut (RTL)
                    </a>
                    <a href="{{ route('admin.rtm.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/rtm*') ? 'active' : '' }}">
                        <i class="fa-solid fa-landmark"></i> Rapat Tinjauan Manajemen (RTM)
                    </a>
                </div>

                {{-- P5 — Peningkatan Standar --}}
                @php
                    $p5Active = request()->is('admin/quality-standards*') || (request()->is('admin/standard-decrees*') && !request()->is('admin/standard-decrees/auditor*'));
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p5Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p5" aria-expanded="{{ $p5Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-arrow-trend-up"></i> P5 — Peningkatan Standar <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p5Active ? 'show' : '' }}" id="spmi-p5">
                    <a href="{{ route('admin.quality-standards.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/quality-standards*') ? 'active' : '' }}">
                        <i class="fa-solid fa-star"></i> Peninjauan & Revisi Standar
                    </a>
                    <a href="{{ route('admin.standard-decrees.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/standard-decrees*') && !request()->is('admin/standard-decrees/auditor*') ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp"></i> SK Penetapan & Perubahan
                    </a>
                </div>

                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Sistem & Supervisi</div>
                <a href="{{ route('admin.activity-logs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Audit Trail (Log)
                </a>
                @endhasrole

                @hasrole('administrator|prodi|unit|auditor|pimpinan')
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                @endhasrole
                
                @hasrole('administrator')
                <a href="{{ route('admin.documents.index', ['module' => 'dokumen_mutu']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/documents*') && request('module') == 'dokumen_mutu' ? 'active' : '' }}">
                    <i class="fa-solid fa-file-pdf"></i> Dokumen Mutu
                </a>
                @endhasrole

                @hasrole('prodi|unit')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Evaluasi Diri</div>
                <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                    <i class="fa-solid fa-pen-ruler"></i> Isi Evaluasi Diri (ED)
                </a>
                @endhasrole

                @hasrole('auditor')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Evaluasi Diri</div>
                <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                    <i class="fa-solid fa-eye"></i> Lihat Evaluasi Diri (ED)
                </a>
                <a href="{{ route('admin.risk-registers.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/risk-registers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> Risk Register (Read-only)
                </a>
                @endhasrole

                @hasrole('administrator')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Pemantauan Pelaksanaan</div>
                <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Monitoring Evaluasi Diri
                </a>
                @endhasrole

                @hasrole('auditor')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Kertas Kerja</div>
                <a href="{{ route('admin.audit.assignments.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/audit/assignments*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i> Jadwal Penugasan Saya
                </a>
                <a href="{{ route('admin.kertas-kerja.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/kertas-kerja*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list"></i> Kertas Kerja Auditor
                </a>
                <a href="{{ route('admin.panduan-etik.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/panduan-etik*') ? 'active' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i> Panduan Etik Auditor
                </a>
                @endhasrole

                @hasrole('prodi|unit')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">AMI — Pemantauan Mutu</div>
                <a href="{{ route('admin.risk-registers.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/risk-registers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> Risk Register
                </a>
                <a href="{{ route('admin.audit.assignments.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/audit/assignments*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-user"></i> Info Jadwal Audit
                </a>
                <a href="{{ route('admin.rtm.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/rtm*') ? 'active' : '' }}">
                    <i class="fa-solid fa-landmark"></i> Risalah RTM (Disahkan)
                </a>
                @endhasrole

                @hasrole('pimpinan')
                {{-- P1 — Penetapan --}}
                @php
                    $pimpinanP1Active = request()->is('admin/standard-decrees*') && !request()->is('admin/standard-decrees/auditor*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $pimpinanP1Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#pimpinan-p1" aria-expanded="{{ $pimpinanP1Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-stamp"></i> P1 — Penetapan <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $pimpinanP1Active ? 'show' : '' }}" id="pimpinan-p1">
                    <a href="{{ route('admin.standard-decrees.index') }}" class="list-group-item list-group-item-action submenu-item {{ $pimpinanP1Active ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp"></i> SK Penetapan Standar
                    </a>
                </div>

                {{-- P3 — Pengendalian (AMI) --}}
                @php
                    $pimpinanP3Active = request()->is('admin/standard-decrees/auditor*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $pimpinanP3Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#pimpinan-p3" aria-expanded="{{ $pimpinanP3Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-shield-halved"></i> P3 — Pengendalian (AMI) <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $pimpinanP3Active ? 'show' : '' }}" id="pimpinan-p3">
                    <a href="{{ route('admin.standard-decrees.auditor') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/standard-decrees/auditor*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-user"></i> SK Penugasan Auditor
                    </a>
                </div>

                {{-- P4 — Peningkatan Mutu --}}
                @php
                    $pimpinanP4Active = request()->is('admin/rtm*') || request()->is('admin/pimpinan/rtl*') || request()->is('admin/reports*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $pimpinanP4Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#pimpinan-p4" aria-expanded="{{ $pimpinanP4Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-chart-line"></i> P4 — Peningkatan Mutu <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $pimpinanP4Active ? 'show' : '' }}" id="pimpinan-p4">
                    <a href="{{ route('admin.reports.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Laporan AMI &amp; RTM
                    </a>
                    <a href="{{ route('admin.rtm.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/rtm*') ? 'active' : '' }}">
                        <i class="fa-solid fa-landmark"></i> Rapat Tinjauan Manajemen
                    </a>
                    <a href="{{ route('admin.pimpinan.rtl') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/pimpinan/rtl*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-check"></i> Rapat Tindak Lanjut
                    </a>
                </div>
                @endhasrole

                @hasrole('administrator')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Pengaturan</div>
                <a href="{{ route('admin.academic_programs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/academic-programs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building-columns"></i> Program Studi
                </a>
                <a href="{{ route('admin.units.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/units*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i> Unit Kerja
                </a>
                <a href="{{ route('admin.academic_years.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/academic-years*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i> Tahun Akademik
                </a>
                <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Manajemen User
                </a>
                <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i> Konfigurasi Aplikasi
                </a>
                <a href="{{ route('admin.pages.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/pages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-pager"></i> Halaman Publik
                </a>
                @endhasrole

                @hasrole('administrator')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Sistem & Supervisi</div>
                <a href="{{ route('admin.activity-logs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Audit Trail (Log)
                </a>
                @endhasrole

                
                <a href="{{ route('home') }}" class="list-group-item list-group-item-action mt-auto border-top border-secondary">
                    <i class="fa-solid fa-globe"></i> Kembali ke Web
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg topbar d-flex justify-content-between align-items-center">
                <button class="btn btn-light d-md-none" id="menu-toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="fs-5 fw-bold text-muted ms-3 d-none d-md-block">
                    @yield('title', 'Dashboard')
                </div>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle px-3 py-2 rounded" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false" style="background: var(--primary-bg); color: var(--text-color);">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-weight: 600;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;">{{ Auth::user()->name }}</span>
                            <span class="d-block text-muted" style="font-size: 0.75rem;">{{ Auth::user()->roles->pluck('name')->implode(', ') }}</span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="dropdownUser1">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid px-4 py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("menu-toggle").addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("wrapper").classList.toggle("toggled");
        });
    </script>
    @stack('scripts')
</body>
</html>
