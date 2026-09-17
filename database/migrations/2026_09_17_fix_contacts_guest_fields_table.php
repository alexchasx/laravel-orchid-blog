<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Миграция 2022_09_09_140729 удалила колонки name/email и сделала user_id NOT NULL,
     * из-за чего сообщения обратной связи (особенно от гостей) не сохраняются в БД.
     * Возвращаем поля формы и разрешаем анонимные сообщения.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('email')->nullable()->after('name');

            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->dropColumn(['name', 'email']);
        });
    }
};
