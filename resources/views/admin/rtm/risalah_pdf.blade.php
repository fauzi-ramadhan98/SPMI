<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Risalah RTM</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 18px; }
        .header .inst { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .header .title { font-size: 17px; font-weight: bold; margin: 6px 0 2px 0; }
        .header .sub { font-size: 11px; color: #555; }
        .info { margin-bottom: 14px; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 4px 6px; vertical-align: top; border: 1px solid #ccc; }
        .info td.k { font-weight: bold; width: 170px; background: #f6f6f6; }
        .lha-label { font-size: 12px; font-weight: bold; margin: 18px 0 8px 0; border-bottom: 2px solid #333; padding-bottom: 4px; text-transform: uppercase; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-grid td { border: 1px solid #bbb; padding: 8px 4px; text-align: center; width: 20%; }
        .summary-grid .num { font-size: 18px; font-weight: bold; }
        .summary-grid .cap { font-size: 9px; text-transform: uppercase; }
        .bg-red { background: #fdecea; color: #c0392b; }
        .bg-orange { background: #fef3e2; color: #e67e22; }
        .bg-blue { background: #eaf4fb; color: #2980b9; }
        .bg-green { background: #eafaf1; color: #27ae60; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th, table.data td { border: 1px solid #bbb; padding: 6px; text-align: left; font-size: 10px; }
        table.data th { background: #ececec; font-weight: bold; }
        .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .signature-section table { width: 100%; }
        .signature-section td { width: 50%; text-align: center; vertical-align: top; padding: 0 15px; }
        .signature-label { font-weight: bold; font-size: 11px; margin-bottom: 5px; }
        .signature-role { font-size: 10px; color: #555; margin-bottom: 60px; }
        .signature-line { border-top: 1px solid #333; padding-top: 5px; font-size: 11px; font-weight: bold; }
        .signature-title { font-size: 9px; color: #555; margin-top: 2px; }
        .footer { text-align: center; font-size: 9px; color: #777; position: fixed; bottom: 0; width: 100%; }
        .page-break { page-break-after: always; }
        .notulensi { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="header">
        <div class="inst">{{ config('app.name') }}</div>
        <div class="title">Risalah Rapat Tinjauan Manajemen (RTM)</div>
        <div class="sub">Siklus AMI: {{ $rtm->cycle?->name }} — {{ $rtm->cycle?->academic_year }} Semester {{ $rtm->cycle?->semester }}</div>
    </div>

    <div class="info">
        <table>
            <tr><td class="k">Judul RTM</td><td>: {{ $rtm->title }}</td></tr>
            <tr><td class="k">Hari / Tanggal</td><td>: {{ $rtm->meeting_date?->format('d F Y') ?? '-' }} {{ $rtm->meeting_time ? '· ' . $rtm->meeting_time->format('H:i') : '' }}</td></tr>
            <tr><td class="k">Tempat</td><td>: {{ $rtm->location ?? '-' }}</td></tr>
            <tr><td class="k">Siklus AMI</td><td>: {{ $rtm->cycle?->name }}</td></tr>
            <tr><td class="k">Status</td><td>: {{ strtoupper($rtm->status_label) }}</td></tr>
            <tr><td class="k">Disahkan</td><td>: {{ $rtm->approved_at?->format('d F Y H:i') . ' oleh ' . ($rtm->approver?->name ?? '-') }}</td></tr>
            <tr><td class="k">Dicetak</td><td>: {{ now()->format('d/m/Y H:i') }}</td></tr>
        </table>
    </div>

    <div class="lha-label">Ringkasan Hasil Audit Siklus</div>
    <table class="summary-grid">
        <tr>
            <td class="bg-red"><div class="num">{{ $cycleStats['kts_mayor'] }}</div><div class="cap">KTS Mayor</div></td>
            <td class="bg-orange"><div class="num">{{ $cycleStats['kts_minor'] }}</div><div class="cap">KTS Minor</div></td>
            <td class="bg-blue"><div class="num">{{ $cycleStats['ob'] }}</div><div class="cap">Observasi (OB)</div></td>
            <td class="bg-green"><div class="num">{{ $cycleStats['kts'] }}</div><div class="cap">Temuan KTS</div></td>
            <td><div class="num">{{ $cycleStats['scored'] }}</div><div class="cap">Butir Dinilai</div></td>
        </tr>
    </table>

    <div class="lha-label">Risalah / Notulensi Rapat</div>
    <p class="notulensi">{{ $rtm->notulensi ?: 'Belum ada notulensi.' }}</p>

    <div class="lha-label">Instruksi Tindak Lanjut</div>
    @if($rtm->instructions->count() > 0)
    <table class="data">
        <thead>
            <tr><th style="width:5%">No</th><th style="width:20%">Target</th><th style="width:60%">Instruksi</th><th style="width:15%">Target Selesai</th></tr>
        </thead>
        <tbody>
            @foreach($rtm->instructions as $idx => $instruction)
            <tr>
                <td style="text-align:center;">{{ $idx + 1 }}</td>
                <td>{{ $instruction->target_label }}</td>
                <td>{{ $instruction->instruction }}</td>
                <td>{{ $instruction->target_date?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="font-style: italic; color: #777;">Belum ada instruksi tindak lanjut.</p>
    @endif

    <div class="signature-section">
        <table>
            <tr>
                <td>
                    <div class="signature-label">Disusun oleh,</div>
                    <div class="signature-role">Koordinator / Sekretariat SPMI {{ config('app.name') }}</div>
                    <div class="signature-line">{{ $rtm->creator?->name ?? '.............................' }}</div>
                    <div class="signature-title">NIP/NIDN: .............................</div>
                </td>
                <td>
                    <div class="signature-label">Disahkan oleh,</div>
                    <div class="signature-role">Pimpinan {{ config('app.name') }}</div>
                    <div class="signature-line">{{ $rtm->approver?->name ?? '.............................' }}</div>
                    <div class="signature-title">NIP/NIDN: .............................</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">Risalah RTM — {{ config('app.name') }} — Siklus {{ $rtm->cycle?->name }}</div>
</body>
</html>