<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // $table->foreignId('target_id'); // Для MySQL 5.0 полиморф не работает
            // $table->enum('target_type', [
            //     'user', 'category', 'article', 'comment', 'tag'
            // ]);
            $table->morphs('target');

            $table->boolean('active')->default(true);
            $table->string('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
