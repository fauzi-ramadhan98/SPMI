<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditFinding extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'audit_assignment_id',
        'type',
        'criteria',
        'description',
        'root_cause',
        'corrective_action',
        'prodi_clarification',
        'target_date',
        'pimpinan_note',
        'status',
        'prodi_decision',
        'prodi_decision_note',
        'prodi_decision_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'prodi_decision_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(AuditAssignment::class, 'audit_assignment_id');
    }

    public function attachments()
    {
        return $this->hasMany(FindingAttachment::class, 'audit_finding_id');
    }
}
