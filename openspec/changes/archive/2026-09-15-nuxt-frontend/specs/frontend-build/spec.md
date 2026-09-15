## MODIFIED Requirements

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
