<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditCycle extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'status',
        'description',
        'pimpinan_note',
        'created_by',
        'rtm_approved',
        'rtm_approved_by',
        'rtm_approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rtm_approved' => 'boolean',
        'rtm_approved_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'rtm_approved_by');
    }

    public function assignments()
    {
        return $this->hasMany(AuditAssignment::class);
    }
}
