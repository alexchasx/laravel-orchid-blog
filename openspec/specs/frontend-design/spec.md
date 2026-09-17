# frontend-design

## Purpose

Определяет единую дизайн-систему публичного фронтенда блога: дизайн-токены (CSS custom properties), модульную организацию SCSS, базовую типографику и доступный viewport. Эти требования создают фундамент для последующих фаз обновления дизайна.

## Requirements

### Requirement: Единый источник дизайн-токенов
Система SHALL предоставлять дизайн-токены в виде CSS custom properties, разделённых по layout:
- Legacy-токены: `resources/sass/_tokens.scss` → `:root` для `base.blade`
- Techlog-токены: `resources/sass/techlog/_variables.scss` → `:root` и `html.light` для `techlog.blade`

Legacy-токены (`:root` в `_tokens.scss`):
- `--color-body-bg: #f8fafc`, `--color-surface: #eee`, `--color-text: #333`, `--color-link: #5d92b7`, `--color-accent: #5d92b7`
- Типографика, spacing, радиусы, тени — без изменений

Techlog-токены (`:root` в `techlog/_variables.scss`):
- `--bg: #0a0a0a`, `--surface: #151715`, `--surface-2: #1a1a1a`, `--text: #f0f5f2`, `--muted: #8b9992`, `--green: #00ff88`, `--line: rgba(0,255,136,.15)`, `--shadow: 10px 10px 28px rgba(0,0,0,.65),-8px -8px 24px rgba(255,255,255,.025)`, `--max: 1180px`, `--radius: 20px`
- `html.light`: `--bg: #e8eee9`, `--surface: #e8eee9`, `--surface-2: #edf2ee`, `--text: #112018`, `--muted: #53645b`, `--line: rgba(0,105,58,.18)`, `--shadow: 10px 10px 25px rgba(91,110,99,.18),-8px -8px 22px rgba(255,255,255,.85)`

#### Scenario: Токены определены в :root
- **WHEN** загружается собранный CSS публичного блога
- **THEN** селектор `:root` объявляет CSS custom properties для цвета, типографики, spacing, радиусов и теней

#### Scenario: Стили используют токены
- **WHEN** стиль публичной части задаёт цвет, радиус или тень
- **THEN** значение берётся из CSS custom property, а не из хардкод-литерала

### Requirement: Модульная организация стилей с сохранением сборки Vite
`resources/sass/style.scss` SHALL оставаться единственной входной точкой кастомных стилей публичного блога и SHALL подключаться в шаблонах через `@vite` без изменения пути. Реализация стилей SHALL быть разбита на модули (партиалы токенов, базовых стилей, макета, компонентов, тёмной темы), подключаемые из `style.scss` через `@use`.

#### Scenario: Сборка проходит после реорганизации
- **WHEN** выполняется `npm run build`
- **THEN** Vite успешно собирает `resources/sass/style.scss` без ошибок и без изменения имени выходного файла

#### Scenario: Стили применяются на страницах
- **WHEN** открывается публичная страница (index/article/contact)
- **THEN** к странице подключён собранный CSS и применяются стили из партиалов

### Requirement: Базовая типографика
Базовый размер шрифта публичного фронтенда SHALL составлять не менее 16px на всех viewport, включая мобильные. Семейство шрифтов SHALL использовать системный стек. Заголовки SHALL использовать fluid-размеры через `clamp()`.

#### Scenario: Минимальный размер текста на мобильных
- **WHEN** страница просматривается на viewport шириной 320–900px
- **THEN** вычисленный `font-size` основного текста составляет не менее 16px

#### Scenario: Fluid-заголовки
- **WHEN** заголовок h1–h6 отображается в браузере
- **THEN** его размер задан через `clamp()`, монотонно меняясь между минимальным и максимальным значениями

### Requirement: Доступное масштабирование страницы
Viewport-метатег в `resources/views/layouts/base.blade.php` SHALL NOT содержать ограничения `maximum-scale` и `user-scalable=0`. Пользователь SHALL иметь возможность увеличивать страницу жестом или зумом браузера.

#### Scenario: Зум разрешён на мобильных
- **WHEN** страница открывается на мобильном устройстве
- **THEN** метатег viewport содержит `width=device-width, initial-scale=1` и не содержит `maximum-scale` и `user-scalable`

#### Scenario: Аудит доступности без предупреждений о зуме
- **WHEN** Lighthouse-audit выполняется на публичной странице
- **THEN** отсутствуют предупреждения о запрете масштабирования (viewport allows zoom)

### Requirement: Совместимость наследуемых SCSS-переменных
`resources/sass/_variables.scss` SHALL сохраняться и продолжать экспортировать SCSS-переменные, используемые существующим кодом, чтобы реорганизация не ломала сборку. Цветовые значения из `_variables.scss` SHALL быть перенесены в CSS custom properties из требования «Единый источник дизайн-токенов».

#### Scenario: Наследуемые переменные продолжают работать
- **WHEN** выполняется `npm run build` после реорганизации стилей
- **THEN** сборка не содержит ошибок undefined variable, а переменные `_variables.scss` доступны в наследуемом SCSS

#### Scenario: Цвета присутствуют в токенах
- **WHEN** сравниваются цветовые значения `_variables.scss` и CSS custom properties
- **THEN** каждый цвет из `_variables.scss` представлен соответствующим токеном в `:root`
