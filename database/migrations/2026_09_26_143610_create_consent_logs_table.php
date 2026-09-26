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
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();

            // Ссылка на комментарий (существует на момент создания этой таблицы)
            $table->foreignId('comment_id')
                ->constrained('comments')
                ->cascadeOnDelete();

            // Тип согласия: 'processing' или 'distribution'
            $table->string('consent_type');
            $table->index('consent_type');

            // Дословный текст согласия, с которым ознакомился пользователь
            $table->text('consent_text');

            // Версия текста согласия
            $table->string('consent_version');

            // IP-адрес пользователя
            $table->string('ip_address', 45);

            // User-Agent браузера
            $table->text('user_agent');

            // URL страницы, на которой дано согласие
            $table->string('page_url');

            // Дата/время дачи согласия
            $table->timestamp('consented_at');

            // Дата/время отзыва согласия (nullable — активная запись)
            $table->timestamp('revoked_at')->nullable();
            $table->index('revoked_at');

            $table->timestamps();

            // Уникальная активная запись: один тип согласия на комментарий
            $table->unique(['comment_id', 'consent_type'], 'consent_active_unique')
                ->where('revoked_at IS NULL');
        });
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
