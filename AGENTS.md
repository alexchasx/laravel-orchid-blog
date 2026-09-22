# AGENTS.md

Русскоязычный IT-блог **«TECH//LOG»**. Бэкенд — **Laravel 13 (PHP ^8.5)** с админ-панелью **Orchid Platform 14** на `/admin` (`app/Orchid/`). Публичный фронтенд один — **Blade/Vite** (`resources/views/`, layout `layouts.techlog`). Аутентификация (Breeze) и комментарии — только Blade. При добавлении публичной фичи решай, к какой поверхности она относится: Blade-представление, админ-панель или обе.

Ключевые функции блога: лента статей по рубрикам/тегам с поиском и оглавлением, гостевые комментарии с математической капчей, форма обратной связи, подписка на новые статьи с рассылкой по email, автопубликация запланированных статей.

Дизайн и рефакторинг задокументированы в `docs/`: [`design-update-plan.md`](docs/design-update-plan.md), [`architecture.md`](docs/architecture.md), [`refactoring-plan.md`](docs/refactoring-plan.md), [`blade-migration-plan.md`](docs/blade-migration-plan.md), [`guest-comments-plan.md`](docs/guest-comments-plan.md), [`testing-plan.md`](docs/testing-plan.md), [`code-audit-report.md`](docs/code-audit-report.md), [`design-audit-inventory.md`](docs/design-audit-inventory.md).

## Язык

UI-тексты, комментарии в коде и вся документация — на **русском**; новые пиши на русском.

## Команды

Проект работает **только через Docker** — локальные php/composer/npm/artisan не используются. `make` гоняет все команды через Docker Compose из **`docker/docker-compose.yml`** (не из корня репо); `make help` показывает весь список:

- `make install` — первичная установка одним махом: `.env` (из `.env.example`), сборка + запуск контейнеров, затем `make setup`
- `make setup` — полная настройка в уже запущенном Docker: composer/npm install, `key:generate`, **`migrate:fresh --seed`**, админ Orchid (`admin@localhost.ru`/`123456`), `storage:link`, сборка фронтенда
- `make up` / `make down` / `make logs` / `make shell` — жизненный цикл контейнеров; `shell` входит в контейнер `app` (bash)
- `make storage-perm` — починить права `www-data` на `storage/` и `bootstrap/cache`
- `make composer` / `make php` — Composer и PHP REPL внутри контейнера `app`
- `make composer-install` / `make key-generate` / `make storage-link` — отдельные шаги установки
- `make migrate` — **`migrate:fresh --seed`** (разрушает данные!)
- `make orchid-admin` — создание администратора Orchid (`admin@localhost.ru` / `123456`, не интерактивно)
- `make frontend-build` / `make frontend-dev` — `npm run build` / Vite dev-сервер **внутри `blog_node`**
- `make optimize` / `make clear` — кэш конфигов/роутов/представлений и его очистка
- `make test` — `php artisan test` **внутри `blog_app`**; `make test-coverage` — c HTML-отчётом в `tests/coverage`
- `make lint` — только `php -l` по `app database routes` (нет phpstan и Pint)
- `make ide-helper` — `ide:model` + `ide:optimize` для автодополнения в IDE

Контейнеры (`blog_*`): `nginx`, `app`, `node`, **`schedule`** (`php artisan schedule:work` — автопубликация статей), **`queue`** (`php artisan queue:work` — обработка очереди писем рассылки), `mailhog`, `db` (MySQL 8.0), `phpmyadmin`.

Дефолтные адреса: сайт `:8080`, админ `:8080/admin`, phpMyAdmin `:8899`, MailHog `:8026`, Vite dev `:5173`; MySQL — внутри сети `3306`, наружу `:8101`; БД `laraorchid`/`root`/`root` (совпадает с `docker/docker-compose.yml` и `.env.example`).

## Тесты

Тесты есть и реальные (PHPUnit, база `testing` по `phpunit.xml`):
- `tests/Feature/` — `PublicPagesTest`, `UnpublishedArticlesPageTest`, `CommentTest`, `CommentScreenTest`, `ContactPageTest`, `SubscriberTest`, `ProfileTest`, а также `Auth/*` от Breeze;
- `tests/Unit/` — `ArticleModelTest`, `ArticleServiceTest`, `CommentModelTest`, `RubricModelTest`, `TagModelTest`.

При изменении доменной логики обновляй или добавляй тесты (`make test`). CI отсутствует.

## Структура приложения

- `app/Models/` — `Article`, `Rubric`, `Tag`, `ArticleTag`, `Comment`, `Contact`, `Subscriber`, `User`.
- `app/Observers/` — единственный обсервер: `ArticleObserver`.
- `app/Services/ArticleService.php` — выборки статей и построение оглавления (`withToc()`).
- `app/Support/MathCaptcha.php` + `app/Rules/MathCaptchaRule.php` — математическая капча (ответ в сессии).
- `app/Mail/NewArticleMail.php` — письмо подписчикам о новой статье.
- `app/Console/Commands/PublishScheduledArticles.php` — `articles:publish-scheduled`.
- `app/Http/Requests/` — `ArticleRequest`, `CommentRequest`, `ContactRequest`, `ProfileUpdateRequest`, `SubscribeRequest`.
- `app/Orchid/` — экраны `Article`, `Comment`, `Contact`, `Rubric`, `Subscriber`, `Tag`, `User`, `Role` (+ шаблонные `Examples`); пермишены `platform.custom.articles`, `platform.custom.rubrics`, `platform.custom.comments` и системные Orchid.
- Роуты: `routes/web.php` + `routes/auth.php`; расписание — `routes/console.php`.

## Gotchas

- **Колонка БД называется `excert` (опечатка, сохранена).** На неё ссылаются `Article` model и `ArticleService` (список `SELECT_COLUMNS`). Не «чинить» без миграции данных.
- **Обсерверов ровно один — `ArticleObserver`** (события created/updated/deleted/restored/forceDeleted). Его задачи: пересчёт `Tag::updateCountArticles()` (поле `count_articles`) и **рассылка подписчикам** (`NewArticleMail`) для опубликованных статей с наступившей датой выхода. Никаких кэш-ключей и инвалидации кэша нет; паттерн Events/Listeners не используется — держись обсерверов.
- `Article::published()` = `is_published === true` && `published_at <= now` (сравнение **по полному timestamp**, а не по дате — запланированная на сегодня «будущая» статья не показывается). `ArticleService::checkAccess()` отдаёт 403, если статья не опубликована, а текущий пользователь не админ. Отдельная страница «неопубликованные» — роут `notpublic` под `auth` + `access:platform.custom.articles`.
- `Article::booted()`: на `saving` конвертирует markdown `content_raw` → `content_html` через `league/commonmark` (санитизация: `html_input => strip`, `allow_unsafe_links => false`) и авто-генерирует уникальный `slug` из заголовка.
- **Капча — математическая** (`MathCaptcha`, ключ сессии `captcha_answer`), применяется для гостевых комментариев в `CommentRequest`. Middleware `GoogleRecaptcha` в проекте больше нет.
- **Комментарии**: гостевые разрешены, при этом сохраняется IP (миграция `add_ip_to_comments`); `comment.create` под `throttle:10,1`, удаление — авторизованным пользователям (`commentDelete`).
- **Подписка на статьи**: `subscribe.store` под `throttle:5,1`; отписка по токену `unsubscribe/{token}`; активные подписчики — scope `Subscriber::active()`.
- **Автопубликация**: команда `articles:publish-scheduled` каждую минуту (`routes/console.php`), исполняет контейнер `blog_schedule`; сохранение статьи триггерит `ArticleObserver::updated` → рассылку.
- **Очередь рассылки**: `QUEUE_CONNECTION=database` (таблица `jobs`), письма `NewArticleMail` (Mailable `implements ShouldQueue`) обрабатывает контейнер `blog_queue`. В тестах (`phpunit.xml`) очередь форсируется в `sync`.
- Laravel 13-style layout: роутинг/миддлвары регистрируются в `bootstrap/app.php` (нет `Http/Kernel.php`). Кастомные `TrustProxies` и `VerifyCsrfToken` заменяют дефолтные; `Localize` добавлен в группу `web`; алиас `access` → Orchid Access.
- Env-настройки сайта — `config/my_config.php` (`MY_GITHUB`, `MY_TELEGRAM`, `CONTACT_EMAIL`, `SUB_LOGO`, `SLOGAN`).
