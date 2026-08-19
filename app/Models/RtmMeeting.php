<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RtmMeeting extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'audit_cycle_id',
        'title',
        'meeting_date',
        'meeting_time',
        'location',
        'agenda',
        'notulensi',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'meeting_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
    ];

    public function cycle()
    {
        return $this->belongsTo(AuditCycle::class, 'audit_cycle_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'rtm_meeting_user')->withTimestamps();
    }

    public function instructions()
    {
        return $this->hasMany(RtmInstruction::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dijadwalkan' => 'Dijadwalkan',
            'berlangsung' => 'Berlangsung',
            'notulensi' => 'Menunggu Pengesahan',
            'disahkan' => 'Disahkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'dijadwalkan' => 'info',
            'berlangsung' => 'warning',
            'notulensi' => 'primary',
            'disahkan' => 'success',
            default => 'secondary',
        };
    }

    public function logLabel(): string
    {
        return 'RTM: ' . $this->title;
    }
}