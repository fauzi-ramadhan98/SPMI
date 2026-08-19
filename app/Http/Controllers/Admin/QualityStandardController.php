<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QualityStandard;
use App\Models\StandardVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QualityStandardController extends Controller
{
    public function index()
    {
        $standards = QualityStandard::with(['decree'])->orderBy('kode_standar')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.quality_standards.index', compact('standards'));
    }

    public function create()
    {
        return view('admin.quality_standards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_standar'      => 'nullable|string|max:100',
            'name'              => 'required|string|max:255',
            'pernyataan_standar'=> 'nullable|string',
            'rujukan'           => 'nullable|string|max:255',
            'type'              => 'nullable|in:IKU,IKT',
            'description'       => 'nullable|string',
            'iku'               => 'nullable|array',
            'iku.*.text'        => 'nullable|string|max:2000',
            'iku.*.target'      => 'nullable|string|max:255',
            'ikt'               => 'nullable|array',
            'ikt.*.text'        => 'nullable|string|max:2000',
            'ikt.*.target'      => 'nullable|string|max:255',
            'file'              => 'nullable|file|extensions:pdf,doc,docx,xls,xlsx,csv|max:10240',
        ], [
            'file.extensions' => 'Format dokumen harus berupa PDF, Word, Excel, atau CSV.',
            'file.max'        => 'Ukuran dokumen maksimal adalah 10 MB.',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['name'] = $validated['name'];
        $validated['type'] = $validated['type'] ?? 'IKU';
        $validated['indicators'] = [
            'iku' => $this->indicatorRows($request->input('iku')),
            'ikt' => $this->indicatorRows($request->input('ikt')),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $ext = strtolower($file->getClientOriginalExtension());
            $importedCount = 0;
            
            // Cek jika rute ini lewat quick upload form (di mana user berharap impor data)
            if (empty($validated['pernyataan_standar']) || $validated['pernyataan_standar'] === '-') {
                // Gunakan OpenSpout untuk file XLSX/CSV
                if ($ext === 'xlsx' || $ext === 'csv') {
                    try {
                        if ($ext === 'csv') {
                           $reader = new \OpenSpout\Reader\CSV\Reader();
                        } else {
                           $reader = new \OpenSpout\Reader\XLSX\Reader();
                        }
                        $reader->open($filePath);
                        $lastStandard = null;
                        $ikuCounter = 1;
                        $iktCounter = 1;
                        // Mapping indeks dinamis agar anti-geser kolom
                        $headerMap = null;
                        
                        foreach ($reader->getSheetIterator() as $sheet) {
                            foreach ($sheet->getRowIterator() as $row) {
                                $cellsArray = $row->toArray();
                                $cellsText = strtolower(implode(' ', $cellsArray));
                                
                                // Deteksi apakah ini baris header
                                if (is_null($headerMap)) {
                                    if (str_contains($cellsText, 'standar') || str_contains($cellsText, 'indikator') || str_contains($cellsText, 'target') || str_contains($cellsText, 'kode')) {
                                        $headerMap = [];
                                        foreach($cellsArray as $i => $h) {
                                            $hstr = strtolower(trim((string)$h));
                                            if (str_contains($hstr, 'kode')) $headerMap['kode'] = $i;
                                            elseif (str_contains($hstr, 'nama')) $headerMap['nama'] = $i;
                                            elseif (str_contains($hstr, 'pernyata')) $headerMap['pernyataan'] = $i;
                                            elseif (str_contains($hstr, 'rujukan')) $headerMap['rujukan'] = $i;
                                            elseif (str_contains($hstr, 'utama') || str_contains($hstr, 'iku')) $headerMap['iku'] = $i;
                                            elseif (str_contains($hstr, 'tambahan') || str_contains($hstr, 'ikt')) $headerMap['ikt'] = $i;
                                            elseif (str_contains($hstr, 'target')) $headerMap['target'] = $i;
                                            elseif (str_contains($hstr, 'link') || str_contains($hstr, 'dokumen')) $headerMap['link'] = $i;
                                            elseif (str_contains($hstr, 'indikator') && !isset($headerMap['iku'])) $headerMap['iku'] = $i;
                                        }
                                        continue; 
                                    } else {
                                        // Jika tidak ada baris header yang terdeteksi, berasumsi urutan standar template baru
                                        $headerMap = ['kode'=>0, 'nama'=>1, 'pernyataan'=>2, 'rujukan'=>3, 'iku'=>4, 'ikt'=>5, 'target'=>6, 'link'=>7];
                                    }
                                }
                                
                                $cells = array_pad($cellsArray, 100, ''); // Cegah Out of bounds
                                
                                $kode       = trim((string)$cells[ $headerMap['kode'] ?? 99 ]);
                                $nama       = trim((string)$cells[ $headerMap['nama'] ?? 99 ]);
                                $pernyataan = trim((string)$cells[ $headerMap['pernyataan'] ?? 99 ]);
                                $rujukan    = trim((string)$cells[ $headerMap['rujukan'] ?? 99 ]);
                                $iku        = trim((string)$cells[ $headerMap['iku'] ?? 99 ]);
                                $ikt        = trim((string)$cells[ $headerMap['ikt'] ?? 99 ]);
                                $target     = trim((string)$cells[ $headerMap['target'] ?? 99 ]);
                                $linkFile   = trim((string)$cells[ $headerMap['link'] ?? 99 ]);
                                
                                if (!empty($nama) || !empty($pernyataan)) {
                                    if ($kode === 'Kode Standar') continue; // double safety if header wasn't skipped
                                    
                                    if (empty($nama)) $nama = $pernyataan;

                                    $ikuCounter = 1;
                                    $iktCounter = 1;
                                    
                                    $desc = "";
                                    if ($iku && $iku !== '-') { $desc .= "IKU $ikuCounter: $iku\n"; $ikuCounter++; }
                                    if ($ikt && $ikt !== '-') { $desc .= "IKT $iktCounter: $ikt\n"; $iktCounter++; }

                                    // Bersihkan target jika hanya berisi strip/hyphen
                                    if ($target === '-') $target = '';

                                    // Prepare link
                                    $fileData = [];
                                    if (!empty($linkFile) && $linkFile !== '-') {
                                        // jika ada teks tapi tidak diawali http, kasih peringatan / rapikan
                                        if (!str_starts_with($linkFile, 'http')) {
                                            $linkFile = 'https://' . $linkFile;
                                        }
                                        $fileData = [
                                            'file_path' => $linkFile,
                                            'file_name' => 'Dokumen Eksternal',
                                            'file_type' => 'link'
                                        ];
                                    }

                                    $indIku = ($iku && $iku !== '-') ? [['text' => $iku, 'target' => $target]] : [];
                                    $indIkt = ($ikt && $ikt !== '-') ? [['text' => $ikt, 'target' => $target]] : [];

                                    $lastStandard = QualityStandard::create(array_merge([
                                        'kode_standar'       => $kode,
                                        'name'               => $nama,
                                        'pernyataan_standar' => $pernyataan,
                                        'rujukan'            => $rujukan,
                                        'type'               => 'IKU',
                                        'target_value'       => $target,
                                        'description'        => trim($desc),
                                        'indicators'         => ['iku' => $indIku, 'ikt' => $indIkt],
                                        'is_active'          => true,
                                    ], $fileData));
                                    $importedCount++;
                                } else {
                                    // Jika baris kosong tapi ada lanjuan indikator (merge row)
                                    if ($lastStandard && (!empty($iku) || !empty($ikt))) {
                                        $desc = "";
                                        if ($iku && $iku !== '-') { $desc .= "\n---\nIKU $ikuCounter: $iku"; $ikuCounter++; }
                                        if ($ikt && $ikt !== '-') { $desc .= "\n---\nIKT $iktCounter: $ikt"; $iktCounter++; }
                                        
                                        $lastStandard->description .= $desc;
                                        if (!empty($target) && $target !== '-') {
                                            $lastStandard->target_value .= ($lastStandard->target_value ? "\n---\n" : "") . $target;
                                        }

                                        $indicatorData = is_array($lastStandard->indicators) ? $lastStandard->indicators : ['iku' => [], 'ikt' => []];
                                        if ($iku && $iku !== '-') {
                                            $indicatorData['iku'][] = ['text' => $iku, 'target' => ($target && $target !== '-') ? $target : ''];
                                        }
                                        if ($ikt && $ikt !== '-') {
                                            $indicatorData['ikt'][] = ['text' => $ikt, 'target' => ($target && $target !== '-') ? $target : ''];
                                        }
                                        $lastStandard->indicators = $indicatorData;

                                        $lastStandard->save();
                                    }
                                }
                            }
                            break; // hanya sheet pertama
                        }
                        $reader->close();
                    } catch (\Exception $e) { /* ignore to fallback */ }
                } 
                
                if ($importedCount > 0) {
                    return redirect()->route('admin.quality-standards.index')->with('success', "$importedCount Standar Mutu berhasil diimpor dari dokumen.");
                } else {
                    return redirect()->route('admin.quality-standards.index')->with('error', 'Gagal membaca data dari dokumen. Anda wajib menggunakan Template EXCEL (.xlsx) terbaru dan memastikan kolom tidak diubah.');
                }
            }
            
            // Menyimpan file jika tidak diimpor atau berasal dari form Modal biasa
            $validated['file_path'] = $file->store('quality_standards', 'local');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $fileExt = strtolower($file->getClientOriginalExtension());
            $validated['file_type'] = in_array($fileExt, ['xls', 'xlsx', 'csv']) ? 'excel'
                : (in_array($fileExt, ['doc', 'docx']) ? 'word' : 'pdf');
        }

        QualityStandard::create($validated);
        return redirect()->route('admin.quality-standards.index')->with('success', 'Standar Mutu berhasil ditambahkan.');
    }

    public function show(QualityStandard $qualityStandard)
    {
        return redirect()->route('admin.quality-standards.edit', $qualityStandard);
    }

    public function edit(QualityStandard $qualityStandard)
    {
        return view('admin.quality_standards.edit', compact('qualityStandard'));
    }

    public function update(Request $request, QualityStandard $qualityStandard)
    {
        $validated = $request->validate([
            'kode_standar'      => 'nullable|string|max:100',
            'name'              => 'required|string|max:255',
            'pernyataan_standar'=> 'required|string',
            'rujukan'           => 'nullable|string|max:255',
            'type'              => 'nullable|in:IKU,IKT',
            'description'       => 'nullable|string',
            'iku'               => 'nullable|array',
            'iku.*.text'        => 'nullable|string|max:2000',
            'iku.*.target'      => 'nullable|string|max:255',
            'ikt'               => 'nullable|array',
            'ikt.*.text'        => 'nullable|string|max:2000',
            'ikt.*.target'      => 'nullable|string|max:255',
            'file'              => 'nullable|file|extensions:pdf,doc,docx,xls,xlsx,csv|max:10240',
        ], [
            'file.extensions' => 'Format dokumen harus berupa PDF, Word, Excel, atau CSV.',
            'file.max'        => 'Ukuran dokumen maksimal adalah 10 MB.',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['name'] = $validated['name'];
        $validated['type'] = $validated['type'] ?? ($qualityStandard->type ?? 'IKU');
        $validated['indicators'] = [
            'iku' => $this->indicatorRows($request->input('iku')),
            'ikt' => $this->indicatorRows($request->input('ikt')),
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($qualityStandard->file_path && Storage::disk('local')->exists($qualityStandard->file_path)) {
                Storage::disk('local')->delete($qualityStandard->file_path);
            }

            $file = $request->file('file');
            $validated['file_path'] = $file->store('quality_standards', 'local');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $ext = strtolower($file->getClientOriginalExtension());
            $validated['file_type'] = in_array($ext, ['xls', 'xlsx']) ? 'excel'
                : (in_array($ext, ['doc', 'docx']) ? 'word' : 'pdf');
        }

        // Snapshot versi lama sebelum diubah (versioning / riwayat revisi)
        StandardVersion::create([
            'quality_standard_id' => $qualityStandard->id,
            'version'             => $qualityStandard->version ?? 1,
            'snapshot'            => $qualityStandard->only([
                'kode_standar', 'pernyataan_standar', 'rujukan', 'name', 'type',
                'description', 'target_value', 'indicators', 'is_active', 'file_path', 'file_name',
            ]),
            'changed_by'          => auth()->id(),
        ]);

        // Naikkan nomor versi
        $validated['version'] = ($qualityStandard->version ?? 1) + 1;

        $qualityStandard->update($validated);
        return redirect()->route('admin.quality-standards.index')->with('success', 'Standar Mutu berhasil diperbarui (Revisi v' . $validated['version'] . ').');
    }

    public function destroy(QualityStandard $qualityStandard)
    {
        if ($qualityStandard->file_path && Storage::disk('local')->exists($qualityStandard->file_path)) {
            Storage::disk('local')->delete($qualityStandard->file_path);
        }
        $qualityStandard->delete();
        return redirect()->route('admin.quality-standards.index')->with('success', 'Standar Mutu berhasil dihapus.');
    }

    /**
     * Download the uploaded file for a standard
     */
    public function downloadFile(QualityStandard $qualityStandard)
    {
        if (!$qualityStandard->file_path || !Storage::disk('local')->exists($qualityStandard->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return Storage::disk('local')->download($qualityStandard->file_path, $qualityStandard->file_name);
    }

    /**
     * Normalisasi baris indikator dari input form (iku/ikt).
     * Buang baris tanpa teks, susun ulang index berurutan.
     */
    private function indicatorRows($rows): array
    {
        $out = [];
        $rows = is_array($rows) ? $rows : [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $text = trim((string)($row['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $out[] = [
                'text'   => $text,
                'target' => trim((string)($row['target'] ?? '')),
            ];
        }

        return $out;
    }

    /**
     * Download a blank template (Word or Excel)
     */
    public function downloadTemplate(Request $request)
    {
        $format = $request->query('format', 'xlsx');

        if ($format === 'xlsx') {
            return $this->generateExcelTemplate();
        } elseif ($format === 'docx') {
            return $this->generateWordTemplate();
        } else {
            return $this->generatePdfTemplate();
        }
    }

    private function generateExcelTemplate()
    {
        $tempPath = storage_path('app/template_standar_mutu.xlsx');
        
        try {
            if (class_exists('\OpenSpout\Writer\XLSX\Writer')) {
                $writer = new \OpenSpout\Writer\XLSX\Writer();
                $writer->openToFile($tempPath);
                
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    'Kode Standar', 'Nama Standar', 'Pernyataan Standar', 'Rujukan', 
                    'Indikator Kinerja Utama (IKU)', 'Indikator Kinerja Tambahan (IKT)', 'Target', 'Link Dokumen'
                ]));
                
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    'S.01', 'Standar Pendidikan', 'Melaksanakan Tri Dharma Perguruan Tinggi secara optimal', 'Permendikbud No.3 Th 2020', 
                    '80% dosen aktif tiap tahun', '-', '80%', 'https://docs.google.com/...'
                ]));
                
                $writer->close();
                
                return response()->download($tempPath, 'template_standar_mutu.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            // fall back to CSV if OpenSpout writing fails
        }
        
        // Generate a simple CSV-based Excel template fallback
        $rows = [
            ['Kode Standar', 'Nama Standar', 'Pernyataan Standar', 'Rujukan', 'Indikator Kinerja Utama (IKU)', 'Indikator Kinerja Tambahan (IKT)', 'Target', 'Link Dokumen'],
            ['S.01', 'Standar Pendidikan', 'Contoh pernyataan standar...', 'Permendikbud No. XX/YYYY', 'Capaian pembelajaran sesuai KKNI', '-', '≥ 80%', 'https://url...'],
        ];

        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF); // UTF-8 BOM so Excel opens it correctly
        foreach ($rows as $row) {
            $csv .= implode(';', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_standar_mutu.csv"',
        ]);
    }

    private function generateWordTemplate()
    {
        // Generate a simple HTML that can be opened in Word
        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Template Standar Mutu SPMI</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; }
h2 { color: #1a56a0; text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th { background-color: #1a56a0; color: white; padding: 10px; text-align: left; font-size: 13px; }
td { border: 1px solid #ccc; padding: 8px; font-size: 12px; vertical-align: top; min-height: 30px; }
th { border: 1px solid #0d3d7a; }
.note { font-size: 11px; color: #666; margin-top: 10px; }
</style>
</head>
<body>
<h2>TEMPLATE STANDAR MUTU SPMI</h2>
<p style="text-align:center; color:#555; font-size:13px;">Silakan isi tabel berikut sesuai dengan standar mutu institusi Anda</p>
<table>
<thead>
<tr>
<th width="10%">Kode Standar</th>
<th width="25%">Pernyataan Standar</th>
<th width="20%">Rujukan</th>
<th width="18%">Indikator Kinerja Utama (IKU)</th>
<th width="17%">Indikator Kinerja Tambahan (IKT)</th>
<th width="10%">Target</th>
</tr>
</thead>
<tbody>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
</tbody>
</table>
<p class="note">Keterangan:<br>
- IKU: Indikator Kinerja Utama (wajib dipenuhi)<br>
- IKT: Indikator Kinerja Tambahan (pelengkap/opsional)<br>
- Target: nilai capaian yang diharapkan (contoh: &ge; 3.00, 100%, dsb.)
</p>
</body>
</html>';

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="template_standar_mutu.doc"',
        ]);
    }

    private function generatePdfTemplate()
    {
        // Plain text PDF fallback — just return HTML with PDF mimetype hint
        // Since we don't have a PDF library, we return the Word template but named as doc
        // and label it a simple text document
        $content = "TEMPLATE STANDAR MUTU SPMI\n\n";
        $content .= str_repeat("=", 80) . "\n";
        $content .= sprintf("%-12s %-30s %-20s %-20s %-20s %-10s\n",
            "Kode Standar", "Pernyataan Standar", "Rujukan", "IKU", "IKT", "Target");
        $content .= str_repeat("-", 120) . "\n";
        for ($i = 0; $i < 10; $i++) {
            $content .= sprintf("%-12s %-30s %-20s %-20s %-20s %-10s\n", "", "", "", "", "", "");
        }
        $content .= "\nKeterangan:\n";
        $content .= "- IKU: Indikator Kinerja Utama\n";
        $content .= "- IKT: Indikator Kinerja Tambahan\n";
        $content .= "- Target: nilai capaian yang diharapkan\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="template_standar_mutu.txt"',
        ]);
    }
}
