<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Приводит все названия меток к нижнему регистру и заменяет пробелы на дефисы.
     * Одновременно регенерирует slug для каждой записи.
     */
    public function up(): void
    {
        $tags = DB::table('tags')->get();

        foreach ($tags as $tag) {
            $newTitle = mb_strtolower($tag->title, 'UTF-8');
            $newTitle = str_replace(' ', '-', $newTitle);

            // Пропускаем, если значение не изменилось.
            if ($newTitle === $tag->title) {
                continue;
            }

            try {
                DB::table('tags')
                    ->where('id', $tag->id)
                    ->update([
                        'title' => $newTitle,
                        'slug'  => Str::slug($newTitle),
                    ]);
            } catch (QueryException $e) {
                // Дубликат slug — добавляем суффикс.
                $slug = Str::slug($newTitle);
                $counter = 1;

                while (DB::table('tags')
                    ->where('slug', $slug)
                    ->where('id', '!=', $tag->id)
                    ->exists()
                ) {
                    $slug = Str::slug($newTitle) . '-' . $counter++;
                }

                DB::table('tags')
                    ->where('id', $tag->id)
                    ->update([
                        'title' => $newTitle,
                        'slug'  => $slug,
                    ]);

                Log::warning("Slug for tag #{$tag->id} adjusted to: {$slug}");
            }
        }
    }

    public function down(): void
    {
        // Необратимо без истории изменений.
    }
};
