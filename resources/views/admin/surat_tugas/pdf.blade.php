<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 0; line-height: 1.6; }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }
        .page .kop-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }
        .page .body {
            position: relative;
            z-index: 1;
            padding: 42mm 24mm 20mm 24mm;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .title { font-weight: bold; letter-spacing: 1px; margin: 0 0 4px; }
        .nomor { margin: 0 0 14px; }

        table.field { width: 100%; margin: 8px 0 14px; border-collapse: collapse; }
        table.field td { vertical-align: top; padding: 2px 0; }
        table.field .label { width: 130px; }

        table.tim { width: 100%; border-collapse: collapse; margin: 10px 0 16px; }
        table.tim td { padding: 3px 0; vertical-align: top; }
        table.tim .label { width: 160px; }

        .sign { margin-top: 30px; width: 100%; }
        .sign td { vertical-align: top; }
        .sign .block { text-align: center; }
        .sign .block .tanggal { margin-bottom: 8px; }
        .sign .block .jabatan { margin-bottom: 4px; }
        .sign .block .ttd { height: 90px; margin: 2px 0 -25px 0; }
        .sign .block .ttd img { height: 90px; }
        .footer-note { margin-top: 26px; font-size: 9px; }
        .mt20 { margin-top: 20px; }
    </style>
</head>
<body>

@php
    $inst     = setting('institution_name', 'STMIK Mardira Indonesia');
    $ketua    = setting('ketua_nama', 'Dr. Marjito, M. Pd.');
    $ketuaNik = setting('ketua_nik', 'NIK. 95.01.017');
    $cycle    = $assignment->cycle;
    $auditor  = $assignment->auditor_name ?: ($assignment->auditor->name ?? '—');
    $nidn     = $assignment->auditor_nidn ?? '—';
    $nomor    = 'Nomor: ' . ($cycle?->id ?? '') . '/SPMI/AMI/' . now()->year;
    $periode  = ($cycle?->academic_year ?? '—') . ' (' . ($cycle?->semester ?? '—') . ')';
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $fmt = fn($d) => $d ? $d->format('d') . ' ' . $bulan[(int) $d->format('n')-1] . ' ' . $d->format('Y') : '—';
    $tglMulai = $fmt($cycle?->start_date);
    $tglSelesai = $fmt($cycle?->end_date);
    $tglFull = now()->format('d') . ' ' . $bulan[(int) now()->format('n')-1] . ' ' . now()->format('Y');
@endphp

<div class="page">
    <div class="kop-bg">@if($kop)<img src="{{ $kop }}" style="width:210mm; height:297mm;">@endif</div>

    <div class="body">
        <div class="center">
            <div class="title">SURAT TUGAS</div>
        </div>

        <div class="center nomor">{{ $nomor }}</div>

        <p style="text-indent: 30px; text-align: justify;">Dengan hormat,</p>

        <p style="text-align: justify;">
            Sehubungan dengan rencana pelaksanaan Audit Mutu Internal (AMI) {{ $cycle?->name }} Tahun Ajaran
            {{ $cycle?->academic_year ?? '—' }} di lingkungan {{ $inst }}, yang mencakup proses pembelajaran dan
            seluruh aspek penjaminan mutu pada {{ $assignment->auditee_label }}, kami menginformasikan bahwa AMI akan
            dilaksanakan mulai tanggal
            <strong>{{ $tglMulai }}</strong> hingga
            <strong>{{ $tglSelesai }}</strong>. Oleh karena itu, perlu diterbitkan surat
            tugas untuk tim pelaksana audit mutu internal dengan susunan tim sebagai berikut:
        </p>

        <table class="tim">
            <tr>
                <td class="label">Pengarah</td>
                <td>:</td>
                <td>Ketua {{ $inst }}</td>
            </tr>
            <tr>
                <td class="label">Ketua Tim Audit</td>
                <td>:</td>
                <td>{{ $auditor }}</td>
            </tr>
            <tr>
                <td class="label">NIDN</td>
                <td>:</td>
                <td>{{ $nidn }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Auditee</td>
                <td>:</td>
                <td>{{ $assignment->auditee_type_label }}</td>
            </tr>
            <tr>
                <td class="label">Nama Auditee</td>
                <td>:</td>
                <td>{{ $assignment->auditee_label }}</td>
            </tr>
            <tr>
                <td class="label">Periode AMI</td>
                <td>:</td>
                <td>{{ $periode }}</td>
            </tr>
        </table>

        <p style="text-align: justify;">
            Demikian surat tugas ini dibuat agar dapat menjalankan tugas dengan penuh tanggung jawab. Terima kasih.
        </p>

        <table class="sign">
            <tr>
                <td style="width:55%;"></td>
                <td class="block" style="width:45%;">
                    <div class="tanggal">Bandung, {{ $tglFull }}</div>
                    <div class="jabatan"><strong>Ketua {{ $inst }}</strong></div>
                    <div class="ttd">@if($ttd)<img src="{{ $ttd }}">@endif</div>
                    <u><strong>{{ $ketua }}</strong></u><br>
                    {{ $ketuaNik }}
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Jl. Soekarno-Hatta No.211 Leuwi Panjang Bandung Telp. (022) 5233429, 5230382
            Website: http://www.stmik-mi.ac.id, e-mail: info@stmik-mi.ac.id
        </div>
    </div>
</div>
</body>
</html>
