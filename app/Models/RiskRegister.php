<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class RiskRegister extends Model
{
    use LogsActivity;

    protected $fillable = [
        'academic_program_id',
        'unit_id',
        'academic_year',
        'quality_standard_id',
        'source',
        'standar_mutu',
        'butir_tilik',
        'risk_description',
        'temuan',
        'akar_masalah',       // Akar Masalah (Root Cause)
        'probability',
        'impact',
        'risk_score',
        'risk_level',
        'mitigation_plan',
        'document_link',
        'pic',                // Person in Charge
        'target_date',        // Tanggal Penyelesaian
        'created_by',
        'status',
        'status_note',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function standard()
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Label pemilik risiko: prodi ATAU unit kerja.
     */
    public function getOwnerLabelAttribute(): string
    {
        if ($this->academicProgram) {
            return trim($this->academicProgram->degree_level . ' ' . $this->academicProgram->name);
        }
        if ($this->unit) {
            return $this->unit->name;
        }
        return '—';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
            default    => 'Menunggu Review',
        };
    }
}
