<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запустить миграцию.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->text('distribution_conditions')
                ->nullable()
                ->after('active');
        });
    }

    /**
     * Откатить миграцию.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('distribution_conditions');
        });
    }
};
