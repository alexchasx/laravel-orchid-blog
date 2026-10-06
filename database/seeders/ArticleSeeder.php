<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Набор тестовых статей: первые 5 — «свежие», остальные — с отступом по датам.
     */
    private const ARTICLES = [
        // --- 5 свежих статей ---
        [
            'rubric'  => 'AI-engineering',
            'title'   => 'Как OpenSpec помогает проектировать ИИ-агентов',
            'excerpt'  => 'Разбираемся, как спецификации OpenSpec упрощают проектирование и постановку задач для ИИ-агентов.',
            'tags'    => ['AI', 'OpenSpec'],
            'keywords' => 'ИИ, OpenSpec, агенты, спецификации',
        ],
        [
            'rubric'  => 'Backend-разработка',
            'title'   => 'Оптимизация запросов в Laravel: практические приёмы',
            'excerpt'  => 'Eager-loading, индексы и работа с Carbon при разборе практических примеров оптимизации.',
            'tags'    => ['PHP', 'Laravel', 'Carbon'],
            'keywords' => 'Laravel, PHP, оптимизация, Carbon',
        ],
        [
            'rubric'  => 'DevOps',
            'title'   => 'Настройка Nginx и балансировки нагрузки для высоконагруженных сервисов',
            'excerpt'  => 'Практический гайд по настройке Nginx, upstream-ов и балансировки нагрузки.',
            'tags'    => ['Nginx', 'DevOps'],
            'keywords' => 'Nginx, DevOps, балансировка, нагрузка',
        ],
        [
            'rubric'  => 'Архитектура',
            'title'   => 'SOLID и GRASP: принципы проектирования на практике',
            'excerpt'  => 'Сопоставляем принципы SOLID и GRASP и смотрим, как они работают в реальных проектах.',
            'tags'    => ['SOLID', 'GRASP', 'Паттерны'],
            'keywords' => 'SOLID, GRASP, паттерны, архитектура',
        ],
        [
            'rubric'  => 'Frontend-разработка',
            'title'   => 'Паттерны для работы с API на JavaScript',
            'excerpt'  => 'Обзор популярных паттернов работы с API на JavaScript и NodeJS.',
            'tags'    => ['JavaScript', 'NodeJS'],
            'keywords' => 'JavaScript, NodeJS, API, паттерны',
        ],

        // --- 20 статей с отступом по датам (published_days_ago = 3 + index * 3) ---
        [
            'rubric'  => 'AI-engineering',
            'title'   => 'RAG-пайплайн: от векторного поиска к готовому ответу',
            'excerpt'  => 'Собираем RAG: нарезка документов, эмбеддинги, векторный поиск и формирование ответа на основе найденных фрагментов.',
            'tags'    => ['AI', 'API', 'Backend'],
            'keywords' => 'RAG, эмбеддинги, векторный поиск, ИИ',
        ],
        [
            'rubric'  => 'AI-engineering',
            'title'   => 'Оценка качества ответов LLM: метрики и датасеты',
            'excerpt'  => 'Как измерять качество ответов языковых моделей: размеченные датасеты, точность, полнота и регрессионные тесты промптов.',
            'tags'    => ['AI'],
            'keywords' => 'LLM, метрики, датасеты, оценка качества',
        ],
        [
            'rubric'  => 'AI-engineering',
            'title'   => 'Кэширование промптов и ускорение ИИ-сервисов',
            'excerpt'  => 'Приёмы снижения задержки и стоимости ИИ-сервиса: кэш промптов, батчинг запросов и переиспользование контекста.',
            'tags'    => ['AI', 'API', 'Backend'],
            'keywords' => 'кэш промптов, задержка, стоимость, ИИ',
        ],
        [
            'rubric'  => 'Backend-разработка',
            'title'   => 'Очереди в Laravel: Jobs, воркеры и отложенные задачи',
            'excerpt'  => 'Разбираем очереди Laravel: постановка задач, отложенные Jobs, повторные попытки и мониторинг длинных очередей.',
            'tags'    => ['Laravel', 'PHP', 'Backend'],
            'keywords' => 'Laravel, очереди, Jobs, фоновые задачи',
        ],
        [
            'rubric'  => 'Backend-разработка',
            'title'   => 'Сервисный слой в Laravel: куда девать бизнес-логику',
            'excerpt'  => 'Тонкие контроллеры и сервисы: как разложить логику по классам, не утонув в бойлерплейте и «божественных» моделях.',
            'tags'    => ['Laravel', 'PHP', 'Паттерны'],
            'keywords' => 'сервисный слой, SOLID, Laravel, архитектура',
        ],
        [
            'rubric'  => 'Backend-разработка',
            'title'   => 'Транзакции и блокировки в MySQL: как не потерять данные',
            'excerpt'  => 'Уровни изоляции, блокировки строк и типичные гонки при списании баланса — на примерах MySQL и Laravel.',
            'tags'    => ['MySQL', 'SQL', 'PHP'],
            'keywords' => 'транзакции, блокировки, изоляция, MySQL',
        ],
        [
            'rubric'  => 'Backend-разработка',
            'title'   => 'Версионирование REST API: URL, заголовки и совместимость',
            'excerpt'  => 'Сравним подходы к версионированию API и разбираем, как не ломать клиентов обратно несовместимыми изменениями.',
            'tags'    => ['API', 'Backend', 'PHP'],
            'keywords' => 'REST, версионирование, API, совместимость',
        ],
        [
            'rubric'  => 'DevOps',
            'title'   => 'Docker Compose для локальной разработки PHP-проекта',
            'excerpt'  => 'Собираем окружение в Docker Compose: приложение, БД, почтовик и планировщик — с готовыми командами для повседневной работы.',
            'tags'    => ['DevOps', 'PHP', 'Инструменты веб-разработки'],
            'keywords' => 'Docker, Compose, окружение, DevOps',
        ],
        [
            'rubric'  => 'DevOps',
            'title'   => 'CI/CD для PHP-проекта: линтеры, тесты и деплой',
            'excerpt'  => 'Настраиваем конвейер: запуск тестов на каждый пул-реквест, сборка фронтенда и безопасный деплой на прод.',
            'tags'    => ['DevOps', 'Git', 'PHP'],
            'keywords' => 'CI/CD, пайплайн, деплой, тесты',
        ],
        [
            'rubric'  => 'DevOps',
            'title'   => 'Медленные запросы MySQL: лог и профилирование',
            'excerpt'  => 'Включаем slow query log, читаем EXPLAIN и находим запросы, которые держат нагрузку на базе.',
            'tags'    => ['MySQL', 'SQL', 'Databases', 'DevOps'],
            'keywords' => 'slow query log, EXPLAIN, профилирование, MySQL',
        ],
        [
            'rubric'  => 'DevOps',
            'title'   => 'Виртуальные хосты Apache и проксирование PHP-приложения',
            'excerpt'  => 'Настраиваем Apache: виртуальные хосты, mod_proxy и раздача нескольких проектов на одном сервере.',
            'tags'    => ['Apache', 'DevOps', 'PHP'],
            'keywords' => 'Apache, виртуальные хосты, proxy, DevOps',
        ],
        [
            'rubric'  => 'DevOps',
            'title'   => 'Cron и планировщик Laravel: надёжные фоновые задачи',
            'excerpt'  => 'Расписание задач без дублей и гонок: withoutOverlapping, mutex, перезапуск упавших воркеров и мониторинг.',
            'tags'    => ['Laravel', 'PHP', 'DevOps'],
            'keywords' => 'cron, планировщик, artisan, фоновые задачи',
        ],
        [
            'rubric'  => 'Frontend-разработка',
            'title'   => 'Сборка ассетов на Vite: кэш и code splitting',
            'excerpt'  => 'Как Vite разбирает бандл на чанки, зачем хэши в именах файлов и как не сломать кэширование при деплое.',
            'tags'    => ['JavaScript', 'NodeJS', 'Инструменты веб-разработки'],
            'keywords' => 'Vite, сборка, кэш, code splitting',
        ],
        [
            'rubric'  => 'Frontend-разработка',
            'title'   => 'TypeScript для бэкендера: типы, интерфейсы, дженерики',
            'excerpt'  => 'Переход с PHP на TypeScript: как типы, интерфейсы и дженерики помогают держать контракты на фронтенде.',
            'tags'    => ['JavaScript', 'NodeJS'],
            'keywords' => 'TypeScript, типы, дженерики, JavaScript',
        ],
        [
            'rubric'  => 'Frontend-разработка',
            'title'   => 'Работа с API из браузера: fetch, axios и обработка ошибок',
            'excerpt'  => 'Таймауты, повторные запросы и отмена запросов — аккуратная работа с API на JavaScript во фронтенде.',
            'tags'    => ['JavaScript', 'API'],
            'keywords' => 'fetch, axios, ошибки, API',
        ],
        [
            'rubric'  => 'Frontend-разработка',
            'title'   => 'Компоненты на Blade и Alpine: живой интерфейс без SPA',
            'excerpt'  => 'Переиспользуемые Blade-компоненты и Alpine-алгоритмы: интерактивный интерфейс без тяжёлого фреймворка.',
            'tags'    => ['JavaScript'],
            'keywords' => 'Blade, Alpine, компоненты, JavaScript',
        ],
        [
            'rubric'  => 'Архитектура',
            'title'   => 'Гексагональная архитектура: порты и адаптеры в PHP',
            'excerpt'  => 'Переносим гексагональную архитектуру на PHP: домен, порты, адаптеры и границы между слоями приложения.',
            'tags'    => ['Архитектура', 'Паттерны', 'PHP'],
            'keywords' => 'гексагональная архитектура, порты, адаптеры',
        ],
        [
            'rubric'  => 'Архитектура',
            'title'   => 'Как выбрать хранилище: реляционные и документные СУБД',
            'excerpt'  => 'Сравниваем реляционные и документные хранилища: модель данных, согласованность, масштабирование и цена ошибок выбора.',
            'tags'    => ['Databases', 'SQL', 'MySQL', 'Архитектура'],
            'keywords' => 'СУБД, хранилище, документные БД, архитектура',
        ],
        [
            'rubric'  => 'Инструменты веб-разработки',
            'title'   => 'OpenAPI и Postman: контрактное тестирование API',
            'excerpt'  => 'Описываем API в OpenAPI, генерируем коллекции и проверяем реализацию на соответствие контракту в тестах.',
            'tags'    => ['API', 'Инструменты веб-разработки'],
            'keywords' => 'OpenAPI, Postman, контракт, тесты API',
        ],
        [
            'rubric'  => 'Инструменты веб-разработки',
            'title'   => 'Xdebug и PHPUnit: отладка и покрытие тестами',
            'excerpt'  => 'Подключаем Xdebug внутри Docker, отлаживаем тесты и смотрим покрытие, чтобы найти непроверенные участки кода.',
            'tags'    => ['PHP', 'Инструменты веб-разработки'],
            'keywords' => 'Xdebug, PHPUnit, отладка, покрытие',
        ],
    ];

    /**
     * Запускает сидирование статей.
     */
    public function run(): void
    {
        $author = User::query()->firstOrCreate(
            ['email' => 'author@example.test'],
            ['name' => 'Автор тестовых статей', 'password' => 'password'],
        );

        $rubricIds = Rubric::pluck('id', 'title');
        $tagIds = Tag::pluck('id', 'title');

        foreach (self::ARTICLES as $index => $data) {
            $this->createArticle($data, $author, $rubricIds, $tagIds, [
                'published_days_ago' => $index < 5
                    ? 1
                    : 3 + ($index - 5) * 3,
            ]);
        }
    }

    /**
     * Создаёт тестовую статью с текстом, привязкой к рубрике и набору тэгов.
     *
     * @param  array{rubric: string, title: string, excerpt: string, tags: array<int, string>, keywords: string}  $data
     * @param  Collection<string, int>  $rubricIds  Карта «название рубрики => id»
     * @param  Collection<string, int>  $tagIds  Карта «название тэга => id»
     * @param  array{published_days_ago: int}  $options  Отступ публикации от текущей даты в днях
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
            'excerpt' => $data['excerpt'],
            'content_raw' => <<<MD
## Введение

{$data['excerpt']}

## Основная часть

В этой статье разберём ключевые подходы и приёмы для категории «{$data['rubric']}».
Здесь вы найдёте практические примеры, разбор типовых задач и полезные рекомендации.

### Пример

```php
// Короткий фрагмент кода для иллюстрации
\$items = collect([1, 2, 3])->map(fn(\$n) => \$n * 2);
```

## Заключение

Надеемся, материал был полезен. Оставляйте вопросы в комментариях!
MD,
            'is_published' => true,
            'published_at' => now()->subDays($daysAgo),
            'viewed' => fake()->numberBetween(1, 1000),
            'keywords' => $data['keywords'],
            'meta_desc' => $data['excerpt'],
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
