<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграции.
     *
     * comment_id становится nullable с nullOnDelete: лог согласия должен
     * сохраняться даже после полного удаления комментария (например, при
     * отзыве согласия на обработку ПДн), чтобы зафиксировать факт отзыва
     * (revoked_at) и остальные метаданные согласия.
     */
    public function up(): void
    {
        Schema::table('consent_logs', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
        });

        Schema::table('consent_logs', function (Blueprint $table) {
            $table->foreignId('comment_id')->nullable()->change();
        });

        Schema::table('consent_logs', function (Blueprint $table) {
            $table->foreign('comment_id')
                ->references('id')
                ->on('comments')
                ->nullOnDelete();
        });
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::table('consent_logs', function (Blueprint $table) {
            $table->dropForeign(['comment_id']);
        });

        Schema::table('consent_logs', function (Blueprint $table) {
            $table->foreignId('comment_id')->nullable(false)->change();
        });

        Schema::table('consent_logs', function (Blueprint $table) {
            $table->foreign('comment_id')
                ->references('id')
                ->on('comments')
                ->cascadeOnDelete();
        });
    }
};
