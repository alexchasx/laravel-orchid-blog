<?php

namespace App\Support;

/**
 * Хелпер для SEO-разметки: приведение путей к абсолютным URL.
 */
class Seo
{
    /**
     * Приводит путь к абсолютному URL.
     *
     * - `null` → `null`;
     * - уже абсолютный (`http(s)://`) → как есть;
     * - иначе — `url($path)` (оборачивает относительный путь доменом из `APP_URL`).
     */
    public static function absoluteUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }
}
