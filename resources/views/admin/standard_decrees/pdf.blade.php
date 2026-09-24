<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SK {{ $decree->sk_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 0; line-height: 1.5; }

        /* ===== HALAMAN 1 : KONTEN SK DICETAK DI ATAS KERTAS KOP ===== */
        .page1 {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }
        .page1 .kop-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }
        .page1 .body {
            position: relative;
            z-index: 1;
            padding: 40mm 24mm 16mm 24mm;
        }
        .subtitle { text-align: center; font-weight: bold; font-size: 12px; margin: 8px 0 4px; }
        table.field { width: 100%; margin-bottom: 4px; }
        table.field td { vertical-align: top; }
        table.field .label { width: 65px; }
        h3.section { font-size: 12px; margin: 10px 0 2px; }
        .section.center { text-align: center; }
        ol { margin: 2px 0 8px 18px; padding: 0; text-align: justify; }
        ol li { margin-bottom: 3px; }
        .dictum { text-align: justify; }
        .dictum p { margin: 2px 0; text-align: justify; }
        .sign { margin-top: 16px; width: 100%; }
        .sign td { vertical-align: top; }
        .sign .block { text-align: center; }
        .sign .block .ttd { height: 110px; margin: 2px 0 -35px 0; }
        .sign .block .ttd img { height: 110px; }
        .cc { margin-top: 16px; font-size: 11px; }

        /* ===== HALAMAN 2 : LAMPIRAN DAFTAR NAMA TIM AUDITORS ===== */
        .lampiran { page-break-before: always; padding: 24mm 24mm; }
        .lamp-head { width: 100%; font-size: 11px; }
        .lamp-head td { padding: 1px 0; vertical-align: top; }
        .lamp-head .label { width: 70px; }
        .lamp-title { text-align: center; font-weight: bold; font-size: 13px; margin: 12px 0 6px; }
        table.data { width: 100%; font-size: 11px; margin-top: 8px; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 6px; }
        table.data th { text-align: center; }
        table.data td.no, table.data td.nik, table.data td.jab { text-align: center; }
        table.data td.nama { text-align: left; }
        .lamp-sign { margin-top: 36px; width: 100%; }
        .lamp-sign td { vertical-align: top; }
        .lamp-sign .block { text-align: center; }
        .lamp-sign .block .ttd { height: 110px; margin: 2px 0 -35px 0; }
        .lamp-sign .block .ttd img { height: 110px; }
    </style>
</head>
<body>

    @php
        $inst     = strtoupper(setting('institution_name', 'STMIK Mardira Indonesia'));
        $ketua    = setting('ketua_nama', 'Dr. Marjito M.Pd');
        $ketuaNik = setting('ketua_nik', 'NIK. 95.01.017');
        $lokasi   = $decree->lokasi ?: 'Bandung';
        $tanggal  = $decree->tanggal_sk?->format('d F Y');
        $scope    = $decree->nama_standar;
    @endphp

    {{-- ===== HALAMAN 1 : KONTEN SK DI ATAS KERTAS KOP ===== --}}
    <div class="page1">
        <div class="kop-bg">@if($kop)<img src="{{ $kop }}" style="width:210mm; height:297mm;">@endif</div>

        <div class="body">
            <div class="subtitle">KETUA {{ $inst }}</div>

            <table class="field">
                <tr><td class="label">Nomor</td><td>:</td><td>{{ $decree->sk_no }}</td></tr>
                <tr><td class="label">Tentang</td><td>:</td><td>{{ $decree->judul }}</td></tr>
            </table>

            <h3 class="section">Menimbang :</h3>
            @if(trim((string) $decree->menimbang) !== '')
                <div class="dictum">{!! nl2br(e($decree->menimbang)) !!}</div>
            @else
            <ol>
                <li>bahwa untuk meningkatkan pelaksanaan Tri Dharma Agama Tinggi dalam rangka penerapan Sistem Penjaminan Mutu Internal (SPMI), perlu dilaksanakan Audit Mutu Internal yang meliputi bidang Pendidikan, Penelitian, dan Pengabdian kepada Masyarakat;</li>
                <li>bahwa berdasarkan pertimbangan pada poin satu di atas, maka dipandang perlu menetapkan Keputusan Ketua {{ $inst }} sebagai landasan dalam melaksanakan Audit Mutu Internal.</li>
            </ol>
            @endif

            <h3 class="section">Mengingat :</h3>
            @if(trim((string) $decree->mengingat) !== '')
                <div class="dictum">{!! nl2br(e($decree->mengingat)) !!}</div>
            @else
            <ol>
                <li>Undang-Undang Republik Indonesia Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;</li>
                <li>Undang-Undang Republik Indonesia Nomor 14 Tahun 2005 tentang Guru dan Dosen;</li>
                <li>Undang-Undang Republik Indonesia Nomor 12 Tahun 2012 tentang Pendidikan Tinggi;</li>
                <li>Peraturan Menteri Pendidikan, Kebudayaan, Riset, dan Teknologi Nomor 10 Tahun 2025 tentang Sistem Penjaminan Mutu Pendidikan Tinggi;</li>
                <li>Statuta {{ $inst }}.</li>
            </ol>
            @endif

            <h3 class="section center">Memutuskan :</h3>
            @if(trim((string) $decree->memutuskan) !== '')
                <div class="dictum" style="margin-top:6px;">{!! nl2br(e($decree->memutuskan)) !!}</div>
            @else
            <div class="subtitle" style="text-align:left;">Menetapkan :</div>
            <div class="dictum">
                <p><strong>Kesatu</strong> : Menetapkan Tim Auditor untuk pelaksanaan Audit Mutu Internal {!! $scope ? 'terhadap <strong>' . e($scope) . '</strong>' : 'di lingkungan ' . e($inst) !!} sebagaimana tercantum dalam Lampiran Keputusan ini.</p>
                <p><strong>Kedua</strong> : Pelaksanaan kegiatan audit dilakukan sesuai jadwal yang telah ditetapkan dan hasil audit dilaporkan kepada Ketua {{ $inst }}.</p>
                <p><strong>Ketiga</strong> : Keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan apabila di kemudian hari terdapat kekeliruan akan diperbaiki sebagaimana mestinya.</p>
            </div>
            @endif

            <table class="sign">
                <tr>
                    <td class="block" style="text-align:right; width:45%;"></td>
                    <td class="block" style="text-align:center; width:55%;">
                        Ditetapkan di : {{ $lokasi }}<br>
                        Pada tanggal : {{ $tanggal }}
                        <br>
                        <strong>Ketua {{ $inst }}</strong>
                        <br>
                        <div class="ttd">@if($ttd)<img src="{{ $ttd }}">@endif</div>
                        <u><strong>{{ $ketua }}</strong></u><br>
                        {{ $ketuaNik }}
                    </td>
                </tr>
            </table>

            <div class="cc">
                Salinan disampaikan ke Yth.:<br>
                1. Ketua YPMI<br>
                2. Ketua Program Studi<br>
                3. Kepala SPMI<br>
                4. Yang bersangkutan
            </div>
        </div>
    </div>

    {{-- ===== LAMPIRAN : HALAMAN 2 — DAFTAR NAMA TIM AUDITORS (hanya untuk jenis auditor) ===== --}}
    @if($decree->jenis === 'auditor' && $decree->cycle)
    <div class="lampiran">
        <table class="lamp-head">
            <tr><td class="label">Lampiran</td><td>:</td><td>Keputusan Ketua tentang Penunjukan Auditor</td></tr>
            <tr><td class="label">Nomor</td><td>:</td><td>{{ $decree->sk_no }}</td></tr>
            <tr><td class="label">Tentang</td><td>:</td><td>{{ $decree->judul }}</td></tr>
        </table>

        <div class="lamp-title">DAFTAR NAMA TIM AUDITORS<br>AUDIT MUTU INTERNAL</div>

        <table class="field">
            <tr><td class="label">Nama Standar / Lingkup</td><td>:</td><td>{{ $scope ?: '—' }}</td></tr>
        </table>

        <table class="data">
            <thead>
                <tr>
                    <th style="width:8%;">NO</th>
                    <th style="width:18%;">NIK / NIDN</th>
                    <th>NAMA</th>
                    <th style="width:20%;">JABATAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($decree->auditors() as $i => $a)
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td class="nik">{{ $a['nik'] }}</td>
                    <td class="nama">{{ $a['nama'] }}</td>
                    <td class="jab">{{ $a['jabatan'] }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;">—</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="lamp-sign">
            <tr>
                <td></td>
                <td class="block" style="width:55%;">
                    {{ $lokasi }}, {{ $tanggal }}
                    <br><br>
                    <strong>Ketua {{ $inst }}</strong>
                    <br><br>
                    <div class="ttd">@if($ttd)<img src="{{ $ttd }}">@endif</div>
                    <u><strong>{{ $ketua }}</strong></u><br>
                    {{ $ketuaNik }}
                </td>
            </tr>
        </table>
    </div>
    @endif
</body>
</html>