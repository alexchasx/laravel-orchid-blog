<?php

namespace App\Services;

use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class Sidebar
{
    public function getData(): array
    {
        return config('caching.sidebar') ? $this->getCache() : $this->withoutCache();
    }

    private function getCache(): array
    {
        return [
            'tags' => $this->rememberChache(Tag::class), 
            'rubrics' => $this->rememberChache(Rubric::class),
        ];
    }

    private function withoutCache(): array
    {
        return [
            'tags' => Tag::articlePublished()->get(), 
            'rubrics' => Rubric::articlePublished()->get(),
        ];
    }

    protected function rememberChache(string $model): mixed 
    {
        return Cache::remember(
            $model::SIDEBAR_CACHE_KEY,
            now()->addDays(2),
            fn () => $model::articlePublished()->get()
        );
    }
}