## Context

Мотивация — в `proposal.md` (Why). Текущее состояние системы:

- Laravel 13 + Orchid-админка + Breeze; публичный фронт — Blade + SCSS (`resources/views/layouts/base.blade.php`, `index.blade.php`, `article.blade.php`).
- JSON API отсутствует (`routes/api.php` нет), Sanctum установлен, но не задействован для публичной части.
- `Article` хранит `content_html` (Markdown → HTML генерируется на бэке через CommonMark), `image` (строка), `slug`, SEO-поля; маршруты текущего сайта строятся по `id`.
- `ArticleService` инкапсулирует выборку опубликованных статей (`is_published`, `published_at <= now`, сортировка по `published_at` desc, пагинация 12); `CacheService` кэширует рубрики/теги.
- Локализация отключена — только русский.

## Goals / Non-Goals

**Goals:**
- Чёткое разделение: Laravel — JSON API + Orchid-админка; Nuxt 4 SSR в `frontend/` — публичный сайт.
- SSR для SEO; mobile-first адаптивность; светлая/тёмная тема Vuetify 3 с сохранением выбора.
- Минимум зависимостей: `nuxt`, `vuetify`, `vuetify-nuxt-module`; без `@nuxt/content` (контент — готовый HTML из API), без сторонних UI-библиотек.
- Минимальный риск отката: Blade-вьюхи публичной части не удаляются и продолжают работать.

**Non-Goals:**
- Перенос auth (Breeze) и комментариев в Nuxt — остаются на Blade (отдельный этап).
- Удаление Blade-вьюх и SCSS — вне этапа.
- Изменение админки Orchid.
- Мультиязычность (i18n) — только русский.

## Decisions

### D1: Монорепо + Nuxt 4 SSR в `frontend/`
Nuxt живёт в папке `frontend/` репозитория, режим `ssr: true`. Альтернативы: SPA-клиент (слабее SEO — не подходит для блога), отдельный репозиторий (усложняет совместную работу над одним проектом). SSR-режим использует структуру Nuxt 4: исходники в `app/` (pages, components, layouts, composables, plugins, assets).

### D2: Laravel JSON API поверх существующих сервисов
Новые маршруты в `routes/api.php` переиспользуют `ArticleService` и модели:

| Эндпоинт | Поведение |
|---|---|
| `GET /api/articles` | Пагинация 12; query: `page`, `search`, `rubric`, `tag`; только опубликованные |
| `GET /api/articles/{article}` | Полная статья по `id`; 404 для неопубликованной/отсутствующей |
| `GET /api/rubrics` | Список рубрик (кэш `CacheService`) |
| `GET /api/tags` | Список тегов (кэш `CacheService`) |

Селекторы — как в текущем `ArticleService` (+ `excerpt`, `image`, `slug` для карточек). Формат ответа: `snake_case`, даты ISO 8601, мета-пагинации `current_page/last_page/per_page/total`. Альтернатива (JSON:API/спецификация) избыточна для блога. Статьи не кэшируются (свежесть данных важнее), рубрики/теги — кэшируются как сейчас.

### D3: Vuetify 3 через `vuetify-nuxt-module`
Модуль даёт tree-shaking, SSR-совместимость и автоматическую настройку. Темы `light`/`dark` с акцентным цветом задаются в `app/plugins/vuetify.ts` (альтернатива — ручной `vite-plugin-vuetify`, больше boilerplate). Переключатель темы — `useTheme()` + сохранение в `localStorage`; против «вспышки» — inline-скрипт в `head` (`app.vue` / `nuxt.config.ts app.head`), устанавливающий тему до гидрации.

### D4: Контент статей — `v-html` без markdown-парсера
API отдаёт готовый `content_html`; фронт рендерит его через `v-html`. Это исключает `@nuxt/content` и клиентский markdown. Типичные md-выходные теги (`p`, `h2–h4`, `pre/code`, `blockquote`, `table`, `a`) стилизуются в `app/assets/css/main.css` с учётом обеих тем (CSS-переменные Vuetify). XSS-риск низкий, т.к. HTML генерируется доверенным CommonMark-конвертером на бэке.

### D5: Изображения — прямые URL без `@nuxt/image`
`image` — путь в `storage/`; фронт склеивает его с `runtimeConfig.public.mediaBase` (Laravel-хост). Ленивая загрузка — нативный `loading="lazy"` (альтернатива `@nuxt/image` добавила бы зависимость и генерацию оптимизированных версий — отложено). Карточка без изображения рендерится с placeholder-блоком.

### D6: Прокси и CORS
В dev — nitro `devProxy`: `/api` → `http://127.0.0.1:8000/api` и `/storage` → `http://127.0.0.1:8000/storage`. Дополнительно `config/cors.php` разрешает origin `http://localhost:3000` (для прямых запросов и prod-сценариев). В prod — reverse proxy nginx (`docker/nginx/conf.d/nginx.conf`) для `/api` и `/storage`.

### D7: SEO-слой
`app.head` в `nuxt.config.ts`: `lang=ru`, titleTemplate, дефолтный description. `useHead()` на страницах: главная — «Блог: последние статьи»; статья — `<title>` из заголовка, `meta description` из `meta_desc`, Open Graph (`og:type=article`); страницы поиска/рубрик/тегов — свои заголовки; `?search=` — `noindex, nofollow` (как текущее `META_NO_ROBOTS`). Единственный `h1` на странице. Данные для мета подтягиваются в `useAsyncData`/`useFetch` (SSR).

### D8: Данные на фронте
`composables/useBlogApi.ts` — обёртки над `$fetch` с `apiBase`; типы `Article`, `Rubric`, `Tag`, `Paginated<T>` в `app/types/`. SSR-выборка через `useAsyncData` (избегает дублирования запросов между сервером и клиентом).

### D9: Контактная форма
Форма на `/contact` отправляет данные на новый публичный API-маршрут `POST /api/contact` (без CSRF, без auth), валидация через существующий `ContactRequest`; обработка — как в `ContactController@store`. Сообщение об успехе/ошибке — в UI (v-alert). reCAPTCHA-мидлварь не подключаем в этом этапе (вне scope), при необходимости добавится позже.

## Risks / Trade-offs

- [«Вспышка» темы при загрузке] → inline-скрипт в `head`, читающий `localStorage` до рендера.
- [CORS/прокси в prod] → единая конфигурация nginx для `/api` и `/storage`; в dev — nitro `devProxy`.
- [`v-html` с чужим HTML] → контент генерируется доверенным бэкенд-конвертером; санитизация (HTMLPurifier) — потенциальное улучшение вне скоупа.
- [Изображения в `storage/`] → требуется `php artisan storage:link`; в prod проксировать `/storage`; абсолютный `mediaBase` в конфиге.
- [Два dev-сервера (Vite/Breeze + Nuxt)] → документируем запуск; Blade-часть не мешает публичному фронту.
- [SEO-дубли во время переходного периода (Blade + Nuxt доступны одновременно)] → при переключении на Nuxt пометить Blade-версии `noindex` (задача вне этапа).
- [Устаревшие данные рубрик/тегов в кэше] → наследуемый `CacheService` с инвалидацией при изменениях через Orchid.

## Migration Plan

1. **API-слой Laravel**: `routes/api.php`, `Http/Resources/*`, `config/cors.php` — аддитивно, Blade продолжает работать без изменений.
2. **Scaffold Nuxt**: `frontend/` (package.json, nuxt.config.ts, vuetify-плагин, layout, header/footer, переключатель темы).
3. **Компоненты и страницы**: `ArticleCard`, `ArticleLayout`, `BlogSidebar`, `PaginationBar`; страницы index/article/rubric/tag/about/contact.
4. **Проверка**: SSR-ответы (curl), meta/OG, адаптивность и темы (браузер), Lighthouse, производительность.
5. **Rollback**: Blade-вьюхи и старые маршруты остаются; переключение домена/роутинга назад без потери данных.

## Open Questions

- Контактная форма: точные требования к reCAPTCHA/антиспаму (решается при реализации API без изменения спеки).
- Slug-маршруты для статей (`/articles/{slug}` вместо `{id}`): SEO-улучшение, но меняет контракт API — вынесено в отдельное решение после этапа.
