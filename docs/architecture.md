# Архитектура: IT-блог на Laravel + Orchid

> Целевой документ: общее устройство приложения, слои, связи и договорённости.
> Точки входа для новичков — `AGENTS.md`, `Makefile`, `routes/`.

## 1. Обзор

Русскоязычный IT-блог «TECH//LOG». Состоит из **двух поверхностей**:

1. **Публичный сайт** — Blade/Vite, дизайн «TECH//LOG», шаблон `layouts.techlog`.
2. **Админ-панель Orchid** — `/admin`, экраны в `app/Orchid/`, собственная аутентификация Orchid (та же таблица `users`).

Бэкенд — **Laravel 13 (PHP ^8.5)**, админка — **Orchid Platform ^14**. Авторизация Breeze (Blade), комментарии и подписка — только Blade (без SPA/API-контроллеров).

## 2. Стек и зависимости (`composer.json`, `package.json`)

| Слой | Технология |
|---|---|
| PHP | ^8.5 |
| Фреймворк | Laravel ^13.0 |
| Админка | `orchid/platform` ^14.53 |
| Auth/токены | Laravel Breeze, Sanctum |
| Markdown | `league/common-markmark` (CommonMark, через пакет фреймворка) |
| Капча | самописная математическая (`app/Support/MathCaptcha.php`) + пакет `mews/captcha` и заготовка middleware `GoogleRecaptcha` |
| Фронтенд | Blade, Vite ^6, Sass, Tailwind ^3.4 (по факту — самописный SCSS), Alpine.js, vanilla JS |
| БД | MySQL 8 |
| Инфраструктура | Docker Compose (`docker/docker-compose.yml`) |

Сборка фронтенда — 5 входных точек Vite (`vite.config.js`): `resources/css/app.css`,
`resources/sass/style.scss` (legacy), `resources/js/app.js` (legacy/Alpine),
`resources/sass/techlog/index.scss` и `resources/js/techlog.js` (новый дизайн).

## 3. Структура каталогов `app/`

| Каталог | Назначение |
|---|---|
| `app/Http/Controllers/` | Публичная часть: `ArticleController`, `MainController`, `CommentController`, `ContactController`, `SubscriberController`, `ProfileController`, `Auth/` (Breeze) |
| `app/Http/Requests/` | FormRequest-валидация: `ArticleRequest`, `CommentRequest`, `ContactRequest`, `SubscribeRequest`, `ProfileUpdateRequest`, `Auth/*` |
| `app/Http/Middleware/` | `Localize`, `GoogleRecaptcha` (отключён), кастомные `TrustProxies`/`VerifyCsrfToken`, стандартные Breeze |
| `app/Models/` | Доменные модели: `Article`, `Rubric`, `Tag`, `ArticleTag`, `Comment`, `Contact`, `Subscriber`, `User` |
| `app/Services/` | `ArticleService` (выборки + TOC), `CacheService` (кэш сайдбара) |
| `app/Observers/` | `ArticleObserver`, `RubricObserver`, `TagObserver` — обновление счётчиков и рассылка |
| `app/Orchid/` | Админка: `Screens/`, `Layouts/`, `Filters/RoleFilter`, `Presenters/UserPresenter`, `PlatformProvider` |
| `app/Mail/` | `NewArticleMail` — письмо подписчикам о новой статье |
| `app/Console/Commands/` | `PublishScheduledArticles` — автопубликация по расписанию |
| `app/Support/`, `app/Rules/` | `MathCaptcha`, `MathCaptchaRule` |
| `app/helpers.php` | Глобальные функции `active_link()`, `alert()` (автозагрузка через composer `autoload-dev.files`) |

Остальные стандартные каталоги (`database/`, `resources/`, `routes/`, `config/`, `tests/`, `docker/`) — по шаблону Laravel.

## 4. Точка входа и middleware (`bootstrap/app.php`)

Laravel 13-стиль: ядро без `Http/Kernel.php`.

- **Маршруты**: `web` → `routes/web.php`, `commands` → `routes/console.php`, health-check `/up`.
- **Middleware**:
  - кастомный `TrustProxies` и `VerifyCsrfToken` заменены поверх стандартных;
  - в группу `web` добавлен `Localize` (устанавливает локаль из сессии);
  - алиас `access` → `Orchid\Platform\Http\Middleware\Access` (используется в `routes/web.php` для `/notpublic`).

## 5. Маршрутизация

### 5.1 Публичный сайт (`routes/web.php`)

| Маршрут | Контроллер | Назначение |
|---|---|---|
| `GET /` | `ArticleController@index` | Главная: опубликованные статьи с поиском |
| `GET /notpublic` | `ArticleController@showNotPublic` | Черновики, только администратор (`auth` + `access:platform.custom.articles`) |
| `GET /rubric/{rubric}` | `ArticleController@showByRubric` | Статьи рубрики (route-model binding по слагу) |
| `GET /tag/{tag}` | `ArticleController@showByTag` | Статьи по метке |
| `GET /article/{article:slug}` | `ArticleController@show` | Страница статьи + TOC |
| `GET /contact` / `POST /contact.store` | `ContactController` | Обратная связь |
| `POST /subscribe` (throttle:5,1) | `SubscriberController@store` | Подписка на рассылку (JSON + обычный POST) |
| `GET /unsubscribe/{token}` | `SubscriberController@unsubscribe` | Отписка по одноразовому токену |
| `POST /comment.create` (throttle:10,1) | `CommentController@store` | Комментарий (гость или авторизованный) |
| `DELETE /delete.{comment}` | `CommentController@delete` | Удаление комментария (auth) |
| `GET /setlocale/{locale}` | `MainController@setLocale` | Переключение языка (сессия) |
| `/dashboard`, `/profile*` | Breeze | Личная зона |

### 5.2 Админка Orchid (`routes/platform.php`)

Экраны: главная `/main`, статьи `/articles`, контакты `/contacts` + `/contact/{id}`, подписчики `/subscribers`, комментарии `/comments` + `/comment/{comment}`, рубрики `/rubrics`, метки `/tags`, профиль, пользователи и роли (стандарт Orchid). Пример-экраны (`example*`) — демо из коробки.

### 5.3 Консоль (`routes/console.php`)

- `Schedule::command('articles:publish-scheduled')->everyMinute()` — планировщик.
- Команда `articles:publish-scheduled` (`app/Console/Commands/PublishScheduledArticles.php`): переключает `is_published=true` у статей с `published_at <= now`; сохранение триггерит `ArticleObserver::updated` → рассылка подписчикам.

## 6. Модели и схема БД

Миграции — `database/migrations/`. Ключевые сущности:

| Сущность | Таблица | Связи | Особенности |
|---|---|---|---|
| `Article` | `articles` | belongsTo `Rubric`, belongsTo `User`, belongsToMany `Tag` (через `article_tags`), hasMany `Comment` | `softDeletes`; поле **`excert`** (опечатка, сохранена); `slug` уникален; в `booted()` на `saving` — markdown `content_raw` → `content_html` (CommonMark) и автогенерация слага |
| `Rubric` | `rubrics` | hasMany `Article` | `softDeletes`; `$timestamps=false`; `parent_id`, `slug`, `title`, `description` |
| `Tag` | `tags` | belongsToMany `Article`, hasMany `ArticleTag` | `softDeletes`; `$timestamps=false`; `active`, `popular`, `count_articles` (поддерживается вручную) |
| `ArticleTag` | `article_tags` | belongsToMany-связка | без timestamps |
| `Comment` | `comments` | belongsTo `Article`, belongsTo `User` | `softDeletes`; `active` — модерация (гости → `false`); `ip` для гостей |
| `Contact` | `contacts` | belongsTo `User` (nullable) | сообщения обратной связи; guest-поля `name`/`email` |
| `Subscriber` | `subscribers` | belongsTo `User` (nullable) | `email` уникален; `token` (64) — одноразовая отписка; `status` = `active`/`unsubscribed`; в `booted()` — автогенерация токена/статуса |
| `User` | `users` (Orchid) | hasMany `Article`, `Comment`, `Contact` | extends `Orchid\Platform\Models\User`; роли через `role_users`; `isAdmin()` = `hasAccess('platform.custom.articles')`; константы ролей `user`/`moderator`/`admin` |

### Ключевые скоупы и методы

- `Article::published()` — `is_published && published_at <= now`, сортировка `published_at desc`, eager-load `tags` (`app/Models/Article.php`).
- `Article::recents()`, `search()`, `rubric()`, `tags()`, `comments()`.
- `Rubric::articlePublished()`, `Tag::articlePublished()` — только рубрики/метки с опубликованными статьями (используются для сайдбара и кэша).
- `Tag::updateCountArticles()` — пересчёт `count_articles` для тегов статьи.
- `Subscriber::active()` / `isActive()` — активные подписчики.

## 7. Сервисный слой (`app/Services/`)

### `ArticleService`

- `getPublic()` — пагинация 12, колонки `[id, title, published_at]`.
- `getNotPublic()`, `getByRubric()`, `getByTag()` — выборки черновиков/по рубрике/по метке.
- `checkAccess()` — 403 для неопубликованной статьи, если пользователь не админ (`app/Services/ArticleService.php:25`).
- `withToc()` — парсит `content_html`, проставляет `id` заголовкам `<h2>/<h3>` и возвращает `[contentHtml, tocItems]` для оглавления в `article.blade.php`.

### `CacheService`

`remember()` — кэш на 2 дня по `Model::SIDEBAR_CACHE_KEY` (`sidebar-rubrics`/`sidebar-tags`). **Важно:** сейчас функционал активен частично — сайдбар используется только в legacy-шаблоне `layouts/base.blade.php`, а вызовы инвалидации кэша в наблюдателях `RubricObserver`/`TagObserver` закомментированы. Инъекция в `ArticleController` есть (`app/Http/Controllers/ArticleController.php:23`), но фактически не используется. Обновление кэша — через наблюдатели (паттерн «без Events/Listeners», см. `AGENTS.md`).

## 8. Наблюдатели (`app/Observers/`)

- **`ArticleObserver`** — главный по побочным эффектам:
  - `created`/`updated`/`deleted`/`restored`/`forceDeleted` → `Tag::updateCountArticles($article)`;
  - `created` → рассылка подписчикам `notifySubscribers()` (только если `is_published` и `published_at <= now`);
  - `updated` с `wasChanged('is_published')` → рассылка при публикации черновика.
- **`RubricObserver` / `TagObserver`** — заготовки инвалидации кэша (код обновления закомментирован).

Рассылка (`notifySubscribers`) — через `Subscriber::active()->chunkById(100, ...)`, письмо `NewArticleMail` (Mailable + ShouldQueue, шаблон `resources/views/emails/new-article.blade.php`), ошибки пишутся в лог.

## 9. Админ-панель Orchid (`app/Orchid/`)

### `PlatformProvider`

- **Меню** (`app/Orchid/PlatformProvider.php:44`): Главная, Обратная связь, Подписчики, Статьи, Рубрики, Метки, Комментарии, Пользователи, Роли — с бейджами-счётчиками (контакты, активные подписчики, статьи и т.д.).
- **Права**: стандартные системные (`platform.systems.roles`, `platform.systems.users`) + кастомные (`platform.custom.articles`, `platform.custom.rubrics`, `platform.custom.comments`). Пункты меню ограничены через `->permission(...)`.

### Экраны

| Экран | Что делает |
|---|---|
| `Screens/Article/ArticleListScreen` | Список статей (пагинация 24), модальные окна «Создать/Редактировать» на базе `CreateOrUpdateArticle`, async-загрузка статьи для редактирования, `updateOrCreate` + `sync` тегов |
| `Screens/Rubric/RubricListScreen` | Таблица рубрик, create/edit через модалки |
| `Screens/Tag/TagListScreen` | Метки: `active`, `popular` (только просмотр), create/edit модалками |
| `Screens/Comment/CommentListScreen` | Список комментариев со статусом модерации, ссылкой на статью |
| `Screens/Comment/CommentScreen` | Карточка комментария: одобрить / удалить |
| `Screens/Contact/ContactListScreen` | Сообщения обратной связи + ссылка на карточку |
| `Screens/Contact/ContactScreen` | Карточка сообщения (имя/email из гостя или из `user`) |
| `Screens/Subscriber/SubscriberListScreen` | Таблица подписчиков, ручное удаление с подтверждением |
| `Screens/User/*`, `Screens/Role/*` | Стандартные экраны Orchid |
| `Screens/Examples/*`, `PlatformScreen` | Демо-материал Orchid |

Layout'ы — `app/Orchid/Layouts/` (`CreateOrUpdateArticle`, `CreateOrUpdateRubric`, `CreateOrUpdateTag`, `ArticleListTable`, таблицы `User`/`Role`). Фильтры — `app/Orchid/Filters/RoleFilter`. Презентер — `app/Orchid/Presenters/UserPresenter`.

**Особенность async-модалок:** в `asyncGetArticle/asyncGetRubric/asyncGetTag` id сущности извлекается напрямую из query-строки (`parse_str(parse_url(...))`), т.к. при восстановлении состояния экрана он не попадает ни в `request()->query()`, ни в route-параметры.

## 10. Публичный фронтенд (Blade/Vite «TECH//LOG»)

- **Layout** `resources/views/layouts/techlog.blade.php` — шапка с навигацией (в т.ч. выпадающее меню «Темы» на клик, список рубрик внедряется `RubricsComposer`) и переключателем темы, `<main>`, футер, кнопка «наверх»; OG-разметка и SEO-мета (`metaTitle`/`metaDesc` или meta статьи).
- **Страницы**: `index.blade.php` (hero, masonry-сетка постов, newsletter-модалка), `about.blade.php`, `privacy.blade.php`, `article.blade.php` (шапка статьи, aside TOC, prose-контент, hero-image, теги, комментарии), `contact.blade.php`, `unsubscribe.blade.php`, `dashboard.blade.php`, `errors/*` (кастомные страницы ошибок), `emails/new-article.blade.php`.
- **Инклюды**: `includes/comment_modal`, `comments_form`, `comments_list`, `donate`, `locale_links`, `meta_tags`, `newsletter_modal`, `publication_date`, `sidebar` (legacy).
- **Стили**: `resources/sass/techlog/index.scss` (SCSS-модули `_variables`, `_reset`, `_base`, `_components`, `_forms`, `_utilities`, `_responsive`), CSS-переменные, тёмная темы через `html.light` + `localStorage`.
- **JS**: `resources/js/techlog.js` — мобильное меню, выпадающее меню «Темы», тема, scroll-reveal, to-top, валидация форм, модалки. `resources/js/app.js` — legacy/Alpine.
- **Legacy** (не трогать без необходимости): `layouts/base.blade.php`, `layouts/app.blade.php`, `layouts/guest.blade.php`, `includes/sidebar.blade.php`, `resources/sass/style.scss`, Tailwind. Используется Breeze/auth-страницами и старыми представлениями.

Дизайн-направление и план вёрстки зафиксированы в `docs/design-update-plan.md` и `docs/blade-migration-plan.md`.

## 11. Комментарии и модерация

- `CommentController@store`: гости — поля `name`, `email`, математическая капча (`MathCaptcha`, ответ в сессии, правило `MathCaptchaRule`), `active=false` (модерация); авторизованные — берутся из `Auth::user()`, `active=true`, капча не требуется.
- После отправки — редирект на `#comment<id>` (одобренные) или `#comments` с флешем (на модерации).
- Удаление — только авторизованным (`DELETE delete.{comment}`).
- Модерация в админке: экран `CommentScreen` (одобрить/удалить).

## 12. Подписка на рассылку

- `SubscriberController@store` — `SubscribeRequest` (email). Защита от гонок: `lockForUpdate` в транзакции по уникальному email. Повторная подписка после отписки — реактивация и новый токен.
- `unsubscribe/{token}` — одноразовая ссылка: активный подписчик переводится в `unsubscribed`, токен обнуляется.
- Ответ JSON (для модалки newsletter) или редирект с flash-сообщением.
- Сбор подписчиков/рассылка новых статей — см. раздел «Наблюдатели».

## 13. Локализация и конфигурация

- **Языки**: `lang/ru`, `lang/en`, `lang/ru.json`. Переключение — `GET /setlocale/{locale}` (`MainController`), локаль хранится в сессии `user_locale`, применяется middleware `Localize`.
- **Конфиг сайта**: `config/my_config.php` (`MY_GITHUB`, `MY_EMAIL`, `MY_TELEGRAM`, `CONTACT_EMAIL`, `SLOGAN`, `SUB_LOGO`) — выводятся в шаблонах через `config('my_config.*')`.

## 14. Безопасность

- **ЩП/роли**: доступ к админке и кастомные права через Orchid permissions (`PlatformProvider::permissions()`); `Article::checkAccess()` и `access`-middleware на `/notpublic`.
- **CSRF** — стандартный `VerifyCsrfToken`; формы используют `@csrf`.
- **Rate limiting** — `throttle:5,1` на подписку, `throttle:10,1` на комментарии.
- **Капча** — математическая для гостевых комментариев. Middleware `GoogleRecaptcha` существует, но **не подключён** ни к одному маршруту (при включении формы должны отправлять поле `r`, иначе 403).
- **SQL-инъекции** — построение запросов через query builder/параметры; поиск по `content_html` через `LIKE` с привязкой.
- **XSS** — пользовательский контент: `content_html` выводится через `{!! !!}` (при генерации из markdown), остальной текст — через `e()`/`{{ }}` (см. контроллеры/шаблоны; в таблицах Orchid используется `Str::limit(e(...))`).

## 15. Команды и инструменты

- `Makefile` — каноничный dev-workflow (**только Docker**): `make install|up|down|logs|shell|migrate|orchid-admin|storage-link|frontend-build|frontend-dev|test|lint|clear|optimize` и т.д. Локальные php/composer/npm на хосте не используются — все команды выполняются внутри контейнеров `docker/docker-compose.yml`.
- `make test` = `php artisan test` внутри `blog_app` (сейчас только boilerplate `ExampleTest`); о новых тестах — `docs/testing-plan.md`.
- `make lint` = `php -l` (без phpstan/Pint) внутри `blog_app`.
- `make frontend-build` / `make frontend-dev` = `npm run build` / Vite dev server внутри `blog_node`.
- Доступ: сайт `:8080`, админка `:8080/admin`, phpMyAdmin `:8899`, MailHog `:8026`, Vite dev `:5173`; БД `laraorchid`/`root`/`root`.
- CI отсутствует.

## 16. Известные ограничения и заметки

1. Колонка `excert` (опечатка вместо «excerpt») — сохранена во всех слоях (`Article`, `ArticleService`, шаблоны). Не «чинить».
2. `AppServiceProvider` биндит несуществующий `App\Services\ServiceInterface`; в `EventServiceProvider` и `ArticleListScreen` есть мёртвые `use App\Events\ArticleCreated` — класс события не создан, событийная модель не используется (см. `AGENTS.md`). Это legacy-хвосты.
3. Механизм кэша сайдбара (`CacheService`, ключи `Tag::SIDEBAR_CACHE_KEY`, `Rubric::SIDEBAR_CACHE_KEY`) создан, но в новом шаблоне `techlog` не задействован; инвалидация в наблюдателях закомментирована.
4. `mews/captcha` и `GoogleRecaptcha` — заготовки; реальная капча — самописная математическая.
5. Планировщик рассылки/публикации требует запущенного `schedule:run` (или Laravel cron) — без него запланированные статьи публиковаться не будут.