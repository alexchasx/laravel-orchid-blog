<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Blog\Article;
use App\Models\Blog\ArticleTag;
use App\Models\Blog\Comment;
use App\Models\Blog\Rubric;
use App\Models\Blog\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *s
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(16)->create();
        // // AdminUser::factory(1)->create([
        // //     'name' => 'admin',
        // //     'email' => 'laravel@laravel.com',
        // //     'password' => bcrypt('1234'),
        // // ]);
        // Rubric::factory(12)->create();
        // Article::factory(32)->create();
        // Tag::factory(24)->create();
        // ArticleTag::factory(16)->create();
        Comment::factory(32)->create();
    }
}
