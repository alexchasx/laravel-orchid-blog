## Context

Фронтенд в гибридном состоянии: Breeze-часть (`app`/`guest`) уже переведена на Vite+Tailwind+Alpine, а публичный блог через [`base.blade.php`](../../resources/views/layouts/base.blade.php) подключён к ассетам Mix (`asset('css/style.css')`, `asset('js/app.js')`). `webpack.mix.js` собирает `resources/sass/*.scss` и `resources/js/app.js` в `public/js` и `public/css`; `public/mix-manifest.json` устарел.

Легаси-код Vue 2 (`resources/js/vue.js`, `resources/js/components/**`), CommonJS (`bootstrap.js`), осиротевший Bootstrap SASS (`app.scss`) и шаблоны `auth_OLD/**` не используются и не собираются ни в одном бандлере.

Мотивация и требования — в `proposal.md` и `specs/frontend-build/spec.md`.

**Scope:** перевод публичного блога на Vite и удаление Mix/легаси-кода. Orchid-админка, бизнес-логика контроллеров/моделей не затрагиваются.

## Goals / Non-Goals

**Goals:**
- Сделать Vite единственным инструментом сборки фронтенда.
- Перевести все layouts на подключение ассетов через `@vite(...)` (включая `base.blade.php` и, следовательно, весь публичный блог).
- Собирать кастомные стили блога из `resources/sass/style.scss` через Vite.
- Удалить все артефакты Mix (`webpack.mix.js`, `public/mix-manifest.json`, Mix-выход `public/js`, `public/css`) и легаси-код (Vue 2, CommonJS, осиротевший SASS, `auth_OLD`).
- Сохранить работоспособность комментариев на серверном рендере Blade (без Vue).

**Non-Goals:**
- Не мигрировать логику комментариев на Alpine/Vue — оставить серверный рендер.
- Не менять дизайн и стили публичной части (только способ сборки).
- Не трогать Orchid-админку (`public/vendor/orchid/*`) и её сборку.
- Не проводить рефакторинг моделей/контроллеров.

## Decisions

**D1: Vite — единый инструмент сборки.**
Mix полностью выводится из эксплуатации. Все стили и скрипты собираются через `vite.config.js` и подключаются `@vite(...)`.

**D2: Три независимые входные точки.**
- `resources/css/app.css` (Tailwind) — для Breeze (`app`/`guest`);
- `resources/sass/style.scss` (кастом) — для публичного блога (`base`);
- `resources/js/app.js` (Alpine) — общий JS.
Раздельные входы исключают конфликт Tailwind и кастомных стилей.

**D3: Миграция публичного сайта через один файл `base.blade.php`.**
Все публичные страницы наследуют `base.blade.php`, поэтому замена в нём `asset(...)` на `@vite(['resources/sass/style.scss', 'resources/js/app.js'])` мигрирует весь блог (index/article/contact/errors) за одну правку.

**D4: Отказ от Vue 2.**
Компоненты комментариев не собираются и не используются; комментарии рендерятся серверно. Vue 2 (`vue.js`, `components/**`) и зависимости jQuery/Bootstrap (из `bootstrap.js`) удаляются. Это снимает потребность в `@vitejs/plugin-vue2`.

**D5: SCSS через нативный плагин Vite.**
Добавляется devDependency `sass`. Импорты в `style.scss` относительные (`../css/normalize.css`, `variables`) — обрабатываются без алиасов. Префикс `~` (webpack-специфичный) исчезает вместе с удалением `app.scss`.

**D6: Чистка артефактов.**
Удаляются `webpack.mix.js`, `public/mix-manifest.json`, старый Mix-выход `public/js/*`, `public/css/*`, осиротевший `resources/sass/app.scss`, неиспользуемые `resources/views/auth_OLD/**`. `resources/sass/_variables.scss` и `resources/css/normalize.css` сохраняются (используются `style.scss`).

## Risks / Trade-offs

- [Классические браузеры не получат SCSS, если `sass` не установлен] → Добавить `sass` в `devDependencies` до первого `npm run build`.
- [Tailwind (app.css) и кастом (style.scss) в одном документе] → Разнесены по разным layouts/входам; на страницах блога Tailwind не подключается.
- [Случайная ссылка на удалённые `public/css/*`, `public/js/*` или `mix()`] → Финальный grep по `asset('css/`, `asset('js/`, `mix(`, удалённым путям.
- [Различия хеширования имён между Mix и Vite могут обновить кэш ассетов] → Ожидаемо; `public/build` пересобирается, manifest актуален.
- [Ошибочное удаление `_variables.scss`/`normalize.css` ломает `style.scss`] → Держать их в scope сохранения (D6).

## Migration Plan

1. Добавить `sass` в `devDependencies`; (опционально) удалить `axios`.
2. В `vite.config.js` добавить вход `resources/sass/style.scss`.
3. В `base.blade.php` заменить `asset('css/style.css')` и `asset('js/app.js')` на `@vite(['resources/sass/style.scss', 'resources/js/app.js'])`.
4. Удалить Mix: `webpack.mix.js`, `public/mix-manifest.json`, `public/js/*`, `public/css/*`.
5. Удалить легаси JS/Vue: `vue.js`, `components/**`, `bootstrap.js`, `App_OLD.vue_OLD`.
6. Удалить `resources/sass/app.scss` и `resources/views/auth_OLD/**`.
7. `npm run build` → проверить `public/build` (style.scss и app.js).
8. В dev/производстве открыть публичные страницы и Breeze-страницы; убедиться в корректной подаче стилей/скриптов.
9. Финальная чистка: grep по устаревшим путям; при необходимости обновить README.

**Откат:** атомарно через git — возврат ветки к состоянию до изменения.

## Open Questions

- Удалять ли `axios` из `devDependencies`? Сейчас `app.js` его не импортирует; решение: удалить (рекомендуется) либо оставить как служебную зависимость.
- Нужна ли актуализация README (инструкции `npm install` / `npm run dev|build`) — определяется по текущему содержимому README.
