# План рефакторинга проекта

Составлен 2026-09-21. Приоритет: **Фаза 1** (бэкенд: чистка мёртвого кода + унификация выборок + удаление неиспользуемых пакетов).

Аудит выполнен по состоянию проекта: все «мёртвые» сущности перепроверены поиском по `app/`, `routes/`, `resources/`, `config/`.

---

## Фаза 1. Бэкенд: мёртвый код, битые ссылки, пакеты

### 1.1 Провайдеры и загрузка приложения
- `app/Providers/AppServiceProvider.php` — удалить `bind(ServiceInterface::class, ArticleService::class)` и импорт `App\Services\ServiceInterface` (класса не существует). Blade-директиву `@hasAccess` оставить.
- `app/Providers/EventServiceProvider.php` — удалить мёртвые импорты `App\Events\ArticleCreated`, `App\Listeners\ClearSidebarCache` (классов не существует).
- `app/Providers/BroadcastServiceProvider.php` — удалить файл (не зарегистрирован); заодно `routes/channels.php`, `config/broadcasting.php` (не используются).

### 1.2 CacheService и кэш сайдбара (мёртвые)
- Удалить `app/Services/CacheService.php`.
- Константы `Rubric::SIDEBAR_CACHE_KEY`, `Tag::SIDEBAR_CACHE_KEY` — удалить.
- Удалить классы `app/Observers/RubricObserver.php`, `app/Observers/TagObserver.php` (тела пустые/закомментированы) и вызовы `Rubric::observe(...)`, `Tag::observe(...)` в `EventServiceProvider@boot`.
- `app/Observers/ArticleObserver.php` — вычистить закомментированные `// ModelCache::updateCache(...)` во всех 5 методах, мёртвые `// use App\Classes\ModelCache;` и `use App\Models\Rubric;`.
- `app/Http/Controllers/ArticleController.php` (строки 9, 23) — снять инъекцию `CacheService`.

### 1.3 Унификация выборок статей → ArticleService
Прод-запросы дублируются инлайн в контроллере (4 места, пагинация 6), а `ArticleService` держит параллельные неиспользуемые методы (пагинация 12, другие колонки).

- В `ArticleService` перенести реальные выборки как единый источник:
  `getPublic(?search)`, `getByRubric`, `getByTag`, `getNotPublic` — с заголовками/колонками из контроллера (`id,title,slug,excert,image,published_at,rubric_id,is_published`, eager `user/rubric/tags`, пагинация 6).
- `ArticleController` — сжать методы до вызова сервиса, удалить константу `PAGINATE = 6` и инлайн-запросы.
- Обновить `tests/Unit/ArticleServiceTest.php` под новые сигнатуры.

### 1.4 Мёртвый API моделей (используется только тестами)
- `Article::recents()`, `Article::LENGTH_DATE` — удалить; обновить `tests/Unit/ArticleModelTest.php` (test `recents`).
- `Rubric::articlePublished()`, `Tag::articlePublished()` — удалить (в проде использовались только удаляемым `CacheService`; секция «Темы» на главной — хардкод HTML). Обновить `tests/Unit/RubricModelTest.php`, `tests/Unit/TagModelTest.php`.
- `User::saveContact()`, константы `ROLE_*` — удалить (не используются).

### 1.5 Ссылки на несуществующие классы / мёртвые импорты в Orchid
- `ArticleListScreen` — удалить импорты `App\Events\ArticleCreated`, `App\Models\Tag`, `Illuminate\Support\Facades\Event`, `Orchid\Screen\Actions\Link`.
- `CommentScreen` — `App\Models\Article`, `Orchid\Screen\Fields\Relation`.
- `ContactListScreen` — мёртвый импорт `App\Orchid\Layouts\ShowContact`; пройтись по остальным (`User`, `ModalToggle`, `Modal`, `CheckBox`, `Sight`, `Button`, `Color`) и оставить только используемые.
- `app/Http/Requests/Auth/RegisterRequest.php` — удалить (нигде не используется; включает правило mews-капчи).

### 1.6 Middleware и хелперы
- `app/Http/Middleware/GoogleRecaptcha.php` — удалить (ни к одному маршруту не подключён).
- `app/helpers.php` — удалить функции `active_link()`, `alert()` (не используются в коде и шаблонах); при пустом файле — убрать `autoload-dev.files` из `composer.json`.

### 1.7 Конфиги и пакеты
- Удалить `config/captcha.php` (mews) и `config/debugbar.php` (пакета нет в `composer.lock`).
- `config/app.php` — убрать `Mews\Captcha\CaptchaServiceProvider` (стр. 188) и алиас `'Captcha'` (стр. 215).
- `composer remove mews/captcha laravel/sail` (в контейнере через `make composer`).

### 1.8 Бат-фикс заодно
- `app/Models/Comment.php` — `$dates` содержит опечатку `'delete_at'` → `'deleted_at'` (влияет на soft deletes).
- Почистить крупные закомментированные блоки: `Article.php`, `Layouts/CreateOrUpdateArticle.php`, лишние комментарии в экранах.

---

## Фаза 2. Фронтенд: удаление legacy и мёртвых шаблонов

- Шаблоны (не вызываются ни одним контроллером/роутом):
  `layouts/base.blade.php`, `welcome.blade.php`, `vendor/pagination/default.blade.php`,
  `includes/{sidebar,meta_tags,donate,publication_date,locale_links}.blade.php`.
- SCSS-цепочка legacy: `resources/sass/style.scss`, `_variables.scss`, `_tokens.scss`, `_base.scss`, `_layout.scss`, `_components.scss`, `_dark.scss` (пустой стаб), `resources/css/normalize.css`.
- `vite.config.js` — убрать entry `resources/sass/style.scss` (останется 5 точек; пересобрать `public/build/manifest.json` через `make frontend-build`).
- Артефакты: `public/config.rb`, `public/fonts/` (FontAwesome/glyphicons/GreatVibes в живых шаблонах не используются).
- **Сохранить живое:** `layouts/{app,guest,navigation}`, `components/*` (Breeze), `dashboard`, `profile/*`, `auth/*`, techlog-поверхность и `resources/js/{app,techlog,cookie-banner}.js`.

---

## Фаза 3. Тесты и проверка

- Обновить/удалить тесты, покрывавшие удалённый код (`ArticleModelTest.recents`, `RubricModelTest`/`TagModelTest.articlePublished`, `ArticleServiceTest` под новые методы).
- Прогнать `make test` и `make lint` (все зелёные).
- Контрольная проверка grep: в `app/ routes/ resources/ config/` не должно остаться ссылок на удалённое:
  `ServiceInterface`, `ArticleCreated`, `ClearSidebarCache`, `ModelCache`, `ShowContact`, `CacheService`, `SIDEBAR_CACHE_KEY`, `articlePublished`, `GoogleRecaptcha`, `layouts.base`, `mews`.

---

## Фаза 4. Документация

- `AGENTS.md` — устарело: «тесты только ExampleTest» (их больше, они реальные), упоминания кэша-наблюдателей/`CacheService`, Gotcha про капчу и middleware.
- `docs/architecture.md` — переписать разделы 7 (сервисы: удалён `CacheService`), 8 (наблюдатели: только `ArticleObserver`), 10 (legacy удалён), 14 (безопасность: нет `GoogleRecaptcha`), 16 (известные ограничения: убрать выполненные пункты 2, 3; пункт про mews — закрыть).
- `__BACKLOG.md` — отметить «рефакторинг» выполненным; `_ISSUES.md` — закрытые пункты.

---

## Поздние фазы (опционально, вне текущего скоупа)

- **Orchid-админка:** дедупликация статус-бейджей/формата дат в экранах, FormRequests в `RubricListScreen`/`TagListScreen`, починить `createOrUpdateRubric` (не сохраняет slug/description), избавиться от `parse_str(parse_url(...))` в async-модалках.
- **CI:** GitHub Actions на базе `make test`/`make lint`.
- **Прочее из `_ISSUES.md`:** подстановка «TECH//LOG» → `APP_NAME`, SEO-оптимизация.

---

## Замечания / инварианты

- «excert» — опечатка, сохраняется намеренно во всех слоях (модель, сервис, шаблоны). Не чинить.
- Тестовые маршруты `/test-4xx` и `errors/*` — «не удалять», остаются.
- Паттерн «без Events/Listeners», наблюдатели как способ побочных эффектов — сохранить.