<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratedReport extends Model
{
    protected $fillable = [
        'audit_cycle_id',
        'level',
        'academic_program_id',
        'jenis',
        'status',
        'file_path',
        'file_name',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AuditCycle::class, 'audit_cycle_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'academic_program_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /** Lampiran terurut — urutan inilah yang menjadi urutan halaman di PDF. */
    public function attachments(): HasMany
    {
        return $this->hasMany(GeneratedReportAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === 'berbasis-risiko' ? 'AMI Berbasis Risiko' : 'AMI Klasik';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->file_path && $this->status === 'generated') {
            return 'Sudah Digenerate';
        }
        if ($this->file_path) {
            return 'Perlu Generate Ulang';
        }

        return 'Belum Digenerate';
    }
}
