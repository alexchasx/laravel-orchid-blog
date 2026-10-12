<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграции.
     *
     * Добавляем полиморфную связь subject_type/subject_id для поддержки
     * согласия подписчиков. comment_id остаётся для обратной совместимости.
     */
    public function up(): void
    {
        Schema::table('consent_logs', function (Blueprint $table) {
            $table->string('subject_type')->nullable()->after('comment_id');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->index(['subject_type', 'subject_id'], 'consent_logs_subject_index');
        });

        // Бэкфилл: все существующие логи привязаны к комментариям.
        DB::table('consent_logs')->whereNull('subject_type')->update([
            'subject_type' => 'comment',
            'subject_id'   => DB::raw('comment_id'),
        ]);
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::table('consent_logs', function (Blueprint $table) {
            $table->dropIndex('consent_logs_subject_index');
            $table->dropColumn(['subject_type', 'subject_id']);
        });
    }
};
