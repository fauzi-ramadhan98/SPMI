<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class StandardVersion extends Model
{
    use LogsActivity;

    protected $fillable = [
        'quality_standard_id', 'version', 'snapshot', 'changed_by',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function qualityStandard()
    {
        return $this->belongsTo(QualityStandard::class, 'quality_standard_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}