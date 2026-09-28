<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запустить мигрирование.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('image');
        });
    }

    /**
     * Откатить мигрирование.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('image_alt');
        });
    }
};
