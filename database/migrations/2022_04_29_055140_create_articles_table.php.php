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
        Schema::create('articles', function (Blueprint $table) {
            $table->id()->from(1001);
            $table->timestamps();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content');

            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_id')->constrained('users');

            $table->text('image')->nullable();
            $table->integer('viewed')->nullable(); // кол-во просмотров
            $table->string('keywords')->nullable();
            $table->string('meta_desc')->nullable();

            $table->boolean('published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
