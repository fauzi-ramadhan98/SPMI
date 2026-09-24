<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'revisi_status',
        'standard_decree_id',
        'document_id',
        'parent_id',
    ];

    protected $casts = [
        'indicators' => 'array',
        'is_active'  => 'boolean',
    ];

    /* ---------- RELATIONSHIP ---------- */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(QualityStandard::class, 'parent_id')->orderBy('created_at');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'quality_standard_id')
                    ->orderBy('sort_order')
                    ->orderBy('id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(StandardVersion::class, 'quality_standard_id')
                    ->orderByDesc('version');
    }

    public function decree(): BelongsTo
    {
        return $this->belongsTo(StandardDecree::class, 'standard_decree_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function standardIndicators(): HasMany
    {
        return $this->hasMany(StandardIndicator::class, 'quality_standard_id')
                    ->orderBy('sort_order')
                    ->orderBy('id');
    }

    public function applicabilities(): HasMany
    {
        return $this->hasMany(QualityStandardApplicability::class);
    }

    public function applicablePrograms(): HasManyThrough
    {
        return $this->hasManyThrough(
            AcademicProgram::class,
            QualityStandardApplicability::class,
            'quality_standard_id',
            'id',
            'id',
            'academic_program_id'
        )->where('quality_standard_applicability.target_type', 'prodi');
    }

    public function applicableUnits(): HasManyThrough
    {
        return $this->hasManyThrough(
            Unit::class,
            QualityStandardApplicability::class,
            'quality_standard_id',
            'id',
            'id',
            'unit_id'
        )->where('quality_standard_applicability.target_type', 'unit');
    }

    /* ---------- SCOPE ---------- */
    public function scopeForUser($query, \App\Models\User $user)
    {
        if ($user->hasAnyRole(['spmi', 'administrator', 'super_admin', 'pimpinan', 'auditor'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereDoesntHave('applicabilities');

            if ($user->hasRole('prodi') && $user->academic_program_id) {
                $q->orWhereHas('applicabilities', function ($sub) use ($user) {
                    $sub->where('target_type', 'prodi')
                        ->where('academic_program_id', $user->academic_program_id);
                });
            }

            if ($user->hasRole('unit') && $user->unit_id) {
                $q->orWhereHas('applicabilities', function ($sub) use ($user) {
                    $sub->where('target_type', 'unit')
                        ->where('unit_id', $user->unit_id);
                });
            }

            if ($user->hasRole('prodi') && $user->unit_id) {
                $q->orWhereHas('applicabilities', function ($sub) use ($user) {
                    $sub->where('target_type', 'unit')
                        ->where('unit_id', $user->unit_id);
                });
            }
        });
    }

    public function scopeEffective($query)
    {
        return $query->where('is_active', true)
                     ->whereHas('decree', function ($q) {
                         $q->where('status', 'ditetapkan')
                           ->where('jenis', 'standar');
                     });
    }

    /* ---------- INDICATOR ---------- */
    public function indicatorData(): array
    {
        if ($this->exists && $this->standardIndicators()->exists()) {
            $all = $this->standardIndicators()->get();
            return [
                'iku' => $all->where('type', 'iku')
                            ->map(fn ($i) => ['text' => $i->text, 'target' => $i->target])
                            ->values()
                            ->toArray(),
                'ikt' => $all->where('type', 'ikt')
                            ->map(fn ($i) => ['text' => $i->text, 'target' => $i->target])
                            ->values()
                            ->toArray(),
            ];
        }

        $data = is_array($this->indicators) ? $this->indicators : [];
        $iku  = is_array($data['iku'] ?? null) ? $data['iku'] : [];
        $ikt  = is_array($data['ikt'] ?? null) ? $data['ikt'] : [];

        $normalize = function (array $rows): array {
            $out = [];
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                $text = trim((string) ($row['text'] ?? ''));
                if ($text === '') continue;
                $out[] = ['text' => $text, 'target' => trim((string) ($row['target'] ?? ''))];
            }
            return $out;
        };

        $iku = $normalize($iku);
        $ikt = $normalize($ikt);

        if (empty($iku) && empty($ikt) && !empty($this->description)) {
            foreach (preg_split('/\r\n|\r|\n/', $this->description) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '---')) continue;

                if (preg_match('/^(IKU|IKT)\s*\d+\s*:\s*(.*)$/i', $line, $m)) {
                    $kind   = strtoupper($m[1]) === 'IKT' ? 'ikt' : 'iku';
                    $text   = trim($m[2]);
                    $target = '';

                    if (preg_match('/^(.*?)\s*\(\s*Target\s*:\s*(.*?)\s*\)$/i', $text, $t)) {
                        $text   = trim($t[1]);
                        $target = trim($t[2]);
                    }

                    if ($text === '') continue;
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

    public function ikuIndicators(): array { return $this->indicatorData()['iku']; }
    public function iktIndicators(): array { return $this->indicatorData()['ikt']; }

    public function indicatorSummary(): string
    {
        return static::summaryFromIndicators($this->ikuIndicators(), $this->iktIndicators());
    }

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
