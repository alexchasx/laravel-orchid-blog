# AGENTS.md

Универсальный шаблон для блога. Бэкенд — **Laravel 13 (PHP ^8.5)** с админ-панелью **Orchid Platform 14** на `/admin` (`app/Orchid/`). Публичный фронтенд один — **Blade/Vite** (`resources/views/`, layout `layouts.techlog`). Аутентификация (Breeze) и комментарии — только Blade. При добавлении публичной фичи решай, к какой поверхности она относится: Blade-представление, админ-панель или обе.

Ключевые функции блога: лента статей по рубрикам/тегам с поиском и оглавлением, гостевые комментарии с математической капчей, форма обратной связи, подписка на новые статьи с рассылкой по email, автопубликация запланированных статей.

Архитектура приложения задокументирована в [`docs/architecture.md`](docs/architecture.md); список работающих «из коробки» возможностей — в [`README.md`](README.md) (раздел «✨ Возможности "из коробки"»); план подготовки публичного шаблона — [`docs/public-template-plan.md`](docs/public-template-plan.md) (рабочий документ, удаляется после завершения секций 6–12).

## Статус репозитория

Репозиторий — **публичный GitHub-шаблон** (`Use this template`), а не боевой проект. Правила шаблона:

- Никаких личных данных, ключей и паролей в репозитории: `.env.example` содержит только плейсхолдеры, `APP_KEY`/`DB_*` пустые, lock-файлы (`composer.lock`, `package-lock.json`) не коммитятся.
- Миграции только создают структуру; стартовое наполнение — в `database/seeders/`.
- Фичи, заявленные в README как работающие «из коробки», должны реально работать; если меняешь поведение — обнови README и этот файл.
- Держи код, конфиги и документацию переносимыми: без привязки к конкретному сайту, домену, почте или соцсетям — всё через `config/my_config.php` и `.env`.

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

Дефолтные адреса: сайт `:8080`, админ `:8080/admin`, phpMyAdmin `:8899`, MailHog `:8026`, Vite dev `:5173`; MySQL — внутри сети `3306`, наружу `:8101`. Контейнер `db` создаёт БД `laraorchid`/`root`/`root` (`docker/docker-compose.yml`); в `.env.example` значения БД пустые — их заполняет разработчик при настройке проекта.

## Тесты

Тесты есть и реальные (PHPUnit, база `testing` по `phpunit.xml`):
- `tests/Feature/` — `PublicPagesTest`, `UnpublishedArticlesPageTest`, `CommentTest`, `CommentScreenTest`, `ContactPageTest`, `SubscriberTest`, `ProfileTest`, `ConsentTest`, а также `Auth/*` от Breeze;
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
- **Загрузка изображений к статьям не подключена:** колонка `articles.image` есть и в `fillable`, и в `Article`, но в `CreateOrUpdateArticle` нет поля `Picture`/`Upload`, а в `article.blade.php` стоит заглушка-градиент. README упоминает это как «чего пока нет» — при включении фичи поправь README.
- **Согласия на ПДн (152-ФЗ):** форма комментария содержит два обязательных чекбокса (`consent_processing`, `consent_distribution`); кнопка отправки заблокирована (`disabled`), пока не отмечены оба. Каждый факт согласия фиксируется в `consent_logs` (IP, UA, URL, дословный текст, версия). Тексты формируются в `ConsentTextBuilder` из `config/operator.php` + `config/consent.php` (без Blade-литералов в heredoc — только переменные PHP). Отзыв на распространение → обезличивание комментария (`is_anonymized = true`, `name = 'Аноним'`); на обработку → forceDelete. Сроки: прекращение распространения — 3 рабочих дня (`config('consent.distribution.stop_days')`, ч. 4 ст. 9 № 152-ФЗ), удаление/обезличивание — 7 рабочих дней (`config('consent.revocation_days')`). При отзыве на обработку комментарий удаляется (`forceDelete`), а лог сохраняется: `consent_logs.comment_id` nullable (`nullOnDelete`) → обнуляется, `revoked_at` фиксирует факт отзыва. Просроченные отзывы догоняет команда `consents:process-revocations` (daily, контейнер `blog_schedule`). Журнал просматривается через phpMyAdmin (экрана Orchid нет). Маршруты `/consent/*` доступны гостям.

## Выгрузка контекста перед завершением сессии (для код-агента)

При превышении 45% заполненности контекста:

1. Прекрати выполнение текущей задачи.
2. Создай файл `context_handoff.md` в рабочей директории со следующей структурой:
   - **Задача**: краткое описание исходной задачи.
   - **Выполнено**: что уже сделано в этой сессии.
   - **Осталось**: ближайшие шаги для завершения задачи.
   - **Решения и ограничения**: ключевые выборы, что не сработало, договорённости с пользователем.
   - **Файлы**: созданные/изменённые файлы, пути и назначение.
   - **Контекст кода**: ключевые переменные, структуры, архитектурные решения.
3. Сообщи пользователю: «Контекст сессии заполнен более чем на 45%.
   Состояние выгружено в context_handoff.md. Рекомендую начать новую сессию
   и указать этот файл для продолжения работы.»
4. Больше ничего не делай в текущем ходе.

## Восстановление контекста

Если в рабочей директории найден файл `context_handoff.md` — прочитай его
первым делом и продолжи работу с того места, где остановилась предыдущая сессия.
После восстановления удали файл, чтобы избежать путаницы.

## Управление контекстом (для код-агента)

Перед каждым ответом и перед каждым вызовом инструмента оценивай процент
заполненности контекста сессии.

При превышении 45%:

1. Прекрати выполнение текущей задачи.
2. Не запускай новые инструменты и не порождай новый код.
3. Создай файл `context_handoff.md` в рабочей директории со следующей структурой:
   - **Задача**: краткое описание исходной задачи.
   - **Выполнено**: что уже сделано в этой сессии.
   - **Осталось**: ближайшие шаги для завершения задачи.
   - **Решения и ограничения**: ключевые выборы, что не сработало, договорённости с пользователем.
   - **Файлы**: созданные/изменённые файлы, пути и назначение.
   - **Контекст кода**: ключевые переменные, структуры, архитектурные решения.
4. Сообщи пользователю: «Контекст сессии заполнен более чем на 45%.
   Состояние выгружено в context_handoff.md. Рекомендую начать новую сессию
   и указать этот файл для продолжения работы.»
5. Больше ничего не делай в текущем ходе.

### Требования к файлу handoff

- Файл `context_handoff.md` должен быть кратким — не более 50 строк
  и не более 1000 токенов. Цель — передать суть, а не скопировать контекст.
- Имя файла строго `context_handoff.md`, не генерируй новые имена.
- Если работаешь над несколькими задачами параллельно, именуй файлы
  как `context_handoff_<task_id>.md`, где `<task_id>` — идентификатор задачи.

## Восстановление контекста

Если в рабочей директории найден файл `context_handoff.md` — прочитай его
первым делом и продолжи работу с того места, где остановилась предыдущая сессия.
После восстановления удали файл, чтобы избежать путаницы.

