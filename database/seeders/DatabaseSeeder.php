<?php

namespace Database\Seeders;

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
        foreach([
            'AI-engineering',
            'Backend-разработка',
            'DevOps',
            'Frontend-разработка',
            'Архитектура',
            'Инструменты веб-разработки',
        ] as $title) {
            Rubric::factory(1)->createOne([
                'title' => $title,
                'parent_id' => 0,
            ]);
        }

        foreach([
            'AI',
            'API',
            'Apache',
            'Backend',
            'Carbon',
            'Databases',
            'Deploy',
            'DevOps',
            'Docker',
            'GRASP',
            'Git',
            'JavaScript',
            'Laravel',
            'Linux',
            'MySQL',
            'Nginx',
            'NodeJS',
            'OpenSpec',
            'PHP',
            'SOLID',
            'SQL',
            'VueJS',
            'Архитектура',
            'Инструменты веб-разработки',
            'Паттерны',
        ] as $title) {
            Tag::factory(1)->createOne([
                'title' => $title,
                'active' => true,
            ]);
        }

        // $this->call([
        //     ArticleSeeder::class,
        // ]);

        // \App\Models\User::factory(16)->create();

        // Article::factory(32)->create();

        // rescue(function () {
        //     ArticleTag::factory(16)->create();
        //     Comment::factory(32)->create();
        // });
    }
}
