<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label', 'updated_by'];

    /**
     * Ambil nilai setting; jika tidak ada, kembalikan default.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Simpan / perbarui satu setingan.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $label = null, ?int $updatedBy = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'label' => $label ?? $key, 'updated_by' => $updatedBy]
        );
    }
}