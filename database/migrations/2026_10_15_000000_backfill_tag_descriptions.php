<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Заполняет шаблонным описанием метки с пустым/NULL description.
     * Текст шаблона дублирует Tag::defaultDescription() (см. app/Models/Tag.php).
     */
    public function up(): void
    {
        DB::table('tags')
            ->whereNull('description')
            ->orWhere('description', '')
            ->update([
                'description' => DB::raw(
                    "CONCAT('Статьи по тегу «', title, '»: обзоры, гайды, примеры кода и не только')"
                ),
            ]);
    }

    public function down(): void
    {
        // Необратимо: нельзя отличить сгенерированные описания от заданных вручную.
    }
};
