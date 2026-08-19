<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'category',
        'head_name',
        'head_nidn',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'administratif_umum' => 'Administratif Umum',
            'layanan_akademik'   => 'Layanan Akademik',
            'penunjang'          => 'Penunjang',
            default              => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function auditAssignments()
    {
        return $this->hasMany(AuditAssignment::class);
    }

    public function riskRegisters()
    {
        return $this->hasMany(RiskRegister::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function evaluations()
    {
        return $this->morphMany(Evaluation::class, 'evaluable');
    }
}
