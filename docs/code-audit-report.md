# Аудит кода — Laravel Orchid Blog

Промпт: `prompts/audit-universal.prompt` · Дата аудита: 2026-09-22

## 0. Определение стека (по README.md)

Из секции «Используемые технологии» README.md:

- **Backend:** PHP `^8.5`, Laravel Framework `^13.0`, Orchid Platform `^14.53`, Laravel Sanctum `^4.0`, Guzzle `^7.9`.
- **Frontend:** Blade + Vite `^6.0`, Tailwind CSS `^3.4`, Alpine.js `^3.14`, Sass/PostCSS.
- **БД и инфраструктура:** MySQL 8.0, Docker / Docker Compose (nginx, PHP-FPM 8.5, Node, MySQL, phpMyAdmin, MailHog).
- **Инструменты:** Laravel Breeze, IDE Helper, Faker; PHPUnit `^11.5`.

Применены категории проверок: **Общие** + **PHP/Laravel** + **инфраструктура (Docker/MySQL/env)**. Релевантные версии учтены: Laravel 13 (`bootstrap/app.php`, без `Http/Kernel.php`), MySQL 8 (миграции, `lockForUpdate`), Docker-only запуск.

---

## 1. Найденные недочёты

### 🔴 КРИТИЧНО

---
✅ ПРОВЕРЕНО: НЕ ВОСПРОИЗВОДИТСЯ (недочёт уже исправлен)
Бейджи меню Orchid в [`app/Orchid/PlatformProvider.php`](app/Orchid/PlatformProvider.php:52) используют агрегатные `COUNT(*)` через `Model::count()` — конструкции `Model::all('id')->count()` в коде нет:
```php
Menu::make(__('Обратная связь'))
    ->route('platform.contact.list')
    ->badge(fn () => Contact::count()),
// Subscriber::active()->count(), Article::count(), Rubric::count(),
// Tag::count(), Comment::count(), User::count(), Role::count()
```
Все 8 бейджей выполняют запрос `COUNT(*)` в СУБД без загрузки коллекций в память. Недочёт снят.
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Вывод HTML статьи без санитизации (`{!! $contentHtml !!}`) — вектор Stored XSS · ✅ ПОДТВЕРЖДЕНО
ФРАГМЕНТ КОДА:
```php
// app/Models/Article.php:90 — CommonMark по умолчанию ПРОПускает сырой HTML наружу
$article->content_html = (new CommonMarkConverter())->convert((string) $article->content_raw)->getContent();
// resources/views/article.blade.php:45
{!! $contentHtml !!}
```
РИСК: Markdown-конвертер CommonMark по спецификации passthrough-ит сырой inline/block HTML. Любое `<script>`/`<img onerror>`/`onload=` в `content_raw` попадает в `content_html` и рендерится «как есть». Если появится автор/редактор с меньшими правами (не супер-админ) — это XSS против всех читателей и против сессий админов.
ПРИЧИНА: Доверие к «сырому» полю + отсутствие политики экранирования. В `ArticleListScreen::createOrUpdateArticle` поле `content_html` ещё и берётся прямо из запроса (`$request->input('article.content_html')`) — прямая инъекция HTML в модель.
РЕШЕНИЕ:
1. Отключить/очистить сырой HTML на этапе конвертации:
```php
$config = ['html_input' => 'strip', 'allow_unsafe_link' => false]; // или 'escape'
$article->content_html = (new CommonMarkConverter($config))->convert((string) $article->content_raw)->getContent();
```
2. Либо прогонять `content_html` через HTML Purifier / `league/html-to-markdown` перед выводом.
3. Убрать `'content_html'` из `$fillable` и из присваиваний в `ArticleListScreen` — это производное поле, его нельзя принимать из запроса (см. недочёт про валидацию).
---

### 🟠 ВЫСОКИЙ

---
НАЙДЕННЫЙ НЕДОЧЁТ: Ссылка на Telegram захардкожена и игнорирует конфиг · ✅ ПОДТВЕРЖДЕНО · ✅ ИСПРАВЛЕНО ([`resources/views/layouts/techlog.blade.php`](resources/views/layouts/techlog.blade.php:78))
ФРАГМЕНТ КОДА:
```blade
@if(config('my_config.my_telegram'))
    <a href="https://t.me/" target="_blank" rel="noopener">Telegram</a>
@endif
```
РИСК: Ссылка всегда ведёт на пустой `https://t.me/` вместо реального канала. Конфиг `my_config.my_telegram` используется только как флаг «показывать/нет», но не подставляется в URL.
ПРИЧИНА: Магическая строка `https://t.me/` вместо интерполяции значения конфига.
РЕШЕНИЕ:
```blade
@if(config('my_config.my_telegram'))
    <a href="{{ config('my_config.my_telegram') }}" target="_blank" rel="noopener">Telegram</a>
@endif
```
(значение в `.env` хранить полным URL `https://t.me/username` либо нормализовать префикс в `config`).
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: N+1 и падение админ-таблицы статей при удалённой рубрике · ✅ ПОДТВЕРЖДЕНО · ✅ ИСПРАВЛЕНО
ФРАГМЕНТ КОДА:
```php
// app/Orchid/Screens/Article/ArticleListScreen.php:26 — нет ->with('rubric')
'articles' => Article::filters()->defaultSort('created_at', 'desc')->paginate(24),
// app/Orchid/Layouts/Article/ArticleListTable.php:64 — без null-safe
TD::make('rubric_title', ...)->render(fn (Article $article) => $article->rubric->title);
```
РИСК: (1) N+1: на каждую из 24 строк — отдельный запрос за `rubric`. (2) `Rubric` использует `SoftDeletes`, поэтому `$article->rubric` может быть `null` → `null->title` → Error 500 на странице списка статей.
ПРИЧИНА: Отсутствие eager loading + небезопасное обращение к nullable-отношению.
РЕШЕНИЕ:
```php
'articles' => Article::filters()->with('rubric')->defaultSort('id', 'desc')->paginate(24),
// ...
->render(fn (Article $a) => $a->rubric?->title ?? '—');
```
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: `MY_EMAIL` объявлен в `.env`/README, но отсутствует в `config/my_config.php` · ✅ ИСПРАВЛЕНО
ФРАГМЕНТ КОДА:
```php
// config/my_config.php — нет ключа my_email
'sub_logo','slogan','my_github','contact_email','my_telegram'
// .env.example
MY_EMAIL=
```
РИСК: Переменная `MY_EMAIL` молча не читается: `config('my_config.my_email')` вернёт `null`. Расхождение документации и кода → «пропавший» контакт на фронте.
ПРИЧИНА: Конфиг-файл не покрывает все env-переменные, заявленные в README/AGENTS/`.env.example`.
РЕШЕНИЕ: Либо добавить `'my_email' => env('MY_EMAIL', '')` в `config/my_config.php`, либо удалить `MY_EMAIL` из `.env.example` и документации, если он не используется.
---

### 🟡 СРЕДНИЙ

---
НАЙДЕННЫЙ НЕДОЧЁТ: Запланированная на сегодня «будущая» статья показывается до наступления времени · ✅ ПОДТВЕРЖДЕНО ([`app/Models/Article.php`](app/Models/Article.php:187))
ФРАГМЕНТ КОДА:
```php
return $builder->whereDate('published_at', '<=', Carbon::now())
    ->where('is_published', true)
```
РИСК: `whereDate` сравнивает только дату без времени. Статья, опубликованная вручную с `is_published=true` и `published_at` = сегодня 23:00, видна уже утром — обход «расписания».
ПРИЧИНА: Секуное сравнение подменено date-сравнением.
РЕШЕНИЕ:
```php
->where('published_at', '<=', now())
```
(при этом `is_published` остаётся основным выключателем, а команда `articles:publish-scheduled` — автопубликом).
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Рассылка «в очереди», но `QUEUE_CONNECTION=sync` → блокировка веб-запроса · ✅ ПОДТВЕРЖДЕНО
ФРАГМЕНТ КОДА:
```php
// app/Mail/NewArticleMail.php:14 — implements ShouldQueue
class NewArticleMail extends Mailable implements ShouldQueue { ... }
// app/Observers/ArticleObserver.php:29 — chunkById(100) + Mail::to(...)->send(...)
// .env.example:26
QUEUE_CONNECTION=sync
```
РИСК: С `sync`-драйвером `ShouldQueue` игнорируется: письма всем подписчикам (chunk по 100) отправляются синхронно внутри запроса публикации/команды → таймауты PHP-FPM/nginx и зависание админки при большой базе подписчиков.
ПРИЧИНА: Ожидание очереди при синхронном драйвере; отправка писем в цикле без `batch`/job'ов.
РЕШЕНИЕ: Использовать `QUEUE_CONNECTION=database` (или redis) + запустить `queue:worker` (добавить сервис в `docker-compose`); либо явно диспетчить `NewArticleMail::dispatch(...)` в очередь и вынести чанкование в queued-Job.
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Дыры в валидации входных данных · ✅ ПОДТВЕРЖДЕНО
ФРАГМЕНТ КОДА:
```php
// app/Http/Requests/CommentRequest.php:29
'comment' => ['required', 'max:5000'],            // нет 'string'
// app/Http/Requests/ArticleRequest.php:30
'article.rubric_id' => ['required'],               // нет 'exists:rubrics,id'
'article.content_raw' => ['required'],             // нет 'string'/'max'
// 'article.tags' вообще не валидируется (sync принимает что угодно)
```
РИСК: (1) `comment[]` как массив пройдёт `max` (считает элементы) и попадёт в строковую колонку → ошибка записи/мусор. (2) Несуществующий `rubric_id` → битая FK/паддаунти. (3) `content_raw` без лимита → переполнение колонки/DoS. (4) `tags` — произвольные id в `sync()`.
ПРИЧИНА: Неполные правила FormRequest; часть полей вообще не покрыта.
РЕШЕНИЕ:
```php
// CommentRequest
'comment' => ['required', 'string', 'max:5000'],
// ArticleRequest
'article.rubric_id'   => ['required', 'integer', 'exists:rubrics,id'],
'article.content_raw' => ['required', 'string', 'max:1_000_000'],
'article.tags'        => ['nullable', 'array'],
'article.tags.*'      => ['integer', 'exists:tags,id'],
```
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Нет тестов на часть сложной логики (автопубликация, админ-экран статей) · ⚠️ ЧАСТИЧНО УСТАРЕЛО
ФРАГМЕНТ КОДА:
```php
// Не покрыты тестами (поиск по tests/ — 0 совпадений):
PublishScheduledArticles::handle()         // автопубликация по расписанию
ArticleListScreen::createOrUpdateArticle() // updateOrCreate + tags sync
// Уже покрыты тестами (вопреки прежней редакции отчёта):
ArticleService::withToc()                  // tests/Unit/ArticleServiceTest.php (5 тестов test_with_toc_*)
SubscriberController::store()              // tests/Feature/SubscriberTest.php (вкл. повторную подписку)
SubscriberController::unsubscribe()        // tests/Feature/SubscriberTest.php (вкл. невалидный токен)
```
РИСК: Регрессии в автопубликации (`articles:publish-scheduled`) и в `createOrUpdateArticle` (updateOrCreate + sync тегов) останутся незамеченными.
ПРИЧИНА: Прежняя редакция относила к непокрытым `withToc`, подписку/отписку и рассылку, но на дату проверки они уже покрыты (`tests/Unit/ArticleServiceTest.php`, `tests/Feature/SubscriberTest.php`); непокрытыми остались только команда автопубликации и админ-экран статей.
РЕШЕНИЕ: Добавить Feature-тесты: `articles:publish-scheduled` (публикует только `published_at<=now`, триггерит рассылку через `ArticleObserver::updated`), `createOrUpdateArticle` (создание/обновление + sync тегов).
---

### 🟢 НИЗКИЙ

---
НАЙДЕННЫЙ НЕДОЧЁТ: «Минут чтения» — магические числа и `rand()` прямо в Blade
ФРАГМЕНТ КОДА:
```blade
{{ rand(5, 15) }} минут чтения ...   {{-- article.blade.php --}}
{{ rand(4, 12) }} МИН                {{-- index.blade.php --}}
```
РИСК: Время чтения случайно и меняется при каждом рендере (в т.ч. между пагинацией/кэшем) — вводит читателя в заблуждение и ломает детерминизм/кэширование страниц.
ПРИЧИНА: Магические числа и генерация данных в представлении вместо бизнес-логики.
РЕШЕНИЕ: Считать по длине контента и вынести в accessor:
```php
// Article
public function getReadingMinutesAttribute(): int {
    return max(1, (int) ceil(str_word_count(strip_tags((string) $this->content_html)) / 200));
}
```
И в шаблоне: `{{ $article->reading_minutes }} минут чтения`.
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Мёртвая колонка `viewed` · ✅ ПОДТВЕРЖДЕНО
ФРАГМЕНТ КОДА:
```php
protected $fillable = [ ..., 'viewed', ... ]; // app/Models/Article.php:122
// миграция database/migrations/2022_05_19_124837_create_articles_table.php:38
// нигде в app/ и resources/ нет инкремента/чтения viewed
```
РИСК: Счётчик просмотров объявлен, но не используется → ложное ощущение аналитики; мёртвое поле путает при рефакторинге.
ПРИЧИНА: Незавершённая фича.
РЕШЕНИЕ: Либо реализовать (`$article->increment('viewed')` в `ArticleController::show` вне транзакции/с кэшем), либо удалить колонку и из `$fillable`, и из миграции.
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Небезопасный редирект после комментария через `url()->previous()` · ✅ ПОДТВЕРЖДЕНО ([`app/Http/Controllers/CommentController.php`](app/Http/Controllers/CommentController.php:39))
ФРАГМЕНТ КОДА:
```php
$route = url()->previous() . '#comment' . $comment->id;
return redirect()->to($route);
```
РИСК: `url()->previous()` строится по Referer/сессии — редирект может уйти на внешний URL (открытый редирект) и «съесть» якорь при наличии своего `#`.
ПРИЧИНА: Доверие к предыдущему URL вместо явного маршрута сущности.
РЕШЕНИЕ: `return redirect()->route('articleShow', $article).'#comment'.$comment->id;`
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: `contact.store` без throttle и с аномальным лимитом сообщения · ✅ ПОДТВЕРЖДЕНО
ФРАГМЕНТ КОДА:
```php
'message' => ['required', 'string', 'min:10', 'max:500000'], // app/Http/Requests/ContactRequest.php:29
// routes/web.php:14 — contact.store без ->middleware('throttle:...') (в отличие от comment/subscribe)
```
РИСК: Флуд формы обратной связи + отправка полумегабайтных сообщений → разрастание БД/спам.
ПРИЧИНА: Отсутствие rate-limit на публичной POST-ручке.
РЕШЕНИЕ: `Route::post('contact.store', ...)->middleware('throttle:5,1')` и `max:5000`.
---

---
НАЙДЕННЫЙ НЕДОЧЁТ: Мелкие code-smell / легаси · ✅ ПОДТВЕРЖДЕНО
ФРАГМЕНТ КОДА:
```php
// app/Http/Controllers/CommentController.php:12 — должен наследовать Controller
class CommentController extends MainController { ... }
// app/Models/Comment.php:76 — легаси, нужен $casts
protected $dates = ['created_at','updated_at','deleted_at'];
// app/Orchid/Layouts/Article/ArticleListTable.php:71 — published_at уже Carbon (casts datetime), лишняя конвертация
Carbon::create($article->published_at);
// app/Orchid/Screens/Article/ArticleListScreen.php:26 — created_at нет в $allowedSorts Article (app/Models/Article.php:132) → сортировка молча игнорируется
defaultSort('created_at')
```
РИСК: Запутанная иерархия, устаревшие API ( `$dates` удалён/deprecated в новых Laravel), некорректная сортировка в админ-списке.
ПРИЧИНА: Остатки рефакторинга.
РЕШЕНИЕ: `CommentController extends Controller`; удалить `$dates` (даты и так кастятся); убрать лишний `Carbon::create(...)` (использовать `$article->published_at?->format('d.m.Y')`); добавить `created_at` в `$allowedSorts` или сортировать по `published_at`/`id`.
---

## 2. Что нужно приложить для полной проверки

Проверено на дату аудита (2026-09-22): типы колонок `articles.content_html` = `text` nullable, `articles.viewed` = `integer` nullable; `MathCaptchaRule::validate()` вызывает `MathCaptcha::check()` однократно. Для 100% аудита дополнительно нужны:

- **`database/migrations/*`** (кроме `articles`) — индексы на `slug`, `published_at`, `rubric_id`, `is_published`, `comments.article_id`, уникальность `subscriber.email`.
- **`app/Orchid/Screens/Contact/*`, `Subscriber/*`, `Rubric/*`, `Tag/*`** — единый стиль экранирования и `with()` в списках (не открывались полностью).
- **`docker/docker-compose.yml`** и **боевой `.env`** — оценка секретов/пароль БД (`root/root` по умолчанию), `APP_DEBUG`, доступности `queue:worker` и `cron` для `articles:publish-scheduled`.

## 3. Приоритетные действия (по критичности)

1. ~~Убрать `Model::all()->count()` из бейджей меню Orchid~~ → **выполнено**: в [`app/Orchid/PlatformProvider.php`](app/Orchid/PlatformProvider.php:52) уже стоит `Model::count()`.
2. **Закрыть Stored XSS:** санитизировать `content_html` (`html_input => strip` в CommonMark или HTML Purifier) и убрать `content_html` из `$fillable`/из приёма из запроса ([`app/Models/Article.php`](app/Models/Article.php:90), [`app/Orchid/Screens/Article/ArticleListScreen.php`](app/Orchid/Screens/Article/ArticleListScreen.php:116)).
3. **Починить функциональные баги:** ссылка Telegram из конфига ([`resources/views/layouts/techlog.blade.php`](resources/views/layouts/techlog.blade.php:77)); eager load `rubric` + null-safe в `ArticleListTable`; `whereDate` → `where(..., '<=', now())` в `published()`.
4. **Перевести рассылку на реальную очередь** (`QUEUE_CONNECTION=database` + `queue:worker`), иначе публикация статьи блокируется на отправке писем.
5. **Довести валидацию и тесты:** добавить `string`/`exists`/`max` в `CommentRequest`/`ArticleRequest`, throttle на `contact.store`; из тестов дописать только непокрытое — `articles:publish-scheduled` и `ArticleListScreen::createOrUpdateArticle()` (`withToc`, подписка/отписка, рассылка уже покрыты).
