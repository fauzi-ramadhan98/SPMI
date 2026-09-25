{{-- ============ LAMPIRAN: SK, DOKUMEN SIKLUS & BUKTI KEGIATAN ============ --}}
@if(($builderMode ?? false))
{{-- Jalur Generate Laporan: daftar lampiran terurut; berkas PDF/gambar menyusul
     setelah halaman ini oleh PdfAssembler sesuai urutan yang disusun pengguna. --}}
<div class="page-break"></div>
{!! $runheadLampiran !!}
<div class="sec-title">Daftar Lampiran &amp; Berkas</div>

<div class="block">
    <div class="sub">Urutan Lampiran (sesuai susunan pada Generate Laporan)</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Judul Lampiran</th>
                <th style="width: 26%;">Sumber</th>
                <th style="width: 10%;">Jenis</th>
                <th style="width: 22%;">Berkas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lampiranRows as $row)
                <tr>
                    <td class="center">{{ $row['no'] }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['sumber'] }}</td>
                    <td class="center">{{ $row['jenis'] }}</td>
                    <td>{{ $row['berkas'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="center">Belum ada lampiran pada laporan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footnote">Berkas lampiran (PDF &amp; gambar) dicetak pada halaman-halaman berikutnya, tepat sesuai urutan daftar di atas.</div>
@else
<div class="page-break"></div>
{!! $runheadLampiran !!}
<div class="sec-title">Lampiran: SK, Dokumen &amp; Bukti Kegiatan</div>

<div class="block">
    <div class="sub">A. Surat Keputusan (SK)</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode SK</th>
                <th>Judul</th>
                <th style="width: 11%;">Kategori</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 14%;">Status</th>
                <th style="width: 17%;">File Pendukung</th>
            </tr>
        </thead>
        <tbody>
            @forelse($decreesLampiran as $i => $d)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $d['sk_no'] }}</td>
                    <td>{{ $d['judul'] }}</td>
                    <td class="center">{{ strtoupper($d['kategori']) }}</td>
                    <td class="center">{{ $d['tanggal'] ? $tgl($d['tanggal']) : '—' }}</td>
                    <td class="center">{{ strtoupper(str_replace('_', ' ', $d['status'])) }}</td>
                    <td>{{ $d['file_name'] ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="center">Belum ada SK pada siklus ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="block">
    <div class="sub">B. Dokumen Terkait Siklus</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 14%;">Kode Dokumen</th>
                <th>Judul</th>
                <th style="width: 13%;">Modul</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">File</th>
            </tr>
        </thead>
        <tbody>
            @forelse($docsLampiran as $i => $doc)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $doc['code'] ?: '—' }}</td>
                    <td>{{ $doc['title'] }}</td>
                    <td class="center">{{ str_replace('_', ' ', (string) $doc['module']) ?: '—' }}</td>
                    <td class="center">{{ $doc['doc_date'] ? $tgl($doc['doc_date']) : '—' }}</td>
                    <td>{{ $doc['file_name'] ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">Belum ada dokumen yang terhubung dengan siklus ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="block">
    <div class="sub">C. Bukti Kegiatan</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 26%;">Sumber</th>
                <th>Keterangan</th>
                <th style="width: 13%;">Kategori</th>
                <th style="width: 24%;">File / Tautan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($buktiLampiran as $i => $b)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $b['sumber'] }}</td>
                    <td>{{ $b['judul'] }}</td>
                    <td class="center">{{ $b['category'] ?: '—' }}</td>
                    <td>
                        {{ $b['file_name'] ?: ($b['link'] ?: '—') }}
                        @if($b['file_name'] && $b['link'])
                            <div class="mini">{{ $b['link'] }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="center">Belum ada bukti kegiatan yang diunggah pada siklus ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Galeri gambar lampiran: berkas SK/dokumen/bukti berupa gambar ditampilkan penuh, satu per halaman bila besar --}}
@foreach($decreesLampiran->filter(fn ($d) => $d['image']) as $d)
    <div class="bukti-fig">
        <img src="{{ $d['image']['src'] }}" width="{{ (int) $d['image']['w'] }}" height="{{ (int) $d['image']['h'] }}" alt="">
        <div class="bukti-cap">SK {{ $d['sk_no'] }} &mdash; {{ $d['judul'] }}</div>
    </div>
@endforeach
@foreach($docsLampiran->filter(fn ($doc) => $doc['image']) as $doc)
    <div class="bukti-fig">
        <img src="{{ $doc['image']['src'] }}" width="{{ (int) $doc['image']['w'] }}" height="{{ (int) $doc['image']['h'] }}" alt="">
        <div class="bukti-cap">Dokumen {{ $doc['code'] ?: '' }} &mdash; {{ $doc['title'] }}</div>
    </div>
@endforeach
@foreach($buktiLampiran->filter(fn ($b) => $b['image']) as $b)
    <div class="bukti-fig">
        <img src="{{ $b['image']['src'] }}" width="{{ (int) $b['image']['w'] }}" height="{{ (int) $b['image']['h'] }}" alt="">
        <div class="bukti-cap">{{ $b['judul'] }} &mdash; {{ $b['sumber'] }}@if($b['file_name']) ({{ $b['file_name'] }})@endif</div>
    </div>
@endforeach

<div class="footnote">Berkas PDF/Word tidak dapat digabungkan ke dalam dokumen ini; nama file tercatat pada tabel di atas dan berkas asli tetap tersimpan pada aplikasi.</div>
@endif
