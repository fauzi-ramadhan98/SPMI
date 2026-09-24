<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardDecree extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sk_no',
        'judul',
        'jenis',
        'kategori',
        'deskripsi',
        'menimbang',
        'mengingat',
        'memutuskan',
        'audit_cycle_id',
        'nama_standar',
        'lokasi',
        'tanggal_sk',
        'status',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'kop_path',
        'signature_path',
        'issued_by',
        'issued_at',
        'prepared_by',
        'reject_reason',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'tanggal_sk' => 'date',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AuditCycle::class, 'audit_cycle_id');
    }

    public function standards(): HasMany
    {
        return $this->hasMany(QualityStandard::class, 'standard_decree_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'standard_decree_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function isDitetapkan(): bool
    {
        return $this->status === 'ditetapkan';
    }

    public function isMenungguPersetujuan(): bool
    {
        return $this->status === 'menunggu_persetujuan';
    }

    public function isDitolak(): bool
    {
        return $this->status === 'ditolak';
    }

    public function isPerubahan(): bool
    {
        return $this->kategori === 'perubahan';
    }

    /**
     * Daftar auditor unik dari Siklus AMI terkait (sumber Lampiran SK).
     * Indexed by auditor_id agar unik; mengutamakan auditor_name / auditor_nidn dari assignment.
     */
    public function auditors(): array
    {
        if (!$this->cycle) {
            return [];
        }

        $rows = [];
        foreach ($this->cycle->assignments as $a) {
            $key = $a->auditor_id ?: $a->auditor_name;
            if ($key === null) {
                continue;
            }
            $rows[$key] = [
                'nik'   => $a->auditor_nidn ?: '—',
                'nama'  => $a->auditor_name ?: ($a->auditor->name ?? '—'),
                'jabatan' => 'Auditor',
            ];
        }

        return array_values($rows);
    }

    public function logLabel(): string
    {
        return 'SK Penetapan ' . $this->sk_no;
    }
}