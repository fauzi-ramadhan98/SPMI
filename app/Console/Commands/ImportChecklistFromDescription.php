<?php

namespace App\Console\Commands;

use App\Models\ChecklistItem;
use App\Models\QualityStandard;
use Illuminate\Console\Command;

class ImportChecklistFromDescription extends Command
{
    /**
     * Konversi baris IKU/IKT di quality_standards.description menjadi master daftar tilik.
     */
    protected $signature = 'checklist:import-from-description';

    protected $description = 'Buat checklist_items dari kolom description pada quality_standards (baris IKU/IKT)';

    public function handle(): int
    {
        $standards = QualityStandard::with('checklistItems')->get();
        $created = 0;

        foreach ($standards as $standard) {
            $existing = $standard->checklistItems->pluck('indicator')->map(fn($i) => trim($i))->all();

            // Sumber utama: indikator terstruktur (kolom indicators).
            $rows = [];
            foreach ($standard->ikuIndicators() as $i => $r) {
                $rows[] = ['kind' => 'IKU', 'key' => 'IKU ' . ($i + 1), 'text' => trim($r['text'] ?? '')];
            }
            foreach ($standard->iktIndicators() as $i => $r) {
                $rows[] = ['kind' => 'IKT', 'key' => 'IKT ' . ($i + 1), 'text' => trim($r['text'] ?? '')];
            }

            // Fallback: parse baris "IKU N: ..." / "IKT N: ..." dari description lama.
            if (empty($rows) && $standard->description) {
                $ikuN = 0;
                $iktN = 0;
                foreach (preg_split('/\r\n|\r|\n/', $standard->description) as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '---')) {
                        continue;
                    }
                    if (preg_match('/^-(IKU|IKT)\s*\d+\s*:\s*(.*)$/i', $line, $m)) {
                        continue;
                    }
                    if (preg_match('/^(IKU|IKT)\s*\d+\s*:\s*(.*)$/i', $line, $m)) {
                        $kind = strtoupper($m[1]) === 'IKT' ? 'IKT' : 'IKU';
                        if ($kind === 'IKU') {
                            $ikuN++;
                            $key = 'IKU ' . $ikuN;
                        } else {
                            $iktN++;
                            $key = 'IKT ' . $iktN;
                        }
                        $rows[] = ['kind' => $kind, 'key' => $key, 'text' => trim($m[2])];
                    }
                }
            }

            $sort = 0;
            foreach ($rows as $row) {
                $indicator = trim($row['text']);
                if ($indicator === '' || in_array($indicator, $existing, true)) {
                    continue;
                }

                ChecklistItem::create([
                    'quality_standard_id' => $standard->id,
                    'code' => null,
                    'indicator_key' => $row['key'] ?? null,
                    'indicator' => $indicator,
                    'max_score' => 4,
                    'sort_order' => $sort,
                    'is_active' => true,
                ]);

                $existing[] = $indicator;
                $sort++;
                $created++;
            }
        }

        $this->info("Selesai: $created butir daftar tilik berhasil diimpor.");

        return self::SUCCESS;
    }
}
