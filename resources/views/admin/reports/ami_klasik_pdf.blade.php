<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kegiatan Audit Mutu Internal (AMI) — Klasik</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #222; }

        .page-break { page-break-after: always; }

        /* ===== COVER ===== */
        .cover { text-align: center; padding-top: 110px; }
        .cover .type { font-size: 16px; font-weight: bold; letter-spacing: 3px; }
        .cover .title { font-size: 26px; font-weight: bold; margin: 8px 0 4px; text-transform: uppercase; }
        .cover .obj { font-size: 13.5px; font-weight: bold; letter-spacing: 1px; margin-bottom: 70px; }
        .cover .lpm { font-size: 13px; font-weight: bold; letter-spacing: 2px; }
        .cover .inst { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
        .cover-box { border: 2px solid #333; padding: 12px 24px; margin: 26px auto 0; width: 82%; font-size: 11px; text-align: left; }
        .cover-box div { padding: 3.5px 0; }
        .cover-box .k { font-weight: bold; display: inline-block; width: 175px; }
        .cover .year { margin-top: 54px; font-size: 20px; font-weight: bold; letter-spacing: 2px; }
        .cover .note { margin-top: 30px; font-size: 10px; color: #555; font-style: italic; }

        /* ===== RUNNING HEAD (sesuai contoh laporan klien) ===== */
        .runhead { margin-bottom: 14px; }
        .runhead table { width: 100%; border-collapse: collapse; font-family: "DejaVu Serif", serif; }
        .runhead td { border: 1px solid #444; padding: 5px 9px; font-size: 10.5px; vertical-align: middle; }
        .runhead .rh-logo { width: 86px; text-align: center; }
        .runhead .rh-logo img { display: block; margin: 0 auto; }
        .runhead .rh-inst { text-align: center; font-weight: bold; font-size: 14.5px; text-transform: uppercase; line-height: 1.35; }
        .runhead .rh-meta { font-size: 9.8px; line-height: 1.5; }
        .runhead .rh-title { font-weight: bold; font-size: 13px; text-transform: uppercase; padding-top: 7px; padding-bottom: 7px; }

        /* ===== COVER CUSTOM (upload via Pengaturan) ===== */
        .cover-custom { text-align: center; }
        .cover-custom img { display: block; margin: 0 auto; }

        /* ===== FIGURE BUKTI / LAMPIRAN GAMBAR ===== */
        .block { page-break-inside: avoid; }
        .bukti-fig { page-break-inside: avoid; text-align: center; margin: 12px 0 18px; }
        .bukti-fig img { border: 1px solid #999; }
        .bukti-cap { font-size: 9px; color: #555; margin-top: 5px; }

        /* ===== JUDUL ===== */
        .pengesahan { text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 2px; margin: 6px 0 14px; text-transform: uppercase; }
        .sec-title { text-align: center; font-weight: bold; font-size: 13.5px; letter-spacing: 2px; text-transform: uppercase; margin: 6px 0 12px; }
        .bab { text-align: center; margin: 4px 0 8px; }
        .bab .no { font-size: 15px; font-weight: bold; letter-spacing: 4px; }
        .bab .name { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .sub { font-weight: bold; font-size: 11px; margin: 13px 0 5px; }
        .tbl-caption { font-size: 9.5px; font-weight: bold; margin: 8px 0 3px; }

        .quote { border: 1px solid #bbb; background: #fafafa; padding: 12px 16px; text-align: justify; font-size: 10.5px; line-height: 1.6; }

        p { text-align: justify; margin: 0 0 7px; line-height: 1.55; }
        ul, ol { margin: 0 0 8px 22px; padding: 0; }
        li { margin-bottom: 3px; text-align: justify; }
        .mini { font-size: 9px; color: #555; }

        /* ===== TABEL ===== */
        table.data { width: 100%; border-collapse: collapse; margin: 5px 0 12px; }
        table.data th, table.data td { border: 1px solid #999; padding: 5px 6px; font-size: 9.3px; vertical-align: top; }
        table.data th { background: #ececec; text-align: center; font-weight: bold; }
        .center { text-align: center; }
        .footnote { font-size: 8.5px; color: #666; font-style: italic; margin-top: 3px; }

        table.meta { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table.meta td { border: 1px solid #aaa; padding: 4px 7px; font-size: 9.5px; vertical-align: top; }
        table.meta td.k { width: 130px; font-weight: bold; background: #f4f4f4; }

        /* ===== TTD ===== */
        .sign { margin-top: 34px; page-break-inside: avoid; width: 100%; border-collapse: collapse; }
        .sign td { width: 50%; text-align: center; vertical-align: top; padding: 0 12px; font-size: 10.5px; }
        .sign .role { font-size: 9.5px; color: #555; margin-top: 2px; }
        .sign .name { margin-top: 58px; font-weight: bold; }
        .sign .nip { font-size: 9px; color: #555; margin-top: 2px; }

        .footer { text-align: center; font-size: 8.5px; color: #777; margin-top: 22px; }
    </style>
</head>
<body>

@php
    $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
             'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $bulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
              'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
              'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
    $tgl = function ($d) use ($bulan) {
        if (!$d) return '—';
        $d = \Carbon\Carbon::parse($d);
        return $d->format('d') . ' ' . $bulan[$d->format('F')] . ' ' . $d->format('Y');
    };
    $tglLengkap = function ($d) use ($hari, $bulan) {
        if (!$d) return '—';
        $d = \Carbon\Carbon::parse($d);
        return $hari[$d->format('l')] . ', ' . $d->format('d') . ' ' . $bulan[$d->format('F')] . ' ' . $d->format('Y');
    };
    $periodeTeks = trim(($cycle->start_date ? $tgl($cycle->start_date) : '—')
        . ' s.d. ' . ($cycle->end_date ? $tgl($cycle->end_date) : ''));
    $bulanTahun = $cycle->start_date
        ? $bulan[$cycle->start_date->format('F')] . ' ' . $cycle->start_date->format('Y')
        : ($bulan[now()->format('F')] ?? now()->format('F')) . ' ' . now()->format('Y');
    $printedAny = false; // penanda chunk Daftar Tilik pertama (tanpa page-break tambahan)

    // Nilai dari Pengaturan (Konfigurasi Aplikasi)
    $showCover   = $showCover ?? true;
    $coverImage  = $coverImage ?? null;
    $headerLogo  = $headerLogo ?? null;
    $reportInst  = $reportInst ?? strtoupper((string) setting('institution_name', 'STMIK Mardira Indonesia'));
    $reportKode  = $reportKode ?? 'STMIKMI.LPMI.AMI.VIII.1';
    $reportEdisi = $reportEdisi ?? '2';

    // Running head sesuai contoh laporan klien:
    // [logo] Nama Institusi / BANDUNG | Waktu Pelaksanaan / Edisi / Kode, lalu judul dokumen di baris bawah.
    $rh = function (string $title) use ($reportInst, $bulanTahun, $reportKode, $reportEdisi, $headerLogo) {
        $logoCell = '';
        $colCount = 2;
        if (!empty($headerLogo['src'])) {
            $logoCell = '<td class="rh-logo" rowspan="2"><img src="' . $headerLogo['src'] . '"'
                . ' width="' . (int) $headerLogo['w'] . '" height="' . (int) $headerLogo['h'] . '" alt=""></td>';
            $colCount = 3;
        }
        return '<div class="runhead"><table><tr>' . $logoCell
            . '<td class="rh-inst">' . e($reportInst) . '<br>BANDUNG</td>'
            . '<td class="rh-meta">'
            . '<div>Waktu Pelaksanaan : ' . e($bulanTahun) . '</div>'
            . '<div>Edisi : ' . e($reportEdisi) . '</div>'
            . '<div>Kode : ' . e($reportKode) . '</div>'
            . '</td></tr>'
            . '<tr><td class="rh-title" colspan="' . ($colCount - 1) . '">' . e($title) . '</td></tr>'
            . '</table></div>';
    };
    $runheadHtml = $rh('LAPORAN AUDIT MUTU INTERNAL ( AMI )');
    $runheadLampiran = $rh('LAMPIRAN AUDIT MUTU INTERNAL ( AMI )');
@endphp

{{-- ================= COVER ================= --}}
@if($showCover)
    @if($coverImage)
        <div class="cover-custom">
            <img src="{{ $coverImage['src'] }}" width="{{ (int) $coverImage['w'] }}" height="{{ (int) $coverImage['h'] }}" alt="Cover Laporan">
        </div>
    @else
<div class="cover">
    <div class="type">LAPORAN KEGIATAN</div>
    <div class="title">Audit Mutu Internal (AMI)</div>
    @if($level === 'prodi' && $program)
        <div class="obj">PROGRAM STUDI {{ strtoupper(trim($program->degree_level . ' ' . $program->name)) }}</div>
    @else
        <div class="obj">REKAPITULASI SELURUH PROGRAM STUDI &amp; UNIT KERJA</div>
    @endif
    <div class="lpm">LEMBAGA PENJAMIN MUTU</div>
    <div class="inst">{{ $reportInst }}</div>

    <div class="cover-box">
        <div><span class="k">Jenis Laporan</span>: Laporan Kegiatan AMI (Klasik)</div>
        <div><span class="k">Siklus AMI</span>: {{ $cycle->name }}</div>
        <div><span class="k">Tahun Akademik</span>: {{ $cycle->academic_year }} &mdash; Semester {{ $cycle->semester }}</div>
        <div><span class="k">Periode Pelaksanaan</span>: {{ $periodeTeks }}</div>
        <div><span class="k">Cakupan Laporan</span>:
            @if($level === 'prodi' && $program) {{ $program->degree_level }} {{ $program->name }}
            @else Rekapitulasi Seluruh Auditee (Institusi) @endif
        </div>
        <div><span class="k">Jumlah Auditee</span>: {{ $assignments->count() }} Program Studi / Unit Kerja</div>
    </div>

    <div class="year">{{ $cycle->start_date ? $cycle->start_date->format('Y') : now()->year }}</div>
    <div class="note">Dokumen ini merupakan laporan resmi hasil kegiatan Audit Mutu Internal<br>dan menjadi dasar tindak lanjut (RTL) perbaikan mutu.</div>
</div>
    @endif
<div class="page-break"></div>
@endif

{{-- ================= LEMBARAN PENGESAHAN ================= --}}
<div class="pengesahan">Lembaran Pengesahan</div>
<div class="quote">
    &ldquo;Setelah menimbang, membaca dan meneliti hasil yang dimaksud dalam Laporan Hasil Audit Mutu Internal,
    Tahun Akademik <strong>{{ $cycle->academic_year }}</strong> Semester <strong>{{ $cycle->semester }}</strong> yang telah
    dilaksanakan dengan baik oleh Tim {{ config('app.name') }}, maka dokumen ini layak disahkan sebagai dokumen
    hasil evaluasi capaian mutu {{ config('app.name') }} tahun akademik {{ $cycle->academic_year }}&rdquo;.
</div>

<div style="text-align: right; margin-top: 16px;">
    <div>Bandung, {{ $tgl(now()) }}</div>
    <div style="margin-top: 4px;">Disetujui oleh,</div>
</div>
<table class="sign" style="margin-top: 2px;">
    <tr>
        <td>
            <div>Koordinator Pelaksana AMI</div>
            <div class="role">{{ config('app.name') }}</div>
            <div class="name">( .................................................. )</div>
            <div class="nip">NIP/NIDN: ..............................</div>
        </td>
        <td>
            <div>Mengetahui,</div>
            <div class="role">Kepala Lembaga Penjamin Mutu</div>
            <div class="name">( .................................................. )</div>
            <div class="nip">NIP/NIDN: ..............................</div>
        </td>
    </tr>
</table>
<div class="page-break"></div>

{{-- ================= DAFTAR TIM AUDITEE ================= --}}
<div class="sec-title">Daftar Tim Auditee</div>
<table class="data">
    <thead>
        <tr>
            <th style="width: 6%;">No</th>
            <th style="width: 24%;">Nama</th>
            <th style="width: 30%;">Jabatan</th>
            <th style="width: 28%;">Tanggal Pelaksanaan</th>
            <th style="width: 12%;">Tempat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($assignments as $idx => $assignment)
            @php
                $prog = $assignment->academicProgram;
                $unitM = $assignment->unit;
                $namaTim = $prog ? ($prog->head_name ?? '—') : ($unitM ? ($unitM->head_name ?? '—') : '—');
                $jabatanTim = $prog
                    ? ('Ketua Program Studi ' . $prog->degree_level . ' ' . $prog->name)
                    : ('Kepala Unit ' . ($unitM->name ?? '—'));
                $tglPelaksanaan = $tglLengkap($cycle->start_date)
                    . ($cycle->end_date ? ' — ' . $tgl($cycle->end_date) : '');
            @endphp
            <tr>
                <td class="center">{{ $idx + 1 }}</td>
                <td>{{ $namaTim }}</td>
                <td>{{ $jabatanTim }}</td>
                <td>{!! $tglPelaksanaan !!}</td>
                <td class="center">&mdash;</td>
            </tr>
        @empty
            <tr><td colspan="5" class="center">Belum ada auditee pada siklus ini.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="page-break"></div>

{{-- ================= BAB I PENDAHULUAN ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB I</div>
    <div class="name">Pendahuluan</div>
</div>

<div class="sub">1.1. Latar Belakang</div>
<p>
    Sistem Penjaminan Mutu Pendidikan Tinggi merupakan kegiatan sistemik untuk meningkatkan mutu pendidikan
    tinggi secara berencana dan berkelanjutan. Setiap perguruan tinggi wajib menyelenggarakan penjaminan mutu
    internal guna memastikan seluruh proses pendidikan berjalan sesuai standar yang ditetapkan. Salah satu bentuk
    pelaksanaannya adalah kegiatan Audit Mutu Internal (AMI) yang diselenggarakan secara periodik oleh
    {{ config('app.name') }} sebagai wujud evaluasi diri yang internally driven untuk memenuhi atau melampaui
    Standar Nasional Pendidikan Tinggi (SN-Dikti).
</p>
<p>
    Pelaksanaan AMI Tahun Akademik {{ $cycle->academic_year }} Semester {{ $cycle->semester }} ini ditujukan untuk
    mengevaluasi pemenuhan standar pada seluruh program studi dan unit kerja, mengidentifikasi ketidaksesuaian,
    serta menyusun rekomendasi perbaikan yang menjadi dasar Rencana Tindak Lanjut (RTL) mutu institusi.
</p>

<div class="sub">1.2. Dasar Pelaksanaan</div>
<p>Kegiatan AMI {{ $cycle->academic_year }} diselenggarakan berdasarkan antara lain:</p>
<ul>
    <li>Peraturan Presiden Republik Indonesia Nomor 8 Tahun 2012 tentang Jaminan Mutu Pendidikan Tinggi;</li>
    <li>Peraturan Menteri Riset, Teknologi, dan Pendidikan Tinggi tentang Standar Pendidikan Tinggi dan Sistem Penjaminan Mutu Internal;</li>
    <li>Standar Nasional Pendidikan Tinggi (SN-Dikti) beserta pedoman pelaksanaannya;</li>
    <li>Dokumen Standar, Pedoman, dan Prosedur Operasional {{ config('app.name') }}.</li>
</ul>

<div class="sub">1.3. Tujuan</div>
<p>Tujuan pelaksanaan AMI adalah:</p>
<ul>
    <li>Menilai tingkat pemenuhan standar pendidikan pada setiap program studi dan unit kerja;</li>
    <li>Mengidentifikasi ketidaksesuaian (temuan) beserta akar masalahnya;</li>
    <li>Menyusun rekomendasi perbaikan dan Rencana Tindak Lanjut (RTL) yang terukur;</li>
    <li>Menyediakan data objektif bagi pimpinan dalam pengambilan keputusan peningkatan mutu.</li>
</ul>

<div class="sub">1.4. Manfaat</div>
<ul>
    <li>Bagi pimpinan: memberikan informasi kondisi mutu terkini sebagai dasar kebijakan institusi;</li>
    <li>Bagi program studi dan unit kerja: umpan balik perbaikan proses kerja dan dokumen mutu;</li>
    <li>Bagi SPMI: bahan penyusunan laporan hasil audit dan pemantauan tindak lanjut;</li>
    <li>Bagi institusi: bukti komitmen penjaminan mutu internal yang berkelanjutan.</li>
</ul>

<div class="sub">1.5. Organisasi Pelaksana, Objek Audit dan Lingkup Audit</div>
<p>
    Kegiatan AMI dilaksanakan oleh Tim Audit yang ditunjuk berdasarkan keputusan pimpinan {{ config('app.name') }},
    terdiri atas koordinator dan anggota auditor yang bertanggung jawab atas seluruh tahapan audit. Objek audit adalah
    institusi, Program Studi, dan seluruh unit kerja di lingkungan {{ config('app.name') }}. Pada siklus ini terdapat
    <strong>{{ $assignments->count() }}</strong> objek audit (program studi / unit kerja). Uraian organisasi pelaksana
    dan objek audit disajikan pada Tabel 1.1.
</p>
<div class="tbl-caption">Tabel 1.1. Organisasi Pelaksana dan Objek Audit AMI Tahun Akademik {{ $cycle->academic_year }}</div>
<table class="data">
    <thead>
        <tr>
            <th style="width: 6%;">No</th>
            <th>Objek Audit (Unit Kerja Asal)</th>
            <th style="width: 18%;">Tipe</th>
            <th style="width: 26%;">Auditor</th>
            <th style="width: 24%;">Waktu Pelaksanaan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($assignments as $idx => $assignment)
            <tr>
                <td class="center">{{ $idx + 1 }}</td>
                <td>{{ $assignment->auditee_label }}</td>
                <td class="center">{{ $assignment->auditee_type_label }}</td>
                <td>{{ $assignment->auditor_name ?? '—' }} <span class="mini">({{ $assignment->auditor_role ?? '-' }})</span></td>
                <td>{{ $periodeTeks }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="center">Belum ada objek audit pada siklus ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="sub">1.6. Prosedur Pelaksanaan</div>
<ol>
    <li>Penetapan tim audit dan pembagian objek audit kepada setiap auditor;</li>
    <li>Penyusunan instrumen audit berupa daftar tilik (checklist) berbasis standar mutu;</li>
    <li>Pelaksanaan Evaluasi Diri oleh program studi / unit kerja sebagai bahan audit lapangan;</li>
    <li>Audit lapangan: verifikasi dokumen, wawancara, dan observasi di unit teraudit;</li>
    <li>Penyusunan laporan hasil audit beserta temuan, rekomendasi, dan daftar tilik pada lampiran;</li>
    <li>Tindak lanjut temuan melalui Rencana Tindak Lanjut (RTL) yang dipantau pada siklus berikutnya.</li>
</ol>

<div class="sub">1.7. Jadwal Pelaksanaan Audit</div>
<p>
    Pelaksanaan AMI Tahun Akademik {{ $cycle->academic_year }} diselenggarakan pada tanggal
    <strong>{{ $periodeTeks }}</strong>, mekanisme pelaksanaannya dilakukan dengan mengunjungi tempat kerja unit
    teraudit. Uraian jadwal pelaksanaannya disajikan pada Tabel 1.2.
</p>
<div class="tbl-caption">Tabel 1.2. Jadwal Pelaksanaan AMI Tahun Akademik {{ $cycle->academic_year }}</div>
<table class="data">
    <thead>
        <tr>
            <th style="width: 28%;">Auditor</th>
            <th style="width: 30%;">Objek Teraudit</th>
            <th style="width: 26%;">Waktu Pelaksanaan</th>
            <th style="width: 16%;">Tempat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($assignments as $assignment)
            <tr>
                <td>{{ $assignment->auditor_name ?? '—' }}</td>
                <td>{{ $assignment->auditee_label }}</td>
                <td>{{ $periodeTeks }}</td>
                <td class="center">&mdash;</td>
            </tr>
        @empty
            <tr><td colspan="4" class="center">Belum ada jadwal audit.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="page-break"></div>

{{-- ================= BAB II RUANG LINGKUP ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB II</div>
    <div class="name">Ruang Lingkup Audit Mutu Internal</div>
</div>
<p>
    Audit Mutu Internal (AMI) telah dilaksanakan oleh {{ config('app.name') }} terhadap program studi dan unit kerja
    di lingkungan institusi, meliputi penilaian kepatuhan maupun pembinaan terhadap pemenuhan standar yang ditetapkan.
    Hasil proses AMI didokumentasikan dalam laporan ini untuk disampaikan kepada pimpinan {{ config('app.name') }}
    sebagai bahan pengambilan keputusan peningkatan mutu. Ruang lingkup audit mengacu pada standar mutu berikut.
</p>

@forelse($standars as $s)
    <div class="sub">2.{{ $loop->iteration }}. {{ $s->name }}</div>
    <p>{{ $s->description ?? $s->pernyataan_standar ?? ('Evaluasi terhadap ' . $s->name . ' meliputi pemenuhan butir-butir standar pada dokumen standar institusi' . ($s->rujukan ? ' dengan rujukan ' . $s->rujukan : '') . '.') }}</p>
    @if($s->checklistItems->count())
        <p class="mini">Jumlah butir tilik pada standar ini: {{ $s->checklistItems->count() }} butir (rincian pada Lampiran Daftar Tilik).</p>
    @endif
@empty
    <p>Belum ada standar mutu terdaftar pada sistem.</p>
@endforelse
<div class="page-break"></div>

{{-- ================= BAB III REKOMENDASI DAN TINDAK LANJUT ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB III</div>
    <div class="name">Rekomendasi dan Tindak Lanjut</div>
</div>

<div class="sub">3.1. Hasil Audit</div>
<p>
    Berdasarkan pelaksanaan AMI terhadap <strong>{{ $summary['auditee_count'] }}</strong> auditee pada siklus
    <strong>{{ $cycle->name }}</strong>, terdapat <strong>{{ $summary['instrument_count'] }}</strong> indikator borang
    yang dinilai, dengan rincian: <strong>{{ $summary['kts_mayor'] }}</strong> KTS Mayor,
    <strong>{{ $summary['kts_minor'] }}</strong> KTS Minor, <strong>{{ $summary['ob'] }}</strong> catatan Observasi (OB),
    dan <strong>{{ $summary['sesuai'] }}</strong> indikator dinyatakan sesuai/melampaui standar. Rincian temuan beserta
    rekomendasi disajikan pada Tabel 3.1.
</p>
<div class="tbl-caption">Tabel 3.1. Hasil Audit — Temuan, Rekomendasi dan Tindak Lanjut</div>
<table class="data">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 17%;">Standar</th>
            <th style="width: 28%;">Temuan</th>
            <th style="width: 22%;">Rekomendasi</th>
            <th style="width: 18%;">Tindak Lanjut</th>
            <th style="width: 10%;">Referensi</th>
        </tr>
    </thead>
    <tbody>
        @php $noTemuan = 1; @endphp
        @foreach($assignments as $assignment)
            @foreach($assignment->findings as $finding)
                <tr>
                    <td class="center">{{ $noTemuan++ }}</td>
                    <td>{{ $finding->criteria ?: '—' }}</td>
                    <td>
                        {{ $finding->description }}
                        @if($finding->root_cause)
                            <div class="mini"><em>Akar masalah: {{ $finding->root_cause }}</em></div>
                        @endif
                    </td>
                    <td>{{ $finding->corrective_action ?: '—' }}</td>
                    <td>
                        Status: {{ strtoupper(str_replace('_', ' ', $finding->status)) }}
                        @if($finding->target_date)
                            <div class="mini">Target: {{ $tgl($finding->target_date) }}</div>
                        @endif
                    </td>
                    <td class="center">TB-{{ str_pad($finding->id, 3, '0', STR_PAD_LEFT) }}</td>
                </tr>
            @endforeach
        @endforeach
        @if($summary['total_findings'] === 0)
            <tr><td colspan="6" class="center">Belum ada temuan yang tercatat pada siklus ini.</td></tr>
        @endif
    </tbody>
</table>

<div class="sub">3.2. Rekomendasi dan Tindak Lanjut</div>
@php
    $rekomList = $assignments->flatMap->findings
        ->filter(fn ($f) => $f->type === 'KTS' && $f->corrective_action)
        ->values();
@endphp
@if($rekomList->isNotEmpty())
    <ul>
        @foreach($rekomList as $f)
            <li>{{ $f->corrective_action }} <span class="mini">({{ $f->criteria ?: 'temuan KTS' }})</span></li>
        @endforeach
    </ul>
@else
    <ul>
        <li>Lengkapi dokumen mutu yang belum tersedia atau belum mutakhir pada setiap unit teraudit;</li>
        <li>Perkuat implementasi standar yang belum terpenuhi optimal melalui kegiatan pembinaan berkelanjutan;</li>
        <li>Lakukan monitoring rutin terhadap pemenuhan standar sebagai bagian siklus penjaminan mutu.</li>
    </ul>
@endif
<p>
    Seluruh temuan Ketidaksesuaian (KTS) wajib ditindaklanjuti melalui Rencana Tindak Lanjut (RTL) auditee, diverifikasi
    oleh {{ config('app.name') }} pada sistem, dan dilaporkan kembali pada siklus AMI berikutnya sebagai bentuk
    perbaikan mutu yang berkelanjutan.
</p>
<div class="page-break"></div>

{{-- ================= BAB IV PENUTUP ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB IV</div>
    <div class="name">Penutup</div>
</div>

<div class="sub">4.1. Kesimpulan</div>
<p>
    Berdasarkan pelaksanaan Audit Mutu Internal Tahun Akademik {{ $cycle->academic_year }} Semester {{ $cycle->semester }}
    terhadap {{ $summary['auditee_count'] }} auditee dengan {{ $summary['instrument_count'] }} indikator yang dinilai,
    dapat disimpulkan bahwa pemenuhan standar mutu pada program studi dan unit kerja telah berjalan sebagaimana mestinya
    dengan sejumlah ketidaksesuaian yang teridentifikasi sebanyak {{ $summary['total_findings'] }} temuan
    ({{ $summary['kts_mayor'] }} KTS Mayor, {{ $summary['kts_minor'] }} KTS Minor, dan {{ $summary['ob'] }} Observasi).
    Seluruh ketidaksesuaian telah mendapatkan rekomendasi perbaikan dan menjadi dasar Rencana Tindak Lanjut (RTL)
    peningkatan mutu institusi.
</p>

<div class="sub">4.2. Saran</div>
<ul>
    <li>Program studi dan unit kerja sebaiknya menuntaskan RTL temuan sebelum siklus AMI berikutnya dimulai;</li>
    <li>{{ config('app.name') }} perlu memperkuat pembinaan dan verifikasi dokumen mutu pada unit yang berpotensi menghasilkan temuan berulang;</li>
    <li>Evaluasi diri hendaknya diisi tepat waktu oleh setiap auditee agar data pembanding (klaim vs hasil audit) tersedia lengkap.</li>
</ul>

<p style="margin-top: 16px;">
    Demikian laporan ini dibuat dengan sebenar-benarnya untuk dapat dimanfaatkan sebagaimana mestinya dalam rangka
    peningkatan mutu pendidikan di {{ config('app.name') }}.
</p>
<div class="page-break"></div>

{{-- ================= LAMPIRAN: DAFTAR TILIK ================= --}}
@forelse($assignments as $assignment)
    @php
        $ed = $edMap[$assignment->id] ?? null;
        $edItems = $ed ? $ed->items : collect();
        $auditorList = $assignment->auditor_name ? [$assignment->auditor_name . ($assignment->auditor_role ? ' (' . $assignment->auditor_role . ')' : '')] : [];
        $runheadDt = $rh('DAFTAR TILIK AUDIT MUTU INTERNAL ( AMI )');
        $adaIsi = false;
    @endphp

    @foreach($standars as $s)
        @php
            $rows = [];
            foreach ($s->checklistItems->sortBy('sort_order') as $ci) {
                $edItem = $edItems->first(fn ($e) => $e->checklist_item_id && $e->checklist_item_id == $ci->id);
                $rows[] = [
                    'ref' => trim(($s->kode_standar ? $s->kode_standar . '/' : '') . ($ci->code ?? $ci->indicator_key ?? '')),
                    'q' => $ci->audit_question ?? $ci->indicator,
                    'catatan' => $edItem?->narasi,
                    'sa' => $edItem?->self_assessment,
                    'skor' => $edItem?->score,
                ];
            }
            foreach ($edItems->filter(fn ($e) => $e->quality_standard_id && $e->quality_standard_id == $s->id && !$e->checklist_item_id) as $e) {
                $rows[] = [
                    'ref' => $s->kode_standar ?: '—',
                    'q' => $e->indicator,
                    'catatan' => $e->narasi,
                    'sa' => $e->self_assessment,
                    'skor' => $e->score,
                ];
            }
        @endphp

        @if(count($rows))
            @php $adaIsi = true; @endphp
            @if($printedAny)
                <div style="page-break-before: always;"></div>
            @endif
            @php $printedAny = true; @endphp
            {!! $runheadDt !!}

            <table class="meta">
                <tr>
                    <td class="k">Hari / Tanggal</td>
                    <td>: {{ $periodeTeks }}</td>
                    <td class="k" style="width: 95px;">Auditor</td>
                    <td>: @foreach($auditorList as $ai => $aname){{ chr(97 + $ai) . '. ' . $aname }}@if(!$loop->last)<br>&nbsp;&nbsp;&nbsp;@endif @endforeach{{ $auditorList ? '' : '—' }}</td>
                </tr>
                <tr>
                    <td class="k">Waktu</td>
                    <td>: {{ $cycle->start_date ? $tgl($cycle->start_date) . ' — ' . $tgl($cycle->end_date) : '—' }}</td>
                    <td class="k">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td class="k">{{ $assignment->academicProgram ? 'Program Studi' : 'Unit Kerja' }}</td>
                    <td colspan="3">: {{ $assignment->auditee_label }}</td>
                </tr>
                <tr>
                    <td class="k">Nama Dokumen</td>
                    <td colspan="3">: <strong>{{ $s->name }}</strong> @if($s->kode_standar)<span class="mini">({{ $s->kode_standar }})</span>@endif</td>
                </tr>
            </table>

            <table class="data">
                <thead>
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 17%;">Referensi (Butir Mutu)</th>
                        <th style="width: 33%;">Isi Pertanyaan</th>
                        <th style="width: 24%;">Catatan Audit</th>
                        <th style="width: 4%;">S</th>
                        <th style="width: 4%;">TS</th>
                        <th style="width: 14%;">Catatan Khusus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $ri => $row)
                        <tr>
                            <td class="center">{{ $ri + 1 }}</td>
                            <td>{{ $row['ref'] ?: '—' }}</td>
                            <td>{{ $row['q'] }}</td>
                            <td>{{ $row['catatan'] ?? '—' }}</td>
                            <td class="center">{{ ($row['sa'] ?? null) === 'Tercapai' ? '✓' : '' }}</td>
                            <td class="center">{{ ($row['sa'] ?? null) === 'Belum Tercapai' ? '✓' : '' }}</td>
                            <td>{{ $row['skor'] !== null && $row['skor'] !== '' ? 'Skor: ' . $row['skor'] : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="footnote">S = Sesuai Standar; TS = Tidak Sesuai &nbsp;|&nbsp; Skor penilaian mandiri skala 0&ndash;4 @if($ed) &nbsp;|&nbsp; Sumber: Evaluasi Diri {{ $ed->academic_year }} {{ $ed->semester }} @endif</div>
        @endif
    @endforeach

    @if($adaIsi)
        <table class="sign">
            <tr>
                <td>
                    <div>Auditor</div>
                    <div class="role">{{ $assignment->auditor_type ?? 'Auditor Internal' }}</div>
                    <div class="name">{{ $assignment->auditor_name ?? '( .................................................. )' }}</div>
                    <div class="nip">NIDN/NIK: {{ $assignment->auditor_nidn ?? '—' }}</div>
                </td>
                <td>
                    <div>Auditee</div>
                    @if($assignment->academicProgram)
                        <div class="role">Ketua Program Studi {{ $assignment->academicProgram->degree_level }} {{ $assignment->academicProgram->name }}</div>
                        <div class="name">{{ $assignment->academicProgram->head_name ?? '( .................................................. )' }}</div>
                    @else
                        <div class="role">Kepala Unit {{ $assignment->unit->name ?? '' }}</div>
                        <div class="name">{{ $assignment->unit->head_name ?? '( .................................................. )' }}</div>
                    @endif
                    <div class="nip">NIDN/NIK: {{ ($assignment->academicProgram->head_nidn ?? $assignment->unit->head_nidn ?? null) ?? '—' }}</div>
                </td>
            </tr>
        </table>
    @endif
@empty
    <div class="sec-title">Lampiran Daftar Tilik</div>
    <p>Belum ada auditee pada siklus ini, sehingga daftar tilik belum dapat ditampilkan.</p>
@endforelse

@include('admin.reports.lampiran_dokumen')

<div class="footer">
    Dokumen Laporan Kegiatan AMI (Klasik) &mdash; {{ config('app.name') }} &mdash; Siklus {{ $cycle->name }} &mdash; Dicetak {{ now()->format('d/m/Y H:i') }}
</div>
</body>
</html>
