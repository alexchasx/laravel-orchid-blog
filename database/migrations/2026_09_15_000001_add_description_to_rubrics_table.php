<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Описание рубрики используется публичным сайтом (карточки,
     * SEO-мета страниц рубрик) и предусмотрено моделью Rubric.
     */
    public function up(): void
    {
        Schema::table('rubrics', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('rubrics', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
