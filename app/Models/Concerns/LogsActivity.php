<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait untuk merekam riwayat perubahan model (create/update/delete)
 * ke tabel activity_logs.
 */
trait LogsActivity
{
    /**
     * Boot trait: pasang event listener.
     */
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('created', 'Membuat data baru', $model->getLoggableAttributes());
        });

        static::updated(function ($model) {
            $model->logActivity('updated', 'Memperbarui data', $model->getLoggableAttributes(
                $model->getOriginal()
            ));
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', 'Menghapus data', null, $model->getLoggableAttributes());
        });
    }

    /**
     * Simpan satu catatan aktivitas.
     */
    protected function logActivity(string $action, string $description, ?array $newValues, ?array $oldValues = null): void
    {
        $user = auth()->user();

        ActivityLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name,
            'model_type'  => static::class,
            'model_id'    => $this->getKey(),
            'action'      => $action,
            'description' => $this->logDescription() . ' — ' . $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }

    /**
     * Daftar kolom yang direkam (peka). Filter dari fillable.
     */
    protected function getLoggableAttributes(?array $source = null): array
    {
        $source = $source ?? $this->getAttributes();
        $keys = $this->getFillable();

        $out = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $source)) {
                $val = $source[$key];
                if (is_string($val) && strlen($val) > 500) {
                    $val = substr($val, 0, 500) . '…';
                }
                $out[$key] = $val;
            }
        }
        return $out;
    }

    /**
     * Label ringkas untuk deskripsi log.
     */
    protected function logDescription(): string
    {
        $label = method_exists($this, 'logLabel') ? $this->logLabel() : null;
        return $label ?: (static::class);
    }
}