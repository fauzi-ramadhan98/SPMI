<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penugasan pengisian Risk Register dari SPMI ke satu Risk Owner
 * (Program Studi / Unit Kerja) untuk periode T.A. + Semester tertentu.
 */
class RiskRegisterAssignment extends Model
{
    protected $fillable = [
        'academic_year',
        'semester',
        'owner_key',
        'academic_program_id',
        'unit_id',
        'note',
        'assigned_by',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getOwnerLabelAttribute(): string
    {
        if ($this->academic_program_id && $this->academicProgram) {
            return trim($this->academicProgram->degree_level . ' ' . $this->academicProgram->name);
        }
        if ($this->unit_id && $this->unit) {
            return $this->unit->name;
        }
        return '—';
    }

    public function getOwnerTypeLabelAttribute(): string
    {
        return $this->academic_program_id ? 'Prodi' : 'Unit';
    }

    /**
     * Sudah ada entri Risk Register milik owner untuk periode ini?
     * (Status "Sudah diisi / Belum diisi" dihitung langsung dari data RR).
     */
    public function isFilled(): bool
    {
        return RiskRegister::where('academic_year', $this->academic_year)
            ->where('semester', $this->semester)
            ->when($this->academic_program_id, fn ($q) => $q->where('academic_program_id', $this->academic_program_id))
            ->when($this->unit_id, fn ($q) => $q->where('unit_id', $this->unit_id))
            ->exists();
    }
}
