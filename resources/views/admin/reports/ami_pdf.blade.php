<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Audit Mutu Internal (LHA)</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .page-break { page-break-after: always; }

        /* COVER */
        .cover { text-align: center; padding-top: 140px; }
        .cover .institute { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .cover .doc-title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 6px 0; }
        .cover .doc-subtitle { font-size: 16px; font-weight: bold; margin-bottom: 30px; }
        .cover .cover-box { border: 2px solid #333; padding: 22px 30px; margin: 26px auto 0; width: 75%; }
        .cover .cover-box div { padding: 3px 0; font-size: 12px; }
        .cover .cover-box .label { font-weight: bold; }
        .cover .footer-note { margin-top: 46px; font-size: 11px; color: #555; font-style: italic; }

        /* HEADER INFO */
        .lha-title { font-size: 15px; font-weight: bold; text-transform: uppercase; text-align: center; margin: 0 0 4px 0; }
        .lha-subtitle { font-size: 12px; font-weight: bold; text-align: center; margin: 0 0 16px 0; }
        .lha-label { font-size: 13px; font-weight: bold; margin: 18px 0 8px 0; border-bottom: 2px solid #333; padding-bottom: 4px; text-transform: uppercase; }

        .info { margin-bottom: 14px; }
        .info table { width: 100%; border-collapse: collapse; border: 1px solid #ccc; }
        .info td { padding: 5px 8px; vertical-align: top; border: 1px solid #e2e2e2; }
        .info td.k { font-weight: bold; width: 170px; background: #f6f6f6; }

        .section-title { font-size: 12px; font-weight: bold; background-color: #f0f0f0; padding: 7px 10px; margin: 20px 0 10px 0; border-left: 4px solid #333; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data th, table.data td { border: 1px solid #bbb; padding: 6px 7px; text-align: left; font-size: 10px; }
        table.data th { background-color: #ececec; font-weight: bold; text-align: center; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; color: white; background: #666; }
        .badge-kts { background-color: #c0392b; }
        .badge-ob { background-color: #f39c12; color: #fff; }

        /* Ringkasan eksekutif */
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-grid td { border: 1px solid #bbb; padding: 10px 6px; text-align: center; width: 20%; }
        .summary-grid .num { font-size: 20px; font-weight: bold; }
        .summary-grid .cap { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-red { background: #fdecea; color: #c0392b; }
        .bg-orange { background: #fef3e2; color: #e67e22; }
        .bg-blue { background: #eaf4fb; color: #2980b9; }
        .bg-green { background: #eafaf1; color: #27ae60; }

        /* Signature */
        .signature-section { margin-top: 46px; page-break-inside: avoid; }
        .signature-section table { width: 100%; }
        .signature-section td { width: 50%; text-align: center; vertical-align: top; padding: 0 15px; }
        .signature-label { font-weight: bold; font-size: 11px; margin-bottom: 5px; }
        .signature-role { font-size: 10px; color: #555; margin-bottom: 62px; }
        .signature-line { border-top: 1px solid #333; padding-top: 5px; font-size: 11px; font-weight: bold; }
        .signature-title { font-size: 9px; color: #555; margin-top: 2px; }
        .footer { text-align: center; font-size: 9px; color: #777; width: 100%; position: fixed; bottom: 0; }
    </style>
</head>
<body>

    {{-- ============ COVER ============ --}}
    <div class="cover">
        <div class="institute">{{ config('app.name') }}</div>
        <div class="doc-title">Laporan Hasil Audit Mutu Internal</div>
        <div class="doc-subtitle">(LHA-AMI)</div>

        <div class="cover-box">
            @if($level === 'prodi' && $program)
                <div><span class="label">Program Studi:</span> {{ $program->degree_level }} {{ $program->name }}</div>
                <div><span class="label">Fakultas:</span> {{ $program->faculty ?? '-' }}</div>
            @else
                <div><span class="label">Cakupan Laporan:</span> Rekapitulasi Seluruh Auditee (Institusi)</div>
            @endif
            <div><span class="label">Siklus AMI:</span> {{ $cycle->name }}</div>
            <div><span class="label">Tahun Akademik:</span> {{ $cycle->academic_year }} — Semester {{ $cycle->semester }}</div>
            <div><span class="label">Periode Pelaksanaan:</span>
                {{ $cycle->start_date ? \Carbon\Carbon::parse($cycle->start_date)->format('d/m/Y') : '-' }}
                s.d
                {{ $cycle->end_date ? \Carbon\Carbon::parse($cycle->end_date)->format('d/m/Y') : '-' }}
            </div>
            <div><span class="label">Jumlah Auditee:</span> {{ $assignments->count() }} Program Studi / Unit Kerja</div>
        </div>

        <div class="footer-note">Dokumen ini merupakan laporan resmi hasil audit mutu internal<br>dan menjadi dasar tindak lanjut (RTL) perbaikan mutu.</div>
        <div class="page-break"></div>
    </div>

    {{-- ============ HALAMAN PENGESAHAN ============ --}}
    <div class="lha-title">Laporan Hasil Audit Mutu Internal (LHA-AMI)</div>
    <div class="lha-subtitle">{{ config('app.name') }} — Siklus {{ $cycle->name }}</div>

    <table class="info">
        <tr><td class="k">Siklus Audit</td><td>: {{ $cycle->name }}</td></tr>
        <tr><td class="k">Tahun Akademik</td><td>: {{ $cycle->academic_year }} - Semester {{ $cycle->semester }}</td></tr>
        <tr><td class="k">Periode Pelaksanaan</td><td>:
            {{ $cycle->start_date ? \Carbon\Carbon::parse($cycle->start_date)->format('d/m/Y') : '-' }}
            s.d
            {{ $cycle->end_date ? \Carbon\Carbon::parse($cycle->end_date)->format('d/m/Y') : '-' }}
        </td></tr>
        <tr><td class="k">Cakupan Laporan</td><td>:
            @if($level === 'prodi' && $program) {{ $program->degree_level }} {{ $program->name }} @else Rekapitulasi Seluruh Auditee (Institusi) @endif
        </td></tr>
        <tr><td class="k">Jumlah Auditee Dilaporkan</td><td>: {{ $summary['auditee_count'] }} Program Studi / Unit Kerja</td></tr>
        <tr><td class="k">Tanggal Laporan</td><td>: {{ now()->format('d/m/Y') }}</td></tr>
    </table>

    <div class="signature-section">
        <table>
            <tr>
                <td>
                    <div class="signature-label">Disusun oleh,</div>
                    <div class="signature-role">Koordinator / Ketua SPMI {{ config('app.name') }}</div>
                    <div class="signature-line">( ...................................................... )</div>
                    <div class="signature-title">NIP/NIDN: ................................</div>
                </td>
                <td>
                    <div class="signature-label">Mengetahui,</div>
                    <div class="signature-role">Pimpinan {{ config('app.name') }}</div>
                    <div class="signature-line">( ...................................................... )</div>
                    <div class="signature-title">NIP/NIDN: ................................</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="page-break"></div>

    {{-- ============ RINGKASAN EKSEKUTIF ============ --}}
    <div class="lha-title">Ringkasan Eksekutif</div>
    <div class="lha-subtitle">Hasil Audit Mutu Internal {{ $cycle->name }}</div>

    <table class="summary-grid">
        <tr>
            <td class="bg-red">
                <div class="num">{{ $summary['kts_mayor'] }}</div>
                <div class="cap">KTS Mayor<br>(Tidak Tercapai Sedang)</div>
            </td>
            <td class="bg-orange">
                <div class="num">{{ $summary['kts_minor'] }}</div>
                <div class="cap">KTS Minor<br>(Tidak Tercapai Ringan)</div>
            </td>
            <td class="bg-blue">
                <div class="num">{{ $summary['ob'] }}</div>
                <div class="cap">Observasi<br>(OB)</div>
            </td>
            <td class="bg-green">
                <div class="num">{{ $summary['sesuai'] }}</div>
                <div class="cap">Sesuai / Melampaui<br>Standar</div>
            </td>
            <td>
                <div class="num">{{ $summary['instrument_count'] }}</div>
                <div class="cap">Indikator<br>Dinilai</div>
            </td>
        </tr>
    </table>

    <div style="font-size: 10px; color: #555; margin-bottom: 8px;">
        Berdasarkan audit terhadap <strong>{{ $summary['auditee_count'] }}</strong> auditee (program studi / unit kerja) pada siklus <strong>{{ $cycle->name }}</strong>,
        terdapat <strong>{{ $summary['kts_mayor'] }}</strong> temuan <span style="color:#c0392b;">KTS Mayor</span>,
        <strong>{{ $summary['kts_minor'] }}</strong> temuan <span style="color:#e67e22;">KTS Minor</span>,
        dan <strong>{{ $summary['ob'] }}</strong> catatan <span style="color:#2980b9;">Observasi (OB)</span>.
        Seluruh temuan Ketidaksesuaian (KTS) wajib ditindaklanjuti melalui Rencana Tindak Lanjut (RTL) auditee.
    </div>

    <div class="lha-label">Rekapitulasi Temuan Per Auditee</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 26%">Auditee</th>
                <th style="width: 10%">Tipe</th>
                <th style="width: 14%">Auditor</th>
                <th style="width: 10%">Borang</th>
                <th style="width: 12%">KTS Mayor</th>
                <th style="width: 12%">KTS Minor</th>
                <th style="width: 11%">OB</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $idx => $assignment)
                @php
                    $instList = $assignment->instruments;
                    $perMayor = $instList->filter(fn($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Mayor'))->count();
                    $perMinor = $instList->filter(fn($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Minor'))->count();
                    $perOb = $assignment->findings->where('type', 'OB')->count();
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $assignment->auditee_label }}</td>
                    <td style="text-align:center;">{{ $assignment->auditee_type_label }}</td>
                    <td>{{ $assignment->auditor_name }}</td>
                    <td style="text-align:center;">{{ $instList->count() }}</td>
                    <td style="text-align:center;">{{ $perMayor }}</td>
                    <td style="text-align:center;">{{ $perMinor }}</td>
                    <td style="text-align:center;">{{ $perOb }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center; font-style: italic; color: #777;">Belum ada auditee yang dilaporkan pada cakupan ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($summary['total_findings'] > 0)
    <div class="lha-label">Daftar Temuan (KTS / OB)</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 10%">Tipe</th>
                <th style="width: 16%">Auditee</th>
                <th style="width: 22%">Standar / Kriteria</th>
                <th style="width: 47%">Deskripsi Temuan & Rencana Perbaikan</th>
            </tr>
        </thead>
        <tbody>
            @php $findingNo = 1; @endphp
            @foreach($assignments as $assignment)
                @foreach($assignment->findings as $finding)
                <tr>
                    <td style="text-align:center;">{{ $findingNo++ }}</td>
                    <td style="text-align:center;">
                        @if($finding->type == 'KTS')
                            <span class="badge badge-kts">KTS</span>
                        @else
                            <span class="badge badge-ob">OB</span>
                        @endif
                    </td>
                    <td>{{ $assignment->auditee_label }}</td>
                    <td>{{ $finding->criteria }}</td>
                    <td>
                        {{ $finding->description }}
                        @if($finding->corrective_action)
                            <div style="margin-top:4px;"><strong>Rencana Perbaikan:</strong> {{ $finding->corrective_action }}
                                @if($finding->target_date) (target: {{ \Carbon\Carbon::parse($finding->target_date)->format('d/m/Y') }}) @endif
                            </div>
                        @endif
                        <div style="margin-top:3px; font-size:9px; color:#555;">Status: {{ strtoupper(str_replace('_', ' ', $finding->status)) }}</div>
                    </td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @endif
    <div class="page-break"></div>

    {{-- ============ LAMPIRAN: BORANG PER AUDITEE ============ --}}
    <div class="lha-title">Lampiran</div>
    <div class="lha-subtitle">Rekap Instrumen, Borang, dan Temuan Detail Per Auditee</div>

    @foreach($assignments as $assignment)
    <div class="section-title">
        Lampiran {{ $loop->iteration }}: Hasil Audit {{ $assignment->auditee_label }} ({{ $assignment->auditee_type_label }})
    </div>

    <table class="info">
        <tr><td class="k">Auditee</td><td>: {{ $assignment->auditee_label }}</td></tr>
        <tr><td class="k">Auditor Ditugaskan</td><td>: {{ $assignment->auditor_name }} ({{ $assignment->auditor_type }}) — {{ $assignment->auditor_role }}</td></tr>
        <tr><td class="k">Status Borang</td><td>: {{ strtoupper($assignment->status) }}</td></tr>
    </table>

    <h4 style="margin: 10px 0 5px 0;">Rekap Instrumen & Borang</h4>
    @if($assignment->instruments->count() > 0)
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 25%">Standar / Kriteria</th>
                <th style="width: 38%">Indikator Penilaian</th>
                <th style="width: 10%">Skor (0-4)</th>
                <th style="width: 22%">Kategori Ketercapaian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignment->instruments as $idx => $inst)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $inst->criteria }}</td>
                <td>{{ $inst->indicator }}</td>
                <td style="text-align: center; font-weight: bold; font-size: 13px;">{{ $inst->score ?? '-' }}</td>
                <td style="text-align: center;">{{ str_replace('_', ' ', $inst->finding_category ?? '-') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="font-style: italic; color: #777;">Belum ada borang yang diisi oleh auditor.</p>
    @endif

    <h4 style="margin: 15px 0 5px 0;">Rekap Temuan & Tindak Lanjut</h4>
    @if($assignment->findings->count() > 0)
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 10%">Tipe</th>
                <th style="width: 18%">Standar Kriteria</th>
                <th style="width: 36%">Deskripsi Temuan</th>
                <th style="width: 31%">Rencana Perbaikan & Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignment->findings as $idx => $finding)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td style="text-align: center;">
                    @if($finding->type == 'KTS')
                        <span class="badge badge-kts">KTS</span>
                    @else
                        <span class="badge badge-ob">OB</span>
                    @endif
                </td>
                <td>{{ $finding->criteria }}</td>
                <td>
                    {{ $finding->description }}
                    @if($finding->root_cause)
                    <div style="margin-top: 5px; border-top: 1px dotted #ccc; padding-top: 5px;">
                        <em><strong>Akar Masalah:</strong> {{ $finding->root_cause }}</em>
                    </div>
                    @endif
                </td>
                <td>
                    @if($finding->corrective_action)
                        {{ $finding->corrective_action }}
                        <div style="margin-top: 5px; font-size: 9px;">Target Selesai: {{ $finding->target_date ? \Carbon\Carbon::parse($finding->target_date)->format('d/m/Y') : '-' }}</div>
                    @else
                        <span style="font-style: italic; color: #aaa;">Belum diisi...</span>
                    @endif
                    <br><br>
                    <strong>Status:</strong> {{ strtoupper(str_replace('_', ' ', $finding->status)) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="font-style: italic; color: #777;">Tidak ada catatan temuan KTS/OB.</p>
    @endif

    {{-- Signature per auditee --}}
    <div class="signature-section">
        <table>
            <tr>
                <td>
                    <div class="signature-label">Auditor</div>
                    <div class="signature-role">{{ $assignment->auditor_type }}</div>
                    <div class="signature-line">{{ $assignment->auditor_name }}</div>
                    <div class="signature-title">NIDN/NIK: {{ $assignment->auditor_nidn ?? '-' }}</div>
                </td>
                <td>
                    <div class="signature-label">Auditee</div>
                    @if($assignment->academicProgram)
                        <div class="signature-role">Ketua Program Studi {{ $assignment->academicProgram->degree_level }} {{ $assignment->academicProgram->name }}</div>
                        <div class="signature-line">{{ $assignment->academicProgram->head_name ?? '................................' }}</div>
                        <div class="signature-title">NIDN/NIK: {{ $assignment->academicProgram->head_nidn ?? '-' }}</div>
                    @else
                        <div class="signature-role">Kepala Unit Kerja {{ $assignment->unit->name ?? '' }}</div>
                        <div class="signature-line">{{ $assignment->unit->head_name ?? '................................' }}</div>
                        <div class="signature-title">NIDN/NIK: {{ $assignment->unit->head_nidn ?? '-' }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach

    <div class="footer">Dokumen LHA-AMI — {{ config('app.name') }} — Siklus {{ $cycle->name }} — Dicetak {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>