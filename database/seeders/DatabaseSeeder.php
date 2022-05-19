<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(16)->create();
        // AdminUser::factory(1)->create([
        //     'name' => 'admin',
        //     'email' => 'laravel@laravel.com',
        //     'password' => bcrypt('1234'),
        // ]);
        // BlogCategory::factory(8)->create();
        BlogArticle::factory(32)->create();
        // Comment::factory(32)->create();
        // ArticleTag::factory(16)->create();
        // Tag::factory(16)->create();
    }
}
