<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграции.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // ID лога согласия на обработку ПДн
            $table->foreignId('consent_processing_log_id')
                ->nullable()
                ->constrained('consent_logs')
                ->nullOnDelete();

            // ID лога согласия на распространение ПДн
            $table->foreignId('consent_distribution_log_id')
                ->nullable()
                ->constrained('consent_logs')
                ->nullOnDelete();

            // Флаг обезличивания комментария
            $table->boolean('is_anonymized')->default(false);
        });
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['consent_processing_log_id']);
            $table->dropForeign(['consent_distribution_log_id']);
            $table->dropColumn([
                'consent_processing_log_id',
                'consent_distribution_log_id',
                'is_anonymized',
            ]);
        });
    }
};
