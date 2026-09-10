## Why

Фронтенд проекта находится в гибридном состоянии: часть уже переведена на Vite, а публичный блог всё ещё собирается и подключается через Laravel Mix.

Уже на Vite:
- [`package.json`](../../package.json) — зависимости `vite`, `laravel-vite-plugin`, `tailwindcss`, `alpinejs`; скрипты `dev`/`build` через `vite`.
- [`vite.config.js`](../../vite.config.js) — точка входа `resources/css/app.css` + `resources/js/app.js`.
- [`app.blade.php`](../../resources/views/layouts/app.blade.php) и [`guest.blade.php`](../../resources/views/layouts/guest.blade.php) подключают ассеты через `@vite(...)`.
- Собранный вывод в `public/build/` (manifest + hashed assets).

Всё ещё на Mix / легаси:
- [`base.blade.php`](../../resources/views/layouts/base.blade.php) — общий шаблон всех публичных страниц (index/article/contact/errors) — подключает `asset('css/style.css')` и `asset('js/app.js')` напрямую из Mix-сборки.
- [`webpack.mix.js`](../../webpack.mix.js) — собирает `resources/js/app.js` и `resources/sass/*.scss` в `public/js` и `public/css`.
- [`public/mix-manifest.json`](../../public/mix-manifest.json) — устаревший манифест.
- Легаси JS/Vue: [`resources/js/vue.js`](../../resources/js/vue.js) (Vue 2), `resources/js/components/**` (Comment/Like), [`resources/js/bootstrap.js`](../../resources/js/bootstrap.js) (CommonJS: lodash/bootstrap/axios) — **не собираются** ни в Mix, ни в Vite и не используются (комментарии рендерятся серверно Blade).
- [`resources/sass/app.scss`](../../resources/sass/app.scss) (Bootstrap) — осиротел: `app.blade.php` переведён на Tailwind.

Требуется завершить миграцию: перевести публичный блог на Vite и удалить весь устаревший Mix/легаси-код.

## What Changes

- **Зависимости:** добавить `sass` в `devDependencies` (требуется Vite для сборки `.scss`). `laravel-mix` в `package.json` отсутствует, повторно не добавлять.
- **[`vite.config.js`](../../vite.config.js):** добавить входную точку `resources/sass/style.scss` (кастомные стили публичного блога). Входы `resources/css/app.css` (Tailwind, Breeze) и `resources/js/app.js` (Alpine) сохраняются.
- **[`base.blade.php`](../../resources/views/layouts/base.blade.php):** заменить `<link asset('css/style.css')>` и `<script asset('js/app.js')>` на `@vite(['resources/sass/style.scss', 'resources/js/app.js'])`. Это единая точка миграции для всех публичных страниц (index, article, contact, ошибки).
- **Удалить Mix:** `webpack.mix.js`, устаревший `public/mix-manifest.json`, старый Mix-выход `public/js/*`, `public/css/*` (Vite пишет в `public/build`).
- **Удалить легаси JS/Vue:** `resources/js/vue.js`, `resources/js/components/**`, `resources/js/bootstrap.js`, `resources/js/App_OLD.vue_OLD`. Комментарии остаются на серверном рендере Blade.
- **Удалить осиротевший Bootstrap SASS:** `resources/sass/app.scss`. `resources/sass/_variables.scss` и `resources/css/normalize.css` оставить (их использует `style.scss`).
- **Удалить неиспользуемые шаблоны:** `resources/views/auth_OLD/**`.
- **Судьба `axios`:** сейчас `app.js` его не импортирует — удалить из `devDependencies` (уточнить; см. Open Questions).
- **Проверка:** `npm run build` → корректный `public/build`; в dev и производстве открыть публичные и Breeze-страницы.

## Capabilities

### New Capabilities
- `frontend-build`: Зафиксировать Vite как единый инструмент сборки фронтенда: входные точки (`resources/css/app.css`, `resources/sass/style.scss`, `resources/js/app.js`), подключение через `@vite(...)` во всех layouts, отсутствие артефактов Mix и легаси-кода Vue/CommonJS.

## Impact

- [`package.json`](../../package.json), [`package-lock.json`](../../package-lock.json) — добавление `sass`; вероятное удаление `axios`.
- [`vite.config.js`](../../vite.config.js) — новая входная точка.
- [`resources/views/layouts/base.blade.php`](../../resources/views/layouts/base.blade.php) — переход на `@vite`.
- Удаление: `webpack.mix.js`, `public/mix-manifest.json`, `public/js/*`, `public/css/*`, `resources/js/vue.js`, `resources/js/components/**`, `resources/js/bootstrap.js`, `resources/js/App_OLD.vue_OLD`, `resources/sass/app.scss`, `resources/views/auth_OLD/**`.
- Не затрагивается: Orchid-админка (`public/vendor/orchid/*`), бизнес-логика контроллеров/моделей.
