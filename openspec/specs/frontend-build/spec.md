# frontend-build

## Purpose

Vite является единственным инструментом сборки фронтенда. Все стили и скрипты собираются через `vite.config.js` и подключаются в Blade-шаблонах через директиву `@vite(...)`. Артефакты Laravel Mix и легаси-код (Vue 2, CommonJS) удалены.

## Requirements

### Requirement: R1: Единый инструмент сборки — Vite
Публичный фронтенд блога SHALL собираться и запускаться через Nuxt 4 (`frontend/`) с серверным рендерингом. Vite SHALL использоваться только для Breeze-шаблонов аутентификации. `laravel-mix`/`webpack.mix.js` SHALL NOT использоваться.

- **SHALL** использовать Nuxt 4 в папке `frontend/` для сборки и запуска публичного фронтенда.
- **SHALL NOT** использовать `laravel-mix`/`webpack.mix.js`.
- Vite **SHALL** оставаться инструментом сборки только для Breeze-шаблонов (`app`, `guest`).

#### Scenario: Сборка Nuxt проходит
- **WHEN** выполняется `npm run build` в `frontend/`
- **THEN** Nuxt успешно собирает приложение (SSR + клиентский бандл) без ошибок

#### Scenario: Vite обслуживает Breeze
- **WHEN** выполняется `npm run dev`/`npm run build` в корне проекта
- **THEN** Vite собирает ассеты Breeze-шаблонов аутентификации без ошибок

### Requirement: R2: Входные точки сборки
`vite.config.js` SHALL объявлять входные точки только для Breeze-шаблонов: `resources/css/app.css` (Tailwind) и `resources/js/app.js` (Alpine). `resources/sass/style.scss` SHALL NOT быть входной точкой публичного фронтенда; стили публичного фронтенда живут в Nuxt-приложении (Vuetify 3 и `frontend/app/assets/css/main.css`).

#### Scenario: Сборка Vite без style.scss
- **WHEN** выполняется `npm run build` в корне проекта
- **THEN** Vite собирает только входные точки Breeze, без ошибок undefined variable

#### Scenario: Стили публичного фронтенда в Nuxt
- **WHEN** открывается публичная страница, обслуживаемая Nuxt
- **THEN** её стили загружаются из Nuxt-приложения, а не из `@vite(['resources/sass/style.scss', ...])`

### Requirement: R3: Подключение ассетов в шаблонах

- Все layouts **SHALL** подключать собранные ассеты через `@vite(...)`, а не через `asset()`/`mix()`.
- `resources/views/layouts/base.blade.php` **SHALL** использовать `@vite(['resources/sass/style.scss', 'resources/js/app.js'])` (покрывает index/article/contact/errors).
- `resources/views/layouts/app.blade.php` и `guest.blade.php` **SHALL** использовать `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

#### Scenario: Публичные шаблоны подключают собранные ассеты
- **WHEN** рендерится публичный layout (`base.blade.php`)
- **THEN** ассеты подключены через `@vite(['resources/sass/style.scss', 'resources/js/app.js'])`, а не через `asset()`/`mix()`

#### Scenario: Breeze-шаблоны используют Tailwind-ассеты
- **WHEN** рендерятся `app.blade.php` или `guest.blade.php`
- **THEN** они подключают `@vite(['resources/css/app.css', 'resources/js/app.js'])`

### Requirement: R4: Отсутствие артефактов Mix

- **SHALL NOT** существовать `webpack.mix.js` и `public/mix-manifest.json`.
- Старый Mix-выход `public/js/*`, `public/css/*` **SHALL** быть удалён; актуальный вывод — в `public/build/`.

#### Scenario: Артефакты Mix отсутствуют
- **WHEN** проверяется структура репозитория
- **THEN** `webpack.mix.js` и `public/mix-manifest.json` не существуют, а старые `public/js/*` и `public/css/*` удалены

### Requirement: R5: Удаление легаси-кода

- **SHALL NOT** существовать `resources/js/vue.js`, `resources/js/components/**`, `resources/js/bootstrap.js`, `resources/js/App_OLD.vue_OLD`.
- **SHALL NOT** существовать осиротевший `resources/sass/app.scss` (Bootstrap) и неиспользуемые `resources/views/auth_OLD/**`.
- `resources/sass/_variables.scss` и `resources/css/normalize.css` **SHALL** сохраняться (используются `style.scss`).

#### Scenario: Легаси-артефакты отсутствуют
- **WHEN** проверяется структура `resources/js` и `resources/views`
- **THEN** `vue.js`, `components/**`, `bootstrap.js`, `App_OLD.vue_OLD`, `resources/sass/app.scss` и `auth_OLD/**` не существуют

#### Scenario: Наследуемые SCSS-файлы сохраняются
- **WHEN** выполняется сборка Vite
- **THEN** `resources/sass/_variables.scss` и `resources/css/normalize.css` остаются в репозитории и используются `style.scss`

### Requirement: R6: Комментарии на серверном рендере

- Комментарии публичной части **SHALL** оставаться на серверном рендере Blade (без Vue/Alpine), формы и списки — в `includes/comments_form.blade.php` и `includes/comments_list.blade.php`.

#### Scenario: Комментарии рендерятся на сервере
- **WHEN** открывается страница статьи с комментариями
- **THEN** HTML комментариев и формы отдаётся с сервера Blade из `includes/comments_form.blade.php` и `includes/comments_list.blade.php`
