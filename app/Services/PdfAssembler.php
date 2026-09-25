<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

/**
 * Perakit PDF akhir untuk Generate Laporan AMI.
 *
 * Badan laporan ( hasil render DomPDF ) digabung dengan berkas lampiran
 * sesuai urutan yang disusun pengguna:
 *  - berkas PDF  → halamannya diimpor utuh (vektoral, teks tetap bisa dicari)
 *  - gambar      → disematkan sebagai halaman penuh (auto-orient, muat A4)
 *
 * Penggabungan dilakukan murni PHP (setasign/fpdi) — tanpa Imagick,
 * Ghostscript, atau biner eksternal sehingga aman di XAMPP/Windows.
 */
class PdfAssembler
{
    /**
     * @param  string  $bodyPdf  bytes PDF badan laporan dari DomPDF
     * @param  Collection<int, \App\Models\GeneratedReportAttachment>  $attachments  lampiran terurut
     * @return array{bytes: string, pages: int, warnings: array<int, string>}
     */
    public function assemble(string $bodyPdf, Collection $attachments): array
    {
        $warnings = [];
        $pages = 0;

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        // ---- 1. Badan laporan ----
        $tmp = tempnam(sys_get_temp_dir(), 'ami-body-');
        file_put_contents($tmp, $bodyPdf);
        try {
            $pageCount = $pdf->setSourceFile($tmp);
            for ($i = 1; $i <= $pageCount; $i++) {
                $this->placePage($pdf, $pdf->importPage($i));
                $pages++;
            }
        } finally {
            @unlink($tmp);
        }

        // ---- 2. Lampiran terurut ----
        foreach ($attachments as $att) {
            try {
                $path = $this->absolutePath($att);
                if (!$path) {
                    $warnings[] = 'Lampiran "' . $att->title . '" dilewati (file tidak ditemukan).';
                    continue;
                }

                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $count = $pdf->setSourceFile($path);
                    for ($i = 1; $i <= $count; $i++) {
                        $this->placePage($pdf, $pdf->importPage($i));
                        $pages++;
                    }
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                    $this->placeImage($pdf, $path);
                    $pages++;
                } else {
                    $warnings[] = 'Lampiran "' . $att->title . '" dilewati (format .' . $ext . ' tidak didukung).';
                }
            } catch (\Throwable $e) {
                $warnings[] = 'Lampiran "' . $att->title . '" dilewati (berkas tidak terbaca).';
            }
        }

        return [
            'bytes'    => $pdf->output('S'),
            'pages'    => $pages,
            'warnings' => $warnings,
        ];
    }

    /**
     * Sisipkan satu halaman template dengan ukuran halaman aslinya.
     * (FPDI 2.x mengembalikan id template sebagai string.)
     */
    private function placePage(Fpdi $pdf, $templateId): void
    {
        $size = $pdf->getTemplateSize($templateId);
        $pdf->addPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }

    /**
     * Sisipkan gambar sebagai satu halaman penuh (A4, orientasi mengikuti gambar).
     */
    private function placeImage(Fpdi $pdf, string $path): void
    {
        $info = @getimagesize($path);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            throw new \RuntimeException('Gambar tidak terbaca.');
        }

        [$wPx, $hPx] = [$info[0], $info[1]];
        $landscape = $wPx > $hPx;

        if ($landscape) {
            $pageW = 297.0;
            $pageH = 210.0;
            $maxW = 277.0;   // A4 landscape - margin 10 mm kiri/kanan
            $maxH = 190.0;
            $pdf->AddPage('L', [$pageW, $pageH]);
        } else {
            $pageW = 210.0;
            $pageH = 297.0;
            $maxW = 190.0;   // A4 portrait - margin 10 mm kiri/kanan
            $maxH = 277.0;
            $pdf->AddPage('P', [$pageW, $pageH]);
        }

        $ratio = $hPx / $wPx;
        $w = $maxW;
        $h = $w * $ratio;
        if ($h > $maxH) {
            $h = $maxH;
            $w = $h / $ratio;
        }

        $x = ($pageW - $w) / 2;
        $y = ($pageH - $h) / 2;

        // Tipe deteksi otomatis FPDF (jpeg/png/gif); skalakan penuh ke kotak A4.
        $pdf->Image($path, $x, $y, $w, $h);
    }

    /**
     * Path absolut berkas lampiran pada disk-nya; null bila tidak ada.
     */
    private function absolutePath(object $att): ?string
    {
        if (!$att->file_path) {
            return null;
        }
        try {
            $disk = Storage::disk($att->disk ?: 'local');

            return $disk->exists($att->file_path) ? $disk->path($att->file_path) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
