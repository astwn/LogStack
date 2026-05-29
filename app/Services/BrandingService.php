<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BrandingService
{
    protected static ?array $cache = null;

    /**
     * Get all branding config, merged with overrides from branding.json
     */
    public static function get(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $defaults = config('app_branding', []);
        $overrides = [];

        if (Storage::disk('local')->exists('branding.json')) {
            $json = Storage::disk('local')->get('branding.json');
            $overrides = json_decode($json, true) ?? [];
        }

        static::$cache = array_merge($defaults, $overrides);
        return static::$cache;
    }

    /**
     * Get a single branding value
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        return static::get()[$key] ?? $default;
    }

    /**
     * Save branding overrides to branding.json
     */
    public static function save(array $data): bool
    {
        try {
            // Only save allowed keys
            $allowed = [
                'app_name', 'app_full_name', 'app_tagline',
                'app_logo_icon', 'app_version', 'footer_text',
                'primary_color', 'accent_color', 'loader_text',
            ];

            $filtered = array_intersect_key($data, array_flip($allowed));

            // Sanitize
            foreach ($filtered as $key => $value) {
                $filtered[$key] = strip_tags(trim($value));
            }

            Storage::disk('local')->put('branding.json', json_encode($filtered, JSON_PRETTY_PRINT));

            // Clear cache
            static::$cache = null;

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reset to defaults
     */
    public static function reset(): bool
    {
        try {
            if (Storage::disk('local')->exists('branding.json')) {
                Storage::disk('local')->delete('branding.json');
            }
            static::$cache = null;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
