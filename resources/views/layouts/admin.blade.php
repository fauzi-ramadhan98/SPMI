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

        /* Modern Clean / Sneat-like Admin Refresh */
        :root {
            --sidebar-width: 280px;
            --primary-bg: #f5f7fb;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #eef4ff;
            --sidebar-text: #566a7f;
            --topbar-bg: rgba(255, 255, 255, 0.92);
            --accent: #0f3a8b;
            --accent-soft: #e8f0ff;
            --heading-color: #1f2937;
            --muted-color: #8492a6;
            --border-color: #e6eaf0;
            --shadow-sm: 0 0.125rem 0.375rem rgba(67, 89, 113, 0.10);
            --shadow-md: 0 0.25rem 1rem rgba(67, 89, 113, 0.12);
        }

        body {
            background: var(--primary-bg);
            color: #334155;
        }

        #wrapper {
            min-height: 100vh;
        }

        #sidebar-wrapper {
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            border-right: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            padding: 0.75rem;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: #d9dee8;
            border-radius: 999px;
        }

        .sidebar-heading {
            background: linear-gradient(135deg, var(--accent), #2563eb);
            border: 0;
            border-radius: 1rem;
            min-height: 68px;
            box-shadow: 0 0.5rem 1.25rem rgba(15, 58, 139, 0.18);
        }

        #sidebar-wrapper .text-xs.text-uppercase.fw-bold.text-muted,
        #sidebar-wrapper .px-3.pt-4.pb-2.text-xs {
            color: #9aa7b8 !important;
            letter-spacing: 0.08em;
            margin-top: 0.5rem;
        }

        #sidebar-wrapper .list-group {
            gap: 0.2rem;
        }

        #sidebar-wrapper .list-group-item {
            color: var(--sidebar-text);
            border-radius: 0.75rem;
            padding: 0.7rem 0.9rem;
            border-left: 0;
            margin-bottom: 0.1rem;
        }

        #sidebar-wrapper .list-group-item:hover,
        #sidebar-wrapper .list-group-item.active {
            background: var(--sidebar-hover);
            color: var(--accent);
            border-left: 0;
        }

        #sidebar-wrapper .list-group-item.active {
            font-weight: 700;
            box-shadow: inset 0 0 0 1px rgba(15, 58, 139, 0.08);
        }

        #sidebar-wrapper .list-group-item i {
            color: #94a3b8;
        }

        #sidebar-wrapper .list-group-item:hover i,
        #sidebar-wrapper .list-group-item.active i {
            color: var(--accent);
        }

        .submenu-item {
            padding-left: 2.85rem !important;
            font-size: 0.86rem;
        }

        #page-content-wrapper {
            min-width: 0;
            width: 100%;
            padding-left: var(--sidebar-width);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 900;
            background: var(--topbar-bg);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(230, 234, 240, 0.8);
            box-shadow: var(--shadow-sm);
            min-height: 72px;
        }

        .card,
        .card-custom {
            border-width: 0;
            border-radius: 1rem !important;
            box-shadow: var(--shadow-sm) !important;
        }

        .card-header,
        .card-header-custom {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color) !important;
            border-radius: 1rem 1rem 0 0 !important;
            font-weight: 700;
            color: var(--heading-color);
        }

        .btn,
        .form-control,
        .form-select,
        .input-group-text,
        .dropdown-menu,
        .alert {
            border-radius: 0.75rem;
        }

        .table {
            vertical-align: middle;
        }

        .table thead th {
            color: var(--muted-color);
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .admin-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 990;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(2px);
        }

        /* Final compact Sneat-like admin scale */
        :root {
            --sidebar-width: 280px;
            --admin-radius: 0.625rem;
            --admin-radius-lg: 0.875rem;
            --admin-control-height: 2.375rem;
            --admin-font-size: 0.875rem;
            --admin-small-font-size: 0.8125rem;
        }

        html {
            font-size: 15px;
        }

        body {
            font-size: var(--admin-font-size);
            line-height: 1.45;
            color: #566a7f;
        }

        #sidebar-wrapper {
            transition: margin-left 0.25s ease, box-shadow 0.25s ease;
        }

        #page-content-wrapper {
            transition: padding-left 0.25s ease;
        }

        .topbar {
            min-height: 64px;
            padding: 0.65rem 1.5rem;
        }

        .admin-menu-toggle {
            width: 2.375rem;
            height: 2.375rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color) !important;
            color: var(--accent) !important;
            background: #fff !important;
            box-shadow: 0 0.125rem 0.25rem rgba(67, 89, 113, 0.08);
        }

        .admin-page-title {
            font-size: 1rem !important;
            line-height: 1.25;
            color: #566a7f !important;
        }

        h1, .h1 { font-size: 1.625rem; }
        h2, .h2 { font-size: 1.375rem; }
        h3, .h3 { font-size: 1.1875rem; }
        h4, .h4 { font-size: 1.0625rem; }
        h5, .h5 { font-size: 1rem; }
        h6, .h6 { font-size: 0.875rem; }

        .container-fluid {
            max-width: 1440px;
        }
    </style>

    <!-- Unified admin component sizing (loaded last to normalize Bootstrap variants) -->
    <link rel="stylesheet" href="{{ asset('css/admin-consistency.css') . '?v=' . filemtime(public_path('css/admin-consistency.css')) }}">
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
@php
                    // Satu set menu per user — prioritas sama dengan DashboardController
                    // (administrator > pimpinan > spmi > auditor > prodi/unit)
                    // agar user multi-role tidak menampilkan sidebar bertumpuk/dobel.
                    $sideUser = auth()->user();
                    $sideProfile = $sideUser->hasRole('administrator') ? 'administrator'
                        : ($sideUser->hasRole('pimpinan') ? 'pimpinan'
                        : ($sideUser->hasRole('spmi') ? 'spmi'
                        : ($sideUser->hasRole('auditor') ? 'auditor'
                        : ($sideUser->hasAnyRole(['prodi', 'unit']) ? 'auditee' : 'guest'))));
                @endphp

                @if($sideProfile === 'spmi')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Dashboard & Dokumen</div>
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Dashboard Mutu
                </a>
                <a href="{{ route('admin.documents.index', ['module' => 'dokumen_mutu']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/documents*') && request('module') == 'dokumen_mutu' ? 'active' : '' }}">
                    <i class="fa-solid fa-file-pdf"></i> Dokumen SPMI
                </a>

                {{-- P1 — Penetapan --}}
                @php
                    // P1: halaman penetapan saja — kecuali SK auditor (P3) & SK perubahan (P5.2)
                    $p1Active = (request()->is('admin/standard-decrees*') && !request()->is('admin/standard-decrees/auditor*') && !request()->is('admin/standard-decrees/perubahan*'))
                        || request()->is('admin/quality-standards*') || request()->is('admin/checklist-items*') || request()->is('admin/sops*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p1Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p1" aria-expanded="{{ $p1Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-stamp"></i> P1 — Penetapan <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p1Active ? 'show' : '' }}" id="spmi-p1">
                    <a href="{{ route('admin.standard-decrees.index') }}" class="list-group-item list-group-item-action submenu-item {{ (request()->is('admin/standard-decrees*') && !request()->is('admin/standard-decrees/auditor*') && !request()->is('admin/standard-decrees/perubahan*')) ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp"></i> SK Penetapan Standar
                    </a>
                    <a href="{{ route('admin.quality-standards.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/quality-standards*') ? 'active' : '' }}">
                        <i class="fa-solid fa-star"></i> Standar Mutu & Indikator
                    </a>
                    <a href="{{ route('admin.checklist-items.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/checklist-items*') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i> Daftar Tilik (Instrumen)
                    </a>
                    <a href="{{ route('admin.sops.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/sops*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-check"></i> Validasi SOP Unit
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
                    // P5: halaman revisi (P5.1) & daftar SK perubahan (P5.2) — terpisah dari P1
                    $p5Active = request()->is('admin/revisi-standar*') || request()->is('admin/standard-decrees/perubahan*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $p5Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#spmi-p5" aria-expanded="{{ $p5Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-arrow-trend-up"></i> P5 — Peningkatan Standar <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $p5Active ? 'show' : '' }}" id="spmi-p5">
                    <a href="{{ route('admin.revisi-standar.index') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/revisi-standar*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i> Peninjauan & Revisi Standar
                    </a>
                    <a href="{{ route('admin.standard-decrees.perubahan') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/standard-decrees/perubahan*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrows-rotate"></i> SK Perubahan Standar
                    </a>
                </div>

                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Sistem & Supervisi</div>
                <a href="{{ route('admin.activity-logs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Audit Trail (Log)
                </a>
@endif

@if(in_array($sideProfile, ['administrator', 'pimpinan', 'auditor', 'auditee']))
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
@endif
                
@if($sideProfile === 'administrator')
                <a href="{{ route('admin.documents.index', ['module' => 'dokumen_mutu']) }}" class="list-group-item list-group-item-action {{ request()->is('admin/documents*') && request('module') == 'dokumen_mutu' ? 'active' : '' }}">
                    <i class="fa-solid fa-file-pdf"></i> Dokumen Mutu
                </a>
@endif

@if($sideProfile === 'auditee')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Evaluasi Diri</div>
                <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                    <i class="fa-solid fa-pen-ruler"></i> Isi Evaluasi Diri (ED)
                </a>
@endif

@if($sideProfile === 'auditee' && $sideUser->hasRole('unit'))
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Manajemen SOP</div>
                <a href="{{ route('admin.sops.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/sops*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-contract"></i> Manajemen SOP Unit
                </a>
@endif

@if($sideProfile === 'auditor')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Evaluasi Diri</div>
                <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                    <i class="fa-solid fa-eye"></i> Lihat Evaluasi Diri (ED)
                </a>
                <a href="{{ route('admin.risk-registers.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/risk-registers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> Risk Register (Read-only)
                </a>
@endif

@if($sideProfile === 'administrator')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Pemantauan Pelaksanaan</div>
                <a href="{{ route('admin.evaluations.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/evaluations*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Monitoring Evaluasi Diri
                </a>
@endif

@if($sideProfile === 'auditor')
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
@endif

@if($sideProfile === 'auditee')
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
@endif

@if($sideProfile === 'pimpinan')
                {{-- P1 — Penetapan --}}
                @php
                    $pimpinanP1Active = request()->is('admin/standard-decrees*') && !request()->is('admin/standard-decrees/auditor*');
                @endphp
                <a class="list-group-item list-group-item-action sidebar-toggle {{ $pimpinanP1Active ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#pimpinan-p1" aria-expanded="{{ $pimpinanP1Active ? 'true' : 'false' }}">
                    <i class="fa-solid fa-stamp"></i> P1 — Penetapan <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse submenu-wrapper {{ $pimpinanP1Active ? 'show' : '' }}" id="pimpinan-p1">
                    <a href="{{ route('admin.standard-decrees.index') }}" class="list-group-item list-group-item-action submenu-item {{ $pimpinanP1Active && !request()->is('admin/standard-decrees/perubahan*') ? 'active' : '' }}">
                        <i class="fa-solid fa-stamp"></i> SK Penetapan Standar
                    </a>
                    <a href="{{ route('admin.standard-decrees.perubahan') }}" class="list-group-item list-group-item-action submenu-item {{ request()->is('admin/standard-decrees/perubahan*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrows-rotate"></i> SK Perubahan Standar
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
@endif

@if($sideProfile === 'administrator')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Pengaturan</div>
                <a href="{{ route('admin.academic_programs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/academic-programs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building-columns"></i> Program Studi
                </a>
                <a href="{{ route('admin.units.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/units*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i> Unit Kerja
                </a>
                <a href="{{ route('admin.document-categories.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/document-categories*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i> Kategori Dokumen
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
@endif

@if($sideProfile === 'administrator')
                <div class="px-3 pt-4 pb-2 text-xs text-uppercase fw-bold text-muted" style="font-size: 0.75rem;">Sistem & Supervisi</div>
                <a href="{{ route('admin.activity-logs.index') }}" class="list-group-item list-group-item-action {{ request()->is('admin/activity-logs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Audit Trail (Log)
                </a>
@endif

                
                <a href="{{ route('home') }}" class="list-group-item list-group-item-action mt-auto border-top border-secondary">
                    <i class="fa-solid fa-globe"></i> Kembali ke Web
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->
        <div class="admin-sidebar-backdrop" id="sidebar-backdrop"></div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg topbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light admin-menu-toggle" id="menu-toggle" type="button" aria-label="Toggle sidebar" aria-expanded="true">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="admin-page-title fw-semibold d-none d-md-block">
                        @yield('title', 'Dashboard')
                    </div>
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
        const wrapper = document.getElementById('wrapper');
        const menuToggle = document.getElementById('menu-toggle');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');
        const desktopQuery = window.matchMedia('(min-width: 992px)');
        const sidebarStorageKey = 'spmi-admin-sidebar-collapsed';

        function isDesktopLayout() {
            return desktopQuery.matches;
        }

        function setToggleState() {
            if (!menuToggle || !wrapper) return;

            const isCollapsed = isDesktopLayout()
                ? wrapper.classList.contains('layout-menu-collapsed')
                : !wrapper.classList.contains('toggled');

            menuToggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
        }

        function applyStoredDesktopSidebarState() {
            if (!wrapper) return;

            wrapper.classList.remove('toggled');

            if (isDesktopLayout()) {
                const shouldCollapse = localStorage.getItem(sidebarStorageKey) === 'true';
                wrapper.classList.toggle('layout-menu-collapsed', shouldCollapse);
            } else {
                wrapper.classList.remove('layout-menu-collapsed');
            }

            setToggleState();
        }

        function closeMobileSidebar() {
            if (!wrapper || isDesktopLayout()) return;
            wrapper.classList.remove('toggled');
            setToggleState();
        }

        if (menuToggle && wrapper) {
            applyStoredDesktopSidebarState();

            menuToggle.addEventListener('click', function (event) {
                event.preventDefault();

                if (isDesktopLayout()) {
                    wrapper.classList.toggle('layout-menu-collapsed');
                    localStorage.setItem(sidebarStorageKey, wrapper.classList.contains('layout-menu-collapsed') ? 'true' : 'false');
                } else {
                    wrapper.classList.toggle('toggled');
                }

                setToggleState();
            });
        }

        if (sidebarBackdrop && wrapper) {
            sidebarBackdrop.addEventListener('click', function () {
                wrapper.classList.remove('toggled');
                setToggleState();
            });
        }

        if (desktopQuery.addEventListener) {
            desktopQuery.addEventListener('change', applyStoredDesktopSidebarState);
        } else if (desktopQuery.addListener) {
            desktopQuery.addListener(applyStoredDesktopSidebarState);
        }
    </script>
    @stack('scripts')
</body>
</html>
