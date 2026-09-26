<?php

use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Заполнить пустые slug у существующих рубрик и тегов.
     */
    public function up(): void
    {
        // Рубрики.
        Rubric::whereNull('slug')->orWhere('slug', '')->each(function (Rubric $rubric): void {
            $base = Str::slug($rubric->title);
            $slug = $base;
            $counter = 1;

            while (Rubric::where('slug', $slug)->where('id', '!=', $rubric->id)->exists()) {
                $slug = $base . '-' . $counter++;
            }

            $rubric->update(['slug' => $slug]);
        });

        // Теги.
        Tag::whereNull('slug')->orWhere('slug', '')->each(function (Tag $tag): void {
            $base = Str::slug($tag->title);
            $slug = $base;
            $counter = 1;

            while (Tag::where('slug', $slug)->where('id', '!=', $tag->id)->exists()) {
                $slug = $base . '-' . $counter++;
            }

            $tag->update(['slug' => $slug]);
        });
    }

    /**
     * Откат не требуется — slug остаются.
     */
    public function down(): void
    {
        // Ничего не делаем.
    }
};
