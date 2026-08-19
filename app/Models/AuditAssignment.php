<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditAssignment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'audit_cycle_id',
        'auditor_id',
        'auditor_type',
        'auditor_role',
        'auditor_name',
        'auditor_nidn',
        'academic_program_id',
        'unit_id',
        'status',
    ];

    public function cycle()
    {
        return $this->belongsTo(AuditCycle::class, 'audit_cycle_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'academic_program_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Auditee label: prodi ATAU unit kerja yang diaudit.
     */
    public function getAuditeeLabelAttribute(): string
    {
        if ($this->academicProgram) {
            return trim($this->academicProgram->degree_level . ' ' . $this->academicProgram->name);
        }
        if ($this->unit) {
            return $this->unit->name;
        }
        return '—';
    }

    /**
     * Tingkat audit (Prodi/Unit) untuk label.
     */
    public function getAuditeeTypeLabelAttribute(): string
    {
        return $this->academicProgram ? 'Prodi' : 'Unit';
    }

    public function instruments()
    {
        return $this->hasMany(AuditInstrument::class);
    }

    public function findings()
    {
        return $this->hasMany(AuditFinding::class);
    }
}
