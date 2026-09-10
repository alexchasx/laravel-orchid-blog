# frontend-build

## Purpose

Vite является единственным инструментом сборки фронтенда. Все стили и скрипты собираются через `vite.config.js` и подключаются в Blade-шаблонах через директиву `@vite(...)`. Артефакты Laravel Mix и легаси-код (Vue 2, CommonJS) удалены.

## Requirements

### R1: Единый инструмент сборки — Vite

- **SHALL** использовать `vite` + `laravel-vite-plugin` для сборки фронтенда.
- **SHALL NOT** использовать `laravel-mix`/`webpack.mix.js`.
- `package.json` **SHALL** содержать скрипты `dev` и `build`, вызывающие `vite`.

### R2: Входные точки сборки

`vite.config.js` **SHALL** объявлять следующие входные точки:
- `resources/css/app.css` — Tailwind, для Breeze-шаблонов (`app`, `guest`);
- `resources/sass/style.scss` — кастомные стили публичного блога;
- `resources/js/app.js` — общий JavaScript (Alpine).

### R3: Подключение ассетов в шаблонах

- Все layouts **SHALL** подключать собранные ассеты через `@vite(...)`, а не через `asset()`/`mix()`.
- `resources/views/layouts/base.blade.php` **SHALL** использовать `@vite(['resources/sass/style.scss', 'resources/js/app.js'])` (покрывает index/article/contact/errors).
- `resources/views/layouts/app.blade.php` и `guest.blade.php` **SHALL** использовать `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

### R4: Отсутствие артефактов Mix

- **SHALL NOT** существовать `webpack.mix.js` и `public/mix-manifest.json`.
- Старый Mix-выход `public/js/*`, `public/css/*` **SHALL** быть удалён; актуальный вывод — в `public/build/`.

### R5: Удаление легаси-кода

- **SHALL NOT** существовать `resources/js/vue.js`, `resources/js/components/**`, `resources/js/bootstrap.js`, `resources/js/App_OLD.vue_OLD`.
- **SHALL NOT** существовать осиротевший `resources/sass/app.scss` (Bootstrap) и неиспользуемые `resources/views/auth_OLD/**`.
- `resources/sass/_variables.scss` и `resources/css/normalize.css` **SHALL** сохраняться (используются `style.scss`).

### R6: Комментарии на серверном рендере

- Комментарии публичной части **SHALL** оставаться на серверном рендере Blade (без Vue/Alpine), формы и списки — в `includes/comments_form.blade.php` и `includes/comments_list.blade.php`.
