<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sop extends Model
{
    use LogsActivity;

    protected $fillable = [
        'unit_id',
        'created_by',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRevisi(): bool
    {
        return $this->status === 'revisi';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'draft'    => '<span class="badge bg-secondary">Draft</span>',
            'pending'  => '<span class="badge bg-warning text-dark">Menunggu Review</span>',
            'revisi'   => '<span class="badge bg-danger">Revisi</span>',
            'approved' => '<span class="badge bg-success">Disetujui</span>',
            default    => '<span class="badge bg-light text-dark">' . $this->status . '</span>',
        };
    }

    public function logLabel(): string
    {
        return 'SOP: ' . $this->title;
    }
}