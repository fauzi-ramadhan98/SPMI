<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Ambil nilai konfigurasi aplikasi dari tabel settings.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::value($key, $default);
    }
}