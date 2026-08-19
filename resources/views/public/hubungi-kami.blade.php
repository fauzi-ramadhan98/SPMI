@extends('layouts.public')

@section('title', 'Hubungi Kami - ' . config('app.name'))

@push('scripts')
    <style>
        body {
            background-color: #f5f5f5;
        }

        .page-header-custom {
            background-color: #ffffff;
            padding: 40px 0;
            border-bottom: 1px solid #eeeeee;
            margin-bottom: 40px;
        }

        .page-title {
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #333333;
            font-weight: 700;
            font-size: 2.25rem;
            margin: 0;
        }

        .content-area {
            background: #ffffff;
            padding: 50px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 60px;
            color: #444444;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .contact-icon {
            background-color: #0f172a;
            /* matching layout primary var */
            color: white;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 20px;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .contact-text h5 {
            font-weight: 700;
            margin-bottom: 5px;
            color: #222;
        }

        .contact-text p {
            margin: 0;
            color: #555;
        }

        .map-container {
            width: 100%;
            height: 400px;
            background-color: #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 30px 20px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-header-custom text-center">
        <div class="container">
            <h1 class="page-title">Hubungi Kami</h1>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="content-area">

                    <div class="row">
                        <!-- Contact Details -->
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h4 class="fw-bold mb-4">Informasi Kontak</h4>

                            <div class="contact-item">
                                <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                                <div class="contact-text">
                                    <h5>Alamat Kantor</h5>
                                    <p>Jl. Soekarno Hatta No 211<br>Bandung, Jawa Barat</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                                <div class="contact-text">
                                    <h5>WhatsApp</h5>
                                    <p>+62 896-3001-6469</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                                <div class="contact-text">
                                    <h5>Email Resmi</h5>
                                    <p>spmi@stmik-mi.ac.id</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Mockup -->
                        <div class="col-md-6">
                            <h4 class="fw-bold mb-4">Kirim Pesan</h4>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control" placeholder="Masukkan nama Anda">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control" placeholder="alamat@email.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pesan</label>
                                    <textarea class="form-control" rows="4"
                                        placeholder="Tuliskan pesan Anda di sini..."></textarea>
                                </div>
                                <button type="button" class="btn btn-primary px-4 py-2 mt-2 w-100"><i
                                        class="fa-solid fa-paper-plane me-2"></i> Kirim Pesan</button>
                            </form>
                        </div>
                    </div>

                    <!-- Maps / Embed Area -->
                    <div class="map-container d-flex align-items-center justify-content-center text-muted">
                        <div class="text-center">
                            <i class="fa-solid fa-map-location-dot fs-1 mb-2"></i>
                            <h5>Peta Lokasi</h5>
                            <p class="small">Embed Google Maps Anda di sini.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection