<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtmInstruction extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'rtm_meeting_id',
        'academic_program_id',
        'unit_id',
        'instruction',
        'target_date',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function meeting()
    {
        return $this->belongsTo(RtmMeeting::class, 'rtm_meeting_id');
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getTargetLabelAttribute(): string
    {
        if ($this->academicProgram) {
            return trim($this->academicProgram->degree_level . ' ' . $this->academicProgram->name);
        }
        if ($this->unit) {
            return $this->unit->name;
        }
        return '—';
    }

    public function logLabel(): string
    {
        return 'Instruksi RTM: ' . $this->instruction;
    }
}