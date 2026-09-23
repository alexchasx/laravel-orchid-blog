<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Comment;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
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
            $this->createArticle($data, $author, $rubricIds, $tagIds);
        }

        // Ещё 20 статей: задействованы все рубрики seed-набора, тэги комбинируются по-разному,
        // даты публикации разбросаны по последним двум месяцам.
        $moreArticles = [
            // --- AI-engineering (3) ---
            [
                'rubric' => 'AI-engineering',
                'title' => 'RAG-пайплайн: от векторного поиска к готовому ответу',
                'excert' => 'Собираем RAG: нарезка документов, эмбеддинги, векторный поиск и формирование ответа на основе найденных фрагментов.',
                'tags' => ['AI', 'API', 'Backend'],
                'keywords' => 'RAG, эмбеддинги, векторный поиск, ИИ',
            ],
            [
                'rubric' => 'AI-engineering',
                'title' => 'Оценка качества ответов LLM: метрики и датасеты',
                'excert' => 'Как измерять качество ответов языковых моделей: размеченные датасеты, точность, полнота и регрессионные тесты промптов.',
                'tags' => ['AI'],
                'keywords' => 'LLM, метрики, датасеты, оценка качества',
            ],
            [
                'rubric' => 'AI-engineering',
                'title' => 'Кэширование промптов и ускорение ИИ-сервисов',
                'excert' => 'Приёмы снижения задержки и стоимости ИИ-сервиса: кэш промптов, батчинг запросов и переиспользование контекста.',
                'tags' => ['AI', 'API', 'Backend'],
                'keywords' => 'кэш промптов, задержка, стоимость, ИИ',
            ],

            // --- Backend-разработка (4) ---
            [
                'rubric' => 'Backend-разработка',
                'title' => 'Очереди в Laravel: Jobs, воркеры и отложенные задачи',
                'excert' => 'Разбираем очереди Laravel: постановка задач, отложенные Jobs, повторные попытки и мониторинг длинных очередей.',
                'tags' => ['Laravel', 'PHP', 'Backend'],
                'keywords' => 'Laravel, очереди, Jobs, фоновые задачи',
            ],
            [
                'rubric' => 'Backend-разработка',
                'title' => 'Сервисный слой в Laravel: куда девать бизнес-логику',
                'excert' => 'Тонкие контроллеры и сервисы: как разложить логику по классам, не утонув в бойлерплейте и «божественных» моделях.',
                'tags' => ['Laravel', 'PHP', 'Паттерны'],
                'keywords' => 'сервисный слой, SOLID, Laravel, архитектура',
            ],
            [
                'rubric' => 'Backend-разработка',
                'title' => 'Транзакции и блокировки в MySQL: как не потерять данные',
                'excert' => 'Уровни изоляции, блокировки строк и типичные гонки при списании баланса — на примерах MySQL и Laravel.',
                'tags' => ['MySQL', 'SQL', 'PHP'],
                'keywords' => 'транзакции, блокировки, изоляция, MySQL',
            ],
            [
                'rubric' => 'Backend-разработка',
                'title' => 'Версионирование REST API: URL, заголовки и совместимость',
                'excert' => 'Сравняем подходы к версионированию API и разбираем, как не ломать клиентов обратно несовместимыми изменениями.',
                'tags' => ['API', 'Backend', 'PHP'],
                'keywords' => 'REST, версионирование, API, совместимость',
            ],

            // --- DevOps (5) ---
            [
                'rubric' => 'DevOps',
                'title' => 'Docker Compose для локальной разработки PHP-проекта',
                'excert' => 'Собираем окружение в Docker Compose: приложение, БД, почтовик и планировщик — с готовыми командами для повседневной работы.',
                'tags' => ['DevOps', 'PHP', 'Инструменты веб-разработки'],
                'keywords' => 'Docker, Compose, окружение, DevOps',
            ],
            [
                'rubric' => 'DevOps',
                'title' => 'CI/CD для PHP-проекта: линтеры, тесты и деплой',
                'excert' => 'Настраиваем конвейер: запуск тестов на каждый пул-реквест, сборка фронтенда и безопасный деплой на прод.',
                'tags' => ['DevOps', 'Git', 'PHP'],
                'keywords' => 'CI/CD, пайплайн, деплой, тесты',
            ],
            [
                'rubric' => 'DevOps',
                'title' => 'Медленные запросы MySQL: лог и профилирование',
                'excert' => 'Включаем slow query log, читаем EXPLAIN и находим запросы, которые держат нагрузку на базе.',
                'tags' => ['MySQL', 'SQL', 'Databases', 'DevOps'],
                'keywords' => 'slow query log, EXPLAIN, профилирование, MySQL',
            ],
            [
                'rubric' => 'DevOps',
                'title' => 'Виртуальные хосты Apache и проксирование PHP-приложения',
                'excert' => 'Настраиваем Apache: виртуальные хосты, mod_proxy и раздача нескольких проектов на одном сервере.',
                'tags' => ['Apache', 'DevOps', 'PHP'],
                'keywords' => 'Apache, виртуальные хосты, proxy, DevOps',
            ],
            [
                'rubric' => 'DevOps',
                'title' => 'Cron и планировщик Laravel: надёжные фоновые задачи',
                'excert' => 'Расписание задач без дублей и гонок: withoutOverlapping, mutex, перезапуск упавших воркеров и мониторинг.',
                'tags' => ['Laravel', 'PHP', 'DevOps'],
                'keywords' => 'cron, планировщик, artisan, фоновые задачи',
            ],

            // --- Frontend-разработка (4) ---
            [
                'rubric' => 'Frontend-разработка',
                'title' => 'Сборка ассетов на Vite: кэш и code splitting',
                'excert' => 'Как Vite разбирает бандл на чанки, зачем хэши в именах файлов и как не сломать кэширование при деплое.',
                'tags' => ['JavaScript', 'NodeJS', 'Инструменты веб-разработки'],
                'keywords' => 'Vite, сборка, кэш, code splitting',
            ],
            [
                'rubric' => 'Frontend-разработка',
                'title' => 'TypeScript для бэкендера: типы, интерфейсы, дженерики',
                'excert' => 'Переход с PHP на TypeScript: как типы, интерфейсы и дженерики помогают держать контракты на фронтенде.',
                'tags' => ['JavaScript', 'NodeJS'],
                'keywords' => 'TypeScript, типы, дженерики, JavaScript',
            ],
            [
                'rubric' => 'Frontend-разработка',
                'title' => 'Работа с API из браузера: fetch, axios и обработка ошибок',
                'excert' => 'Таймауты, повторные запросы и отмена запросов — аккуратная работа с API на JavaScript во фронтенде.',
                'tags' => ['JavaScript', 'API'],
                'keywords' => 'fetch, axios, ошибки, API',
            ],
            [
                'rubric' => 'Frontend-разработка',
                'title' => 'Компоненты на Blade и Alpine: живой интерфейс без SPA',
                'excert' => 'Переиспользуемые Blade-компоненты и Alpine-алгоритмы: интерактивный интерфейс без тяжёлого фреймворка.',
                'tags' => ['JavaScript'],
                'keywords' => 'Blade, Alpine, компоненты, JavaScript',
            ],

            // --- Архитектура (2) ---
            [
                'rubric' => 'Архитектура',
                'title' => 'Гексагональная архитектура: порты и адаптеры в PHP',
                'excert' => 'Переносим гексагональную архитектуру на PHP: домен, порты, адаптеры и границы между слоями приложения.',
                'tags' => ['Архитектура', 'Паттерны', 'PHP'],
                'keywords' => 'гексагональная архитектура, порты, адаптеры',
            ],
            [
                'rubric' => 'Архитектура',
                'title' => 'Как выбрать хранилище: реляционные и документные СУБД',
                'excert' => 'Сравниваем реляционные и документные хранилища: модель данных, согласованность, масштабирование и цена ошибок выбора.',
                'tags' => ['Databases', 'SQL', 'MySQL', 'Архитектура'],
                'keywords' => 'СУБД, хранилище, документные БД, архитектура',
            ],

            // --- Инструменты веб-разработки (2) ---
            [
                'rubric' => 'Инструменты веб-разработки',
                'title' => 'OpenAPI и Postman: контрактное тестирование API',
                'excert' => 'Описываем API в OpenAPI, генерируем коллекции и проверяем реализацию на соответствие контракту в тестах.',
                'tags' => ['API', 'Инструменты веб-разработки'],
                'keywords' => 'OpenAPI, Postman, контракт, тесты API',
            ],
            [
                'rubric' => 'Инструменты веб-разработки',
                'title' => 'Xdebug и PHPUnit: отладка и покрытие тестами',
                'excert' => 'Подключаем Xdebug внутри Docker, отлаживаем тесты и смотрим покрытие, чтобы найти непроверенные участки кода.',
                'tags' => ['PHP', 'Инструменты веб-разработки'],
                'keywords' => 'Xdebug, PHPUnit, отладка, покрытие',
            ],
        ];

        foreach ($moreArticles as $index => $data) {
            $this->createArticle($data, $author, $rubricIds, $tagIds, [
                'published_days_ago' => 3 + $index * 3,
            ]);
        }

        // \App\Models\User::factory(16)->create();

        // Article::factory(32)->create();

        // rescue(function () {
        //     ArticleTag::factory(16)->create();
        //     Comment::factory(32)->create();
        // });
    }

    /**
     * Создаёт тестовую статью с текстом, привязкой к рубрике и набору тэгов.
     *
     * @param  array{rubric: string, title: string, excert: string, tags: array<int, string>, keywords: string}  $data
     * @param  Collection<string, int>  $rubricIds  Карта «название рубрики => id»
     * @param  Collection<string, int>  $tagIds  Карта «название тэга => id»
     * @param  array{published_days_ago?: int}  $options  Отступ публикации от текущей даты в днях
     */
    private function createArticle(
        array $data,
        User $author,
        Collection $rubricIds,
        Collection $tagIds,
        array $options = [],
    ): Article {
        $daysAgo = $options['published_days_ago'] ?? 1;

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
            'published_at' => now()->subDays($daysAgo),
            'viewed' => fake()->numberBetween(1, 1000),
            'keywords' => $data['keywords'],
            'meta_desc' => $data['excert'],
        ]);

        $article->tags()->attach(
            collect($data['tags'])
                ->map(fn (string $tag) => $tagIds[$tag] ?? null)
                ->filter()
                ->values()
                ->all()
        );

        Tag::updateCountArticles($article);

        return $article;
    }
}
