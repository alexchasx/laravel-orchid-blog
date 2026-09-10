## 1. Зависимости

- [x] 1.1 Добавить `sass` в `devDependencies` (`npm i -D sass`) и зафиксировать в `package.json`/`package-lock.json`
- [x] 1.2 (Опционально) Удалить `axios`, если он не используется в `resources/js/app.js` (см. Open Questions)
- [x] 1.3 Убедиться, что `laravel-mix` отсутствует в `package.json`

## 2. Конфигурация Vite

- [x] 2.1 В `vite.config.js` добавить входную точку `resources/sass/style.scss` в `laravel({ input: [...] })`, сохранив `resources/css/app.css` и `resources/js/app.js`

## 3. Миграция шаблона публичного блога

- [x] 3.1 В `resources/views/layouts/base.blade.php` заменить `<link rel="stylesheet" href="{{ asset('css/style.css') }}" />` и `<script src="{{ asset('js/app.js') }}"></script>` на `@vite(['resources/sass/style.scss', 'resources/js/app.js'])`

## 4. Удаление Mix

- [x] 4.1 Удалить `webpack.mix.js`
- [x] 4.2 Удалить устаревший `public/mix-manifest.json`
- [x] 4.3 Удалить старый Mix-выход: `public/js/app.js*`, `public/css/app.css*`, `public/css/style.css*` (Vite пишет в `public/build`)

## 5. Удаление легаси JS/Vue

- [x] 5.1 Удалить `resources/js/vue.js`
- [x] 5.2 Удалить `resources/js/components/**` (Comment.vue, CommentForm.vue, CommentList.vue, Like.vue)
- [x] 5.3 Удалить `resources/js/bootstrap.js` (CommonJS: lodash/bootstrap/axios)
- [x] 5.4 Удалить `resources/js/App_OLD.vue_OLD`

## 6. Удаление осиротевшего SASS и шаблонов

- [x] 6.1 Удалить `resources/sass/app.scss` (Bootstrap); сохранить `resources/sass/_variables.scss` и `resources/css/normalize.css`
- [x] 6.2 Удалить `resources/views/auth_OLD/**` (не подключаются)

## 7. Сборка и проверка

- [x] 7.1 `npm run build` — убедиться, что `public/build/manifest.json` содержит стили и скрипты для всех входов
- [x] 7.2 Открыть публичные страницы (главная, статья с комментариями, контакт) — стили `style.scss` и скрипты `app.js` подключаются
- [x] 7.3 Открыть Breeze-страницы (`app`/`guest`) — Tailwind и Alpine работают

## 8. Финальная чистка

- [x] 8.1 Grep по `asset('css/`, `asset('js/`, `mix(`, удалённым путям — нет ссылок на удалённые ассеты
- [x] 8.2 Обновить README при необходимости (инструкции `npm install` / `npm run dev|build`)
