<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Audit Mutu Internal (AMI) — Berbasis Risiko</title>
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
        .footnote { font-size: 8.5px; color: #666; font-style: italic; margin-top: 3px; }

        /* ===== JUDUL ===== */
        .pengesahan { text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 2px; margin: 6px 0 14px; text-transform: uppercase; }
        .sec-title { text-align: center; font-weight: bold; font-size: 13.5px; letter-spacing: 2px; text-transform: uppercase; margin: 6px 0 12px; }
        .bab { text-align: center; margin: 4px 0 8px; }
        .bab .no { font-size: 15px; font-weight: bold; letter-spacing: 4px; }
        .bab .name { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .sub { font-weight: bold; font-size: 11px; margin: 13px 0 5px; }
        .tbl-caption { font-size: 9.5px; font-weight: bold; margin: 8px 0 3px; }

        .quote { border: 1px solid #bbb; background: #fafafa; padding: 12px 16px; text-align: justify; font-size: 10.5px; line-height: 1.6; }
        .note-box { border: 1.5px solid #333; background: #f7f7f7; padding: 8px 12px; font-size: 10px; text-align: justify; margin: 6px 0 12px; }

        p { text-align: justify; margin: 0 0 7px; line-height: 1.55; }
        ul, ol { margin: 0 0 8px 22px; padding: 0; }
        li { margin-bottom: 3px; text-align: justify; }
        .mini { font-size: 8.7px; color: #555; }

        /* ===== TABEL ===== */
        table.data { width: 100%; border-collapse: collapse; margin: 5px 0 12px; }
        table.data th, table.data td { border: 1px solid #999; padding: 5px 6px; font-size: 9.2px; vertical-align: top; }
        table.data th { background: #ececec; text-align: center; font-weight: bold; }
        .center { text-align: center; }

        /* warna tingkat risiko (Dampak x Likelihood) */
        .rk-low { background: #e8f5e9; text-align: center; font-weight: bold; }
        .rk-med { background: #fff3e0; text-align: center; font-weight: bold; }
        .rk-high { background: #ffebee; text-align: center; font-weight: bold; }
        .swatch { display: inline-block; width: 10px; height: 10px; border: 1px solid #999; vertical-align: -1px; }
        .sw-low { background: #2e7d32; }
        .sw-med { background: #f9a825; }
        .sw-high { background: #c62828; }

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

    // Tingkat risiko = Dampak x Likelihood: 1-4 RENDAH; 5-12 SEDANG; 13-25 TINGGI
    $tier = function ($r) {
        $score = $r->risk_score;
        if ($score === null || $score === '') {
            $score = ((int) ($r->impact ?? 0)) * ((int) ($r->probability ?? 0));
        }
        $score = (float) $score;
        if (!$score && $r->risk_level) {
            $lvl = strtolower($r->risk_level);
            if (str_contains($lvl, 'tinggi') || str_contains($lvl, 'high')) return ['Tinggi', 'rk-high', null];
            if (str_contains($lvl, 'sedang') || str_contains($lvl, 'medium') || str_contains($lvl, 'mid')) return ['Sedang', 'rk-med', null];
            return ['Rendah', 'rk-low', null];
        }
        if ($score <= 4) return ['Rendah', 'rk-low', $score];
        if ($score <= 12) return ['Sedang', 'rk-med', $score];
        return ['Tinggi', 'rk-high', $score];
    };
    $auditeeLabel = function ($r) {
        if ($r->academicProgram) return $r->academicProgram->degree_level . ' ' . $r->academicProgram->name;
        if ($r->unit) return $r->unit->name;
        return '—';
    };
    $groups = $risks->groupBy(fn ($r) => trim($r->standar_mutu ?: '') !== '' ? $r->standar_mutu : 'Lainnya')->sortKeys();
    $counts = ['Rendah' => 0, 'Sedang' => 0, 'Tinggi' => 0];
    foreach ($risks as $r) { $counts[$tier($r)[0]]++; }
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
        <div><span class="k">Jenis Laporan</span>: Laporan AMI Berbasis Risiko</div>
        <div><span class="k">Siklus AMI</span>: {{ $cycle->name }}</div>
        <div><span class="k">Tahun Akademik</span>: {{ $cycle->academic_year }} &mdash; Semester {{ $cycle->semester }}</div>
        <div><span class="k">Periode Pelaksanaan</span>: {{ $periodeTeks }}</div>
        <div><span class="k">Cakupan Laporan</span>:
            @if($level === 'prodi' && $program) {{ $program->degree_level }} {{ $program->name }}
            @else Rekapitulasi Seluruh Auditee (Institusi) @endif
        </div>
        <div><span class="k">Jumlah Auditee</span>: {{ $assignments->count() }} Program Studi / Unit Kerja</div>
        <div><span class="k">Risiko Teridentifikasi</span>: {{ $risks->count() }} Risiko Risk Register T.A. {{ $cycle->academic_year }}</div>
    </div>

    <div class="year">{{ $cycle->start_date ? $cycle->start_date->format('Y') : now()->year }}</div>
    <div class="note">Dokumen ini merupakan laporan resmi hasil kegiatan Audit Mutu Internal berbasis risiko<br>dan menjadi dasar tindak lanjut (RTL) perbaikan mutu.</div>
</div>
    @endif
<div class="page-break"></div>
@endif

{{-- ================= LEMBARAN PENGESAHAN ================= --}}
<div class="pengesahan">Lembaran Pengesahan</div>
<div class="quote">
    &ldquo;Setelah menimbang, membaca dan meneliti hasil yang dimaksud dalam Laporan Hasil Audit Mutu Internal
    Berbasis Risiko, Tahun Akademik <strong>{{ $cycle->academic_year }}</strong> Semester <strong>{{ $cycle->semester }}</strong>
    yang telah dilaksanakan dengan baik oleh Tim {{ config('app.name') }}, maka dokumen ini layak disahkan sebagai dokumen
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
    AMI Tahun Akademik {{ $cycle->academic_year }} Semester {{ $cycle->semester }} ini diselenggarakan dengan
    pendekatan berbasis risiko: setiap butir standar dinilai berdasarkan Dampak (Consequence) dan Kemungkinan
    Kejadian (Likelihood) apabila standar tidak terpenuhi secara optimal, sehingga prioritas perbaikan dapat
    ditentukan secara terukur.
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
<ul>
    <li>Menilai pemenuhan standar dengan mempertimbangkan dimensi risiko (dampak dan likelihood);</li>
    <li>Mengidentifikasi risiko prioritas beserta akar masalah dan rekomendasi penanganannya;</li>
    <li>Menyusun rekomendasi perbaikan dan Rencana Tindak Lanjut (RTL) yang terukur dan terarah;</li>
    <li>Menyediakan data objektif bagi pimpinan dalam pengambilan keputusan peningkatan mutu.</li>
</ul>

<div class="sub">1.4. Manfaat</div>
<ul>
    <li>Bagi pimpinan: informasi risiko mutu terkini sebagai dasar kebijakan dan alokasi sumber daya;</li>
    <li>Bagi program studi dan unit kerja: prioritas perbaikan yang jelas sesuai tingkat risiko;</li>
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
    <li>Penyusunan instrumen audit berbasis standar dan identifikasi risiko (dampak &amp; likelihood);</li>
    <li>Pelaksanaan Evaluasi Diri oleh program studi / unit kerja dan pengisian Risk Register;</li>
    <li>Audit lapangan: verifikasi dokumen, wawancara, dan observasi di unit teraudit;</li>
    <li>Penilaian tingkat risiko per butir standar serta penyusunan rekomendasi;</li>
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

<div class="sub">1.8. Skala Penilaian Risiko</div>
<p>
    AMI Berbasis Risiko menggunakan matriks penilaian yang mempertimbangkan dua dimensi: Dampak (Consequence) dan
    Kemungkinan Kejadian (Likelihood), masing-masing dengan skala 1&ndash;5.
</p>
<table class="data">
    <thead>
        <tr>
            <th style="width: 10%;">Skala</th>
            <th>Deskripsi Dampak / Likelihood</th>
            <th style="width: 14%;">Bobot</th>
        </tr>
    </thead>
    <tbody>
        <tr><td class="center">1</td><td>Tidak signifikan / Sangat jarang terjadi</td><td class="center">1</td></tr>
        <tr><td class="center">2</td><td>Minor / Jarang terjadi</td><td class="center">2</td></tr>
        <tr><td class="center">3</td><td>Sedang / Mungkin terjadi</td><td class="center">3</td></tr>
        <tr><td class="center">4</td><td>Besar / Sering terjadi</td><td class="center">4</td></tr>
        <tr><td class="center">5</td><td>Katastrofik / Hampir pasti terjadi</td><td class="center">5</td></tr>
    </tbody>
</table>
<div class="note-box">
    Tingkat Risiko = Dampak &times; Likelihood:
    <strong>1&ndash;4 = RENDAH</strong> <span class="swatch sw-low"></span>;
    <strong>5&ndash;12 = SEDANG</strong> <span class="swatch sw-med"></span>;
    <strong>13&ndash;25 = TINGGI</strong> <span class="swatch sw-high"></span>.
</div>
<div class="page-break"></div>

{{-- ================= BAB II HASIL AUDIT MUTU BERBASIS RISIKO ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB II</div>
    <div class="name">Hasil Audit Mutu Berbasis Risiko</div>
</div>
<p>
    Bagian ini menyajikan hasil audit pemenuhan Standar Nasional Pendidikan Tinggi (SN-Dikti) sesuai dokumen standar
    {{ config('app.name') }} berdasarkan Risk Register Tahun Akademik {{ $cycle->academic_year }}. Setiap butir standar
    dinilai berdasarkan dampak dan likelihood risiko apabila standar tidak terpenuhi secara optimal.
</p>

@forelse($groups as $namaStandar => $rows)
    <div class="sub">2.{{ $loop->iteration }}. {{ $namaStandar }}</div>
    @php
        $matchedStandard = $standars->first(fn ($s) => strcasecmp(trim($s->name), trim($namaStandar)) === 0)
            ?? $standars->first(fn ($s) => stripos($s->name, $namaStandar) !== false);
    @endphp
    <p>
        {{ $matchedStandard?->description ?? $matchedStandard?->pernyataan_standar
            ?? ('Kelompok standar ' . $namaStandar . ' dievaluasi melalui Risk Register: setiap butir dinilai berdasarkan dampak dan likelihood risiko, dilengkapi rekomendasi serta rencana tindak lanjut.') }}
    </p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 15%;">Butir Standar</th>
                <th style="width: 27%;">Isi Standar / Temuan AMI</th>
                <th style="width: 8%;">Dampak (1-5)</th>
                <th style="width: 9%;">Likelihood (1-5)</th>
                <th style="width: 12%;">Tingkat Risiko</th>
                <th style="width: 25%;">Rekomendasi / Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $ri => $r)
                @php $t = $tier($r); @endphp
                <tr>
                    <td class="center">{{ $ri + 1 }}</td>
                    <td>
                        {{ $r->butir_tilik ?: '—' }}
                        @if($level === 'institusi')
                            <div class="mini">{{ $auditeeLabel($r) }}</div>
                        @endif
                    </td>
                    <td>
                        {{ $r->risk_description }}
                        @if($r->temuan)
                            <div class="mini"><strong>Temuan:</strong> {{ $r->temuan }}</div>
                        @endif
                        @if($r->akar_masalah)
                            <div class="mini"><em>Akar masalah: {{ $r->akar_masalah }}</em></div>
                        @endif
                    </td>
                    <td class="center">{{ $r->impact ?? '—' }}</td>
                    <td class="center">{{ $r->probability ?? '—' }}</td>
                    <td class="{{ $t[1] }}">{{ $t[0] }}@if($t[2] !== null)<br>({{ $t[2] + 0 }})@endif</td>
                    <td>
                        {{ $r->mitigation_plan ?: '—' }}
                        <div class="mini">
                            @if($r->pic)PIC: {{ $r->pic }} &bull; @endif
                            @if($r->target_date)Target: {{ $tgl($r->target_date) }} @endif
                            @if($r->risk_category) &bull; Kategori: {{ $r->risk_category }} @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@empty
    <p>Belum ada data Risk Register Tahun Akademik {{ $cycle->academic_year }} pada cakupan ini.
    Isi Risk Register terlebih dahulu melalui menu <strong>Risk Register</strong> agar laporan berbasis risiko dapat disusun.</p>
@endforelse
<div class="page-break"></div>

{{-- ================= BAB III REKOMENDASI DAN TINDAK LANJUT ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB III</div>
    <div class="name">Rekomendasi dan Tindak Lanjut</div>
</div>

<div class="sub">3.1. Ringkasan Temuan Berbasis Risiko</div>
<p>
    Pada siklus <strong>{{ $cycle->name }}</strong> teridentifikasi sebanyak <strong>{{ $risks->count() }}</strong> risiko
    dari Risk Register T.A. {{ $cycle->academic_year }} dengan distribusi tingkat risiko sebagai berikut:
    <strong>{{ $counts['Rendah'] }}</strong> Rendah, <strong>{{ $counts['Sedang'] }}</strong> Sedang, dan
    <strong>{{ $counts['Tinggi'] }}</strong> Tinggi. Risiko tinggi wajib ditindaklanjuti terlebih dahulu sesuai
    rekomendasi yang tertera. Rekapitulasi disajikan pada Tabel 3.1.
</p>

<table class="data" style="width: 70%;">
    <thead>
        <tr>
            <th style="width: 26%;">Tingkat Risiko</th>
            <th style="width: 28%;">Rentang Skor (Dampak &times; Likelihood)</th>
            <th style="width: 20%;">Jumlah Risiko</th>
            <th style="width: 26%;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="rk-high">TINGGI</td>
            <td class="center">13 &ndash; 25</td>
            <td class="center">{{ $counts['Tinggi'] }}</td>
            <td class="center">Prioritas utama perbaikan</td>
        </tr>
        <tr>
            <td class="rk-med">SEDANG</td>
            <td class="center">5 &ndash; 12</td>
            <td class="center">{{ $counts['Sedang'] }}</td>
            <td class="center">Perlu pemantauan berkala</td>
        </tr>
        <tr>
            <td class="rk-low">RENDAH</td>
            <td class="center">1 &ndash; 4</td>
            <td class="center">{{ $counts['Rendah'] }}</td>
            <td class="center">Dipertahankan / diterima</td>
        </tr>
    </tbody>
</table>

<div class="tbl-caption">Tabel 3.1. Rekapitulasi Risiko dan Rekomendasi Tindak Lanjut</div>
<table class="data">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            @if($level === 'institusi')
                <th style="width: 16%;">Auditee</th>
            @endif
            <th style="width: 26%;">Risiko</th>
            <th style="width: 7%;">Dampak</th>
            <th style="width: 8%;">Likelihood</th>
            <th style="width: 11%;">Tingkat</th>
            <th>Rekomendasi / Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($risks->sortByDesc(fn ($r) => (float) ($r->risk_score ?? ((int) ($r->impact ?? 0)) * ((int) ($r->probability ?? 0))))->values() as $ri => $r)
            @php $t = $tier($r); @endphp
            <tr>
                <td class="center">{{ $ri + 1 }}</td>
                @if($level === 'institusi')
                    <td>{{ $auditeeLabel($r) }}</td>
                @endif
                <td>{{ $r->risk_description }}</td>
                <td class="center">{{ $r->impact ?? '—' }}</td>
                <td class="center">{{ $r->probability ?? '—' }}</td>
                <td class="{{ $t[1] }}">{{ $t[0] }}</td>
                <td>{{ $r->mitigation_plan ?: '—' }} <span class="mini">@if($r->pic)&bull; PIC: {{ $r->pic }}@endif</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $level === 'institusi' ? 7 : 6 }}" class="center">Belum ada risiko tercatat.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="sub">3.2. Rekomendasi dan Tindak Lanjut</div>
@php
    $risikoTinggi = $risks->filter(fn ($r) => $tier($r)[0] === 'Tinggi');
@endphp
<ul>
    @if($risikoTinggi->isNotEmpty())
        <li>Selesaikan terlebih dahulu {{ $risikoTinggi->count() }} risiko tingkat <strong>Tinggi</strong> sesuai PIC dan target waktu yang telah ditetapkan pada Risk Register;</li>
    @endif
    <li>Melakukan monitoring rutin terhadap risiko tingkat Sedang setiap akhir semester dan memperbarui skor risiko bila kondisi berubah;</li>
    <li>Menginformasikan seluruh rekomendasi kepada pimpinan dan auditee sebagai dasar Rencana Tindak Lanjut (RTL) mutu;</li>
    <li>Memverifikasi efektivitas mitigasi risiko pada siklus AMI berikutnya sebagai bagian siklus penjaminan mutu berkelanjutan.</li>
</ul>
<div class="page-break"></div>

{{-- ================= BAB IV PENUTUP ================= --}}
{!! $runheadHtml !!}
<div class="bab">
    <div class="no">BAB IV</div>
    <div class="name">Penutup</div>
</div>

<div class="sub">4.1. Kesimpulan</div>
<p>
    Berdasarkan pelaksanaan Audit Mutu Internal Berbasis Risiko Tahun Akademik {{ $cycle->academic_year }}
    Semester {{ $cycle->semester }} terhadap {{ $assignments->count() }} auditee, teridentifikasi
    {{ $risks->count() }} risiko pada Risk Register dengan rincian {{ $counts['Tinggi'] }} risiko Tinggi,
    {{ $counts['Sedang'] }} risiko Sedang, dan {{ $counts['Rendah'] }} risiko Rendah. Setiap risiko telah memiliki
    rekomendasi/mitigation plan beserta PIC sehingga menjadi dasar penyusunan Rencana Tindak Lanjut (RTL)
    peningkatan mutu institusi.
</p>

<div class="sub">4.2. Saran</div>
<ul>
    <li>Auditee hendaknya memperbarui Risk Register setiap semester agar skor risiko selalu mencerminkan kondisi terkini;</li>
    <li>Risiko tingkat Tinggi perlu ditindaklanjuti sesuai target waktu dan diverifikasi oleh {{ config('app.name') }};</li>
    <li>Hasil mitigasi risiko dievaluasi kembali pada siklus AMI berikutnya sebagai bagian peningkatan mutu berkelanjutan.</li>
</ul>

<p style="margin-top: 16px;">
    Demikian laporan ini dibuat dengan sebenar-benarnya untuk dapat dimanfaatkan sebagaimana mestinya dalam rangka
    peningkatan mutu pendidikan di {{ config('app.name') }}.
</p>

@include('admin.reports.lampiran_dokumen')

<div class="footer">
    Dokumen Laporan AMI Berbasis Risiko &mdash; {{ config('app.name') }} &mdash; Siklus {{ $cycle->name }} &mdash; Dicetak {{ now()->format('d/m/Y H:i') }}
</div>
</body>
</html>
