<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ModelCache
{
    public static function rememberChache(string $modelName): mixed
    {
        return Cache::remember(
            $modelName::SIDEBAR_CACHE_KEY,
            now()->addDays(2),
            fn () => $modelName::articlePublished()->get()
        );
    }

    public static function updateCache(string $modelName): void
    {
        Cache::forget($modelName::SIDEBAR_CACHE_KEY);
        self::rememberChache($modelName);
    }
}
