<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *s
     * @return void
     */
    public function run()
    {
        // Production:

        foreach([
            'Администрирование',
            'Backend-разработка',
            'Frontend-разработка',
            'Вёрстка',
            'Веб-архитектура',
            'Инструменты веб-разработки',
        ] as $title) {
            Rubric::factory(1)->createOne([
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
            'Databases',
            'SQL',
            'API',
            'SOLID',
            'GRASP',
            'Carbon',
            'MySQL',
        ] as $title) {
            Tag::factory(1)->createOne([
                'title' => $title,
                'active' => true,
            ]);
        }

        // Develop:

        // \App\Models\User::factory(16)->create();

        // Article::factory(32)->create();

        // rescue(function () {
        //     ArticleTag::factory(16)->create();
        //     Comment::factory(32)->create();
        // });
    }
}
