<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Tahun akademik aktif yang dapat dipilih pada Risk Register.
     */
    public static function active(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderByDesc('name')->get();
    }
}
