<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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
            'DevOps',
            'GRASP',
            'Git',
            'JavaScript',
            'Laravel',
            'MySQL',
            'Nginx',
            'NodeJS',
            'OpenSpec',
            'PHP',
            'SOLID',
            'SQL',
            'Архитектура',
            'Инструменты веб-разработки',
            'Паттерны',
        ] as $title) {
            Tag::factory(1)->createOne([
                'title' => $title,
                'active' => true,
            ]);
        }

        // Автор тестовых статей (или первый пользователь, созданный сидами).
        $author = User::query()->firstOrCreate(
            ['email' => 'author@example.test'],
            ['name' => 'Автор тестовых статей', 'password' => 'password'],
        );

        $rubricIds = Rubric::pluck('id', 'title');
        $tagIds = Tag::pluck('id', 'title');

        // 5 пробных статей: разные рубрики, разные наборы тегов.
        $articles = [
            [
                'rubric' => 'AI-engineering',
                'title' => 'Как OpenSpec помогает проектировать ИИ-агентов',
                'excert' => 'Разбираемся, как спецификации OpenSpec упрощают проектирование и постановку задач для ИИ-агентов.',
                'tags' => ['AI', 'OpenSpec'],
                'keywords' => 'ИИ, OpenSpec, агенты, спецификации',
            ],
            [
                'rubric' => 'Backend-разработка',
                'title' => 'Оптимизация запросов в Laravel: практические приёмы',
                'excert' => 'Eager-loading, индексы и работа с Carbon при разборе практических примеров оптимизации.',
                'tags' => ['PHP', 'Laravel', 'Carbon'],
                'keywords' => 'Laravel, PHP, оптимизация, Carbon',
            ],
            [
                'rubric' => 'DevOps',
                'title' => 'Настройка Nginx и балансировки нагрузки для высоконагруженных сервисов',
                'excert' => 'Практический гайд по настройке Nginx, upstream-ов и балансировки нагрузки.',
                'tags' => ['Nginx', 'DevOps'],
                'keywords' => 'Nginx, DevOps, балансировка, нагрузка',
            ],
            [
                'rubric' => 'Архитектура',
                'title' => 'SOLID и GRASP: принципы проектирования на практике',
                'excert' => 'Сопоставляем принципы SOLID и GRASP и смотрим, как они работают в реальных проектах.',
                'tags' => ['SOLID', 'GRASP', 'Паттерны'],
                'keywords' => 'SOLID, GRASP, паттерны, архитектура',
            ],
            [
                'rubric' => 'Frontend-разработка',
                'title' => 'Паттерны для работы с API на JavaScript',
                'excert' => 'Обзор популярных паттернов работы с API на JavaScript и NodeJS.',
                'tags' => ['JavaScript', 'NodeJS'],
                'keywords' => 'JavaScript, NodeJS, API, паттерны',
            ],
        ];

        foreach ($articles as $data) {
            $article = Article::factory()->createOne([
                'user_id' => $author->id,
                'rubric_id' => $rubricIds[$data['rubric']],
                'slug' => Str::slug($data['title']),
                'title' => $data['title'],
                'excert' => $data['excert'],
                'content_raw' => <<<MD
## Введение

{$data['excert']}

## Основная часть

В этой статье разберём ключевые подходы и приёмы для категории «{$data['rubric']}».
Здесь вы найдёте практические примеры, разбор типовых задач и полезные рекомендации.

### Пример

```php
// Короткий фрагмент кода для иллюстрации
\$items = collect([1, 2, 3])->map(fn (\$n) => \$n * 2);
```

## Заключение

Надеемся, материал был полезен. Оставляйте вопросы в комментариях!
MD,
                'is_published' => true,
                'published_at' => now()->subDay(),
                'viewed' => fake()->numberBetween(1, 1000),
                'keywords' => $data['keywords'],
                'meta_desc' => $data['excert'],
            ]);

            $article->tags()->attach(
                collect($data['tags'])->map(fn (string $tag) => $tagIds[$tag])->values()->all()
            );

            Tag::updateCountArticles($article);
        }

        // \App\Models\User::factory(16)->create();

        // Article::factory(32)->create();

        // rescue(function () {
        //     ArticleTag::factory(16)->create();
        //     Comment::factory(32)->create();
        // });
    }
}
