@extends('layouts.public')

@section('title', 'AMI Program Studi - ' . config('app.name'))

@push('scripts')
<style>
    body { background-color: var(--bg-color); }
    .page-header-custom { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 60px 0; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); color: white; }
    .page-title { font-weight: 700; font-size: 2.25rem; color: #ffffff; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .content-area { background: #fff; padding: 40px 50px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); margin-bottom: 60px; color: #444; line-height: 1.8; font-size: 1.05rem; }
    .ami-card { border: none; border-left: 5px solid var(--secondary-color); border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.06); padding: 20px 24px; margin-bottom: 20px; background: #fff; transition: all 0.3s ease; }
    .ami-card:hover { box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15); transform: translateY(-3px) translateX(4px); border-left-color: var(--primary-color); }
    .ami-card h5 { color: var(--primary-color); font-weight: 700; margin-bottom: 6px; }
    .ami-card p { color: #555; font-size: 0.95rem; margin: 0; }
    .ami-badge { background: var(--accent-color); color: white; border-radius: 20px; padding: 4px 14px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    @media (max-width: 768px) { .content-area { padding: 30px 20px; } }
</style>
@endpush

@section('content')
<div class="page-header-custom text-center">
    <div class="container">
        <h1 class="page-title">Audit Mutu Internal — AMI Program Studi</h1>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="content-area">

                <p>Audit Mutu Internal (AMI) Program Studi dilaksanakan secara berkala untuk memastikan kepatuhan dan pemenuhan standar mutu di setiap program studi yang ada di STMIK Mardira Indonesia.</p>
                <p>Berikut adalah arsip pelaksanaan AMI Program Studi dari berbagai tahun akademik:</p>

                <hr class="my-4 opacity-25">

                <a href="{{ route('public.documents.index', ['search' => 'AMI Program Studi 2024/2025']) }}" class="text-decoration-none">
                    <div class="ami-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>AMI Program Studi 2024/2025</h5>
                                <p>Laporan & Dokumen Audit Mutu Internal Program Studi TA 2024/2025</p>
                            </div>
                            <span class="ami-badge">Terbaru</span>
                        </div>
                    </div>
                </a>

                <a href="{{ route('public.documents.index', ['search' => 'AMI Program Studi 2023/2024']) }}" class="text-decoration-none">
                    <div class="ami-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>AMI Program Studi 2023/2024</h5>
                                <p>Laporan & Dokumen Audit Mutu Internal Program Studi TA 2023/2024</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('public.documents.index', ['search' => 'AMI Program Studi 2022/2023']) }}" class="text-decoration-none">
                    <div class="ami-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>AMI Program Studi 2022/2023</h5>
                                <p>Laporan & Dokumen Audit Mutu Internal Program Studi TA 2022/2023</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('public.documents.index', ['search' => 'AMI Program Studi 2021/2022']) }}" class="text-decoration-none">
                    <div class="ami-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>AMI Program Studi 2021/2022</h5>
                                <p>Laporan & Dokumen Audit Mutu Internal Program Studi TA 2021/2022</p>
                            </div>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</div>
@endsection
