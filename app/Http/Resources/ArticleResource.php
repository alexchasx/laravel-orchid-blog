<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

final class ArticleResource extends JsonResource
{
    private bool $detail = false;

    /**
     * Переключает ресурс в режим полной карточки статьи
     * (включает content_html и SEO-поля).
     */
    public function forDetail(): static
    {
        $this->detail = true;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            // API-контракт использует "excerpt", в БД колонка называется "excert".
            'excerpt' => $this->excert,
            'image' => $this->image,
            'slug' => $this->slug,
            'published_at' => $this->published_at
                ? Carbon::parse($this->published_at)->toIso8601String()
                : null,
            'viewed' => $this->when($this->detail, $this->viewed),
            'rubric' => $this->whenLoaded('rubric', fn () => new RubricResource($this->rubric)),
            'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
            'content_html' => $this->when($this->detail, $this->content_html),
            'keywords' => $this->when($this->detail, $this->keywords),
            'meta_desc' => $this->when($this->detail, $this->meta_desc),
        ];
    }
}
