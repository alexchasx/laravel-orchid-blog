<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function remember(string $model): mixed 
    {
        return Cache::remember(
            $model::SIDEBAR_CACHE_KEY,
            now()->addDays(2),
            fn () => $model::articlePublished()->get()
        );
    }
}