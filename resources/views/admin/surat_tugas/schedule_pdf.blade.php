<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Visitasi AMI</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 0; line-height: 1.5; }

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
        .title { font-weight: bold; letter-spacing: 1px; margin: 0 0 6px; }
        h2, h3 { margin: 4px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { text-align: center; }
        .sign { margin-top: 46px; width: 100%; }
        .sign td { vertical-align: top; }
        .sign .block { text-align: center; }
        .sign .block .ttd { height: 90px; margin: 2px 0 -25px 0; }
        .sign .block .ttd img { height: 90px; }
        .footer-note { margin-top: 26px; font-size: 9px; }
    </style>
</head>
<body>

@php
    $inst      = setting('institution_name', 'STMIK Mardira Indonesia');
    $instUpper = strtoupper($inst);
    $ketua     = setting('ketua_nama', 'Dr. Marjito, M. Pd.');
    $ketuaNik  = setting('ketua_nik', 'NIK. 95.01.017');
@endphp

<div class="page">
    <div class="kop-bg">@if($kop)<img src="{{ $kop }}" style="width:210mm; height:297mm;">@endif</div>

    <div class="body">
        <div class="center">
            <h3 class="title">JADWAL VISITASI AUDIT MUTU INTERNAL (AMI)</h3>
            <div>Siklus: <strong>{{ $cycle->name }}</strong> — {{ $cycle->academic_year }} ({{ $cycle->semester }})</div>
        </div>

        <table class="data">
            <thead>
                <tr>
                    <th style="width:20px;">No</th>
                    <th>Auditee</th>
                    <th>Jenis</th>
                    <th>Auditor</th>
                    <th>Periode AMI</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @forelse ($cycle->assignments as $assignment)
                    <tr>
                        <td class="center">{{ $i++ }}</td>
                        <td>{{ $assignment->auditee_label }}</td>
                        <td>{{ $assignment->auditee_type_label }}</td>
                        <td>{{ $assignment->auditor_name ?: ($assignment->auditor->name ?? '—') }}{{ $assignment->auditor_role ? ' (' . $assignment->auditor_role . ')' : '' }}</td>
                        <td>{{ $cycle->start_date?->format('d/m/Y') ?? '—' }} — {{ $cycle->end_date?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center">Belum ada penugasan pada siklus ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="sign">
            <tr>
                <td style="width:55%;"></td>
                <td class="block" style="width:45%;">
                    Ditetapkan di : {{ $cycle->lokasi ?? 'Bandung' }}<br>
                    Hari / Tanggal : {{ $cycle->start_date?->format('d F Y') ?? '—' }}
                    <br><br>
                    <strong>Ketua {{ $inst }}</strong>
                    <br>
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
