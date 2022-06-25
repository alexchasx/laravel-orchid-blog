<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\Tag;
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
        \App\Models\User::factory(16)->create();
        // AdminUser::factory(1)->create([
        //     'name' => 'admin',
        //     'email' => 'laravel@laravel.com',
        //     'password' => bcrypt('1234'),
        // ]);

        foreach([
            'Администрирование',
            'Backend-разработка',
            'Frontend-разработка',
            'Вёрстка',
            'Computer Science',
            'Инструменты веб-разработки',
        ] as $title) {
            Rubric::factory(1)->create([
                'title' => $title,
                'parent_id' => 0,
            ]);
        }

        foreach([
            'PHP',
            'Laravel',
            'JavaScript',
            'NodeJS',
            'Git',
        ] as $title) {
            Tag::factory(1)->create([
                'title' => $title,
                'active' => true,
            ]);
        }

        Article::factory(32)->create();

        ArticleTag::factory(16)->create();

        Comment::factory(32)->create();
    }
}
