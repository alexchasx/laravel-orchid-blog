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
            $table->id();

            $table->foreignId('rubric_id')->constrained()
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained()
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->string('slug')->unique()->nullable();
            $table->string('title');

            $table->text('excert')->nullable();

            $table->text('content_raw');

            $table->text('content_html')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->text('image')->nullable();

            $table->integer('viewed')->nullable()->comment('Кол-во просмотров');

            $table->string('keywords')->nullable();
            $table->string('meta_desc')->nullable();

            $table->timestamps();
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
