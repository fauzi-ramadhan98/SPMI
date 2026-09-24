<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'SPMI'))</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0f3a8b; /* STMIK Mardira blue / Dark attractive blue */
            --secondary-color: #2563eb; /* Bright modern blue */
            --accent-color: #0284c7; /* Sky blue */
            --bg-color: #f4f7fb; /* Soft blue-gray background */
            --text-color: #1e293b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .navbar-custom {
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .navbar-custom .nav-link {
            font-weight: 500;
            font-size: 0.93rem;
            color: var(--text-color);
            padding: 0.5rem 0.75rem !important;
            white-space: nowrap;
            transition: color 0.3s ease;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link:focus {
            color: var(--secondary-color);
        }

        .navbar-custom .dropdown-menu {
            border-radius: 10px;
            padding: 0.5rem 0;
            min-width: 220px;
        }

        .navbar-custom .dropdown-item {
            font-size: 0.88rem;
            padding: 0.5rem 1rem;
            color: var(--text-color);
        }

        .navbar-custom .dropdown-item:hover {
            background-color: var(--bg-color);
            color: var(--secondary-color);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), #2d63cc);
            color: white;
            padding: 100px 0 140px; /* Ditambah padding bawah agar card tidak menutupi teks */
            text-align: center;
            /* clip-path dihapus agar banner menjadi kotak lurus */
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .btn-custom {
            background-color: var(--secondary-color);
            color: white;
            border-radius: 50px;
            padding: 10px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #2563eb;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }

        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }

        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        /* Unified Global Template Overrides for all public menus */
        .page-header-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            padding: 60px 0 !important;
            margin-bottom: 40px !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
            border-bottom: none !important;
            color: white !important;
        }
        .page-title {
            font-weight: 700 !important;
            font-size: 2.25rem !important;
            color: #ffffff !important;
            margin: 0 !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
            font-family: 'Inter', sans-serif !important;
        }
        .content-area {
            background: #fff !important;
            padding: 40px 50px !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03) !important;
            margin-bottom: 60px !important;
            color: #444 !important;
            line-height: 1.8 !important;
            font-size: 1.05rem !important;
        }
        @media (max-width: 768px) {
            .navbar-custom .navbar-brand {
                max-width: calc(100vw - 96px);
                font-size: 0.95rem;
            }

            .content-area { padding: 30px 20px !important; }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-xl navbar-custom sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset(setting('logo', 'images/logo.png')) }}" alt="Logo" class="me-2" style="height: 40px; width: auto;">
                SPMI <span class="fw-light ms-1">{{ setting('institution_name', 'STMIK Mardira Indonesia') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-xl-center">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Beranda</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProfil" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Profil LPM
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownProfil">
                            <li><a class="dropdown-item" href="{{ route('sambutan') }}">Sambutan Kepala LPM</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'struktur-organisasi']) }}">Struktur
                                    Organisasi</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'hubungi-kami']) }}">Hubungi Kami</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownSurvei" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Survei
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownSurvei">
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'survei-pemahaman-visi-misi']) }}">Survei
                                    Pemahaman Visi Misi</a></li>
                            <li><a class="dropdown-item" href="{{ route('public.surveys.index') }}">Survei Kepuasan
                                    Layanan</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'survei-kepuasan-pengguna-lulusan']) }}">Survei
                                    Kepuasan Lulusan</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'survei-kepuasan-mitra-kerjasama']) }}">Survei
                                    Kepuasan Mitra</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'layanan-alumni']) }}">Layanan Alumni
                                    (Karir Link)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('page.generic', ['slug' => 'laporan-kepuasan']) }}">Laporan
                                    Kepuasan</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAMI" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Audit Mutu
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownAMI">
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'ami-prodi']) }}">AMI Program Studi</a></li>
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'ami-unit-kerja']) }}">AMI Unit Kerja</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownDokumen" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Dokumen & Laporan
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownDokumen">
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'monev-pembelajaran']) }}">Laporan Monev Pembelajaran</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('public.documents.index') }}">Semua Dokumen Mutu</a></li>
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'dokumen-spmi-2021']) }}">Dokumen SPMI 2021</a></li>
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'dokumen-spmi-2025']) }}">Dokumen SPMI 2025</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInfo" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Informasi
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navbarDropdownInfo">
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'sertifikat']) }}">Sertifikat</a></li>
                            <li><a class="dropdown-item" href="{{ route('public.news.index') }}">Berita</a></li>
                            <li><a class="dropdown-item" href="{{ route('page.generic', ['slug' => 'benchmarking']) }}">Benchmarking</a></li>
                        </ul>
                    </li>

                    @guest
                        <li class="nav-item ms-xl-3">
                            <a class="btn btn-outline-primary rounded-pill px-4 btn-sm mt-2 mt-xl-0"
                                href="{{ route('login') }}">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Login Portal
                            </a>
                        </li>
                    @else
                        <li class="nav-item ms-xl-3">
                            <a class="btn btn-primary rounded-pill px-4 btn-sm mt-2 mt-xl-0"
                                href="{{ route('admin.dashboard') }}">
                                <i class="fa-solid fa-gauge me-1"></i> Dashboard
                            </a>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row gy-4 py-4">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <img src="{{ asset(setting('logo', 'images/logo.png')) }}" alt="Logo" class="me-2" style="height: 30px; width: auto;">
                        SPMI {{ setting('institution_name', 'STMIK Mardira Indonesia') }}
                    </h5>
                    <p class="text-white-50 small">Website Resmi Satuan Penjaminan Mutu Internal {{ setting('institution_name', 'STMIK Mardira Indonesia') }}
                    </p>
                    <p class="fw-semibold fst-italic text-info small">"Siap Melanjutkan Budaya Mutu"</p>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Kontak</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-info"></i>Jl. Soekarno Hatta No.
                            211 Bandung</li>
                        <li class="mb-2"><i class="fa-brands fa-whatsapp me-2 text-info"></i>+62 896-3001-6469</li>
                        <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-info"></i>spmi@stmik-mi.ac.id</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Menu Cepat</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><a href="{{ route('sambutan') }}"
                                class="text-white-50 text-decoration-none">Sambutan Kepala LPM</a></li>
                        <li class="mb-1"><a href="{{ route('page.generic', 'struktur-organisasi') }}"
                                class="text-white-50 text-decoration-none">Struktur Organisasi</a></li>
                        <li class="mb-1"><a href="{{ route('public.surveys.index') }}"
                                class="text-white-50 text-decoration-none">Survei Kepuasan</a></li>
                        <li class="mb-1"><a href="{{ route('public.documents.index') }}"
                                class="text-white-50 text-decoration-none">Dokumen SPMI</a></li>
                        <li class="mb-1"><a href="{{ route('public.news.index') }}"
                                class="text-white-50 text-decoration-none">Berita</a></li>
                        <li class="mb-1"><a href="{{ route('page.generic', 'hubungi-kami') }}"
                                class="text-white-50 text-decoration-none">Hubungi Kami</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary opacity-25">
            <p class="text-white-50 small text-center mb-0">&copy; {{ date('Y') }} Sistem Penjaminan Mutu Internal STMIK
                Mardira Indonesia. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>