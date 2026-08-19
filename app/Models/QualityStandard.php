<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class QualityStandard extends Model
{
    use LogsActivity;

    protected $fillable = [
        'kode_standar',
        'pernyataan_standar',
        'rujukan',
        'name',
        'type',
        'description',
        'target_value',
        'indicators',
        'is_active',
        'file_path',
        'file_name',
        'file_type',
            'file_size',
        'version',
        'standard_decree_id',
    ];

    protected $casts = [
        'indicators' => 'array',
    ];

    public function checklistItems()
    {
        return $this->hasMany(ChecklistItem::class, 'quality_standard_id')->orderBy('sort_order')->orderBy('id');
    }

    public function versions()
    {
        return $this->hasMany(StandardVersion::class, 'quality_standard_id')->orderByDesc('version');
    }

    public function decree()
    {
        return $this->belongsTo(StandardDecree::class, 'standard_decree_id');
    }

    /**
     * Indikator terstruktur: ['iku' => [['text'=>..., 'target'=>...], ...], 'ikt' => [...]]
     * Bila kolom indicators kosong, coba parse best-effort dari description lama.
     */
    public function indicatorData(): array
    {
        $data = is_array($this->indicators) ? $this->indicators : [];
        $iku  = is_array($data['iku'] ?? null) ? $data['iku'] : [];
        $ikt  = is_array($data['ikt'] ?? null) ? $data['ikt'] : [];

        $normalize = function (array $rows): array {
            $out = [];
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
        };

        $iku = $normalize($iku);
        $ikt = $normalize($ikt);

        if (empty($iku) && empty($ikt) && !empty($this->description)) {
            foreach (preg_split('/\r\n|\r|\n/', $this->description) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '---')) {
                    continue;
                }
                if (preg_match('/^(IKU|IKT)\s*\d+\s*:\s*(.*)$/i', $line, $m)) {
                    $kind   = strtoupper($m[1]) === 'IKT' ? 'ikt' : 'iku';
                    $text   = trim($m[2]);
                    $target = '';
                    if (preg_match('/^(.*?)\s*\(\s*Target\s*:\s*(.*?)\s*\)$/i', $text, $t)) {
                        $text   = trim($t[1]);
                        $target = trim($t[2]);
                    }
                    if ($text === '') {
                        continue;
                    }
                    if ($kind === 'iku') {
                        $iku[] = ['text' => $text, 'target' => $target];
                    } else {
                        $ikt[] = ['text' => $text, 'target' => $target];
                    }
                }
            }
        }

        return ['iku' => $iku, 'ikt' => $ikt];
    }

    public function ikuIndicators(): array
    {
        return $this->indicatorData()['iku'];
    }

    public function iktIndicators(): array
    {
        return $this->indicatorData()['ikt'];
    }

    public function indicatorSummary(): string
    {
        return static::summaryFromIndicators($this->ikuIndicators(), $this->iktIndicators());
    }

    /**
     * Ringkasan teks indikator: "IKU 1: {text} (Target: {target})" dst.
     */
    public static function summaryFromIndicators(array $iku, array $ikt): string
    {
        $lines = [];

        foreach ($iku as $i => $row) {
            $lines[] = 'IKU ' . ($i + 1) . ': ' . $row['text']
                . (!empty($row['target']) ? ' (Target: ' . $row['target'] . ')' : '');
        }

        foreach ($ikt as $i => $row) {
            $lines[] = 'IKT ' . ($i + 1) . ': ' . $row['text']
                . (!empty($row['target']) ? ' (Target: ' . $row['target'] . ')' : '');
        }

        return implode("\n", $lines);
    }
}
