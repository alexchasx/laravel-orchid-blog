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
- **WHEN** открывается любая публичная страница
- **THEN** цвета фона, поверхности и текста определяются активной темой Vuetify (light или dark) и их CSS-переменными

#### Scenario: Стили используют токены
- **WHEN** пользователь нажимает переключатель темы
- **THEN** все компоненты страницы перекрашиваются согласно выбранной теме Vuetify без перезагрузки

### Requirement: Базовая типографика
Базовый размер шрифта основного текста публичного фронтенда SHALL составлять не менее 16px на всех viewport. Заголовки SHALL использовать типографические стили Vuetify (классы `text-h*`), монотонно уменьшаясь на мобильных экранах без жёстких фиксированных размеров.

#### Scenario: Минимальный размер текста на мобильных
- **WHEN** страница просматривается на viewport шириной 320–900px
- **THEN** вычисленный `font-size` основного текста составляет не менее 16px

#### Scenario: Fluid-заголовки
- **WHEN** заголовок отображается в браузере
- **THEN** его размер задан типографикой Vuetify и корректно масштабируется между мобильными и широкими экранами

### Requirement: Доступное масштабирование страницы
Viewport-метатег, отдаваемый Nuxt, SHALL NOT содержать ограничения `maximum-scale` и `user-scalable=0`. Пользователь SHALL иметь возможность увеличивать страницу жестом или зумом браузера.

#### Scenario: Зум разрешён на мобильных
- **WHEN** публичная страница открывается на мобильном устройстве
- **THEN** метатег viewport содержит `width=device-width, initial-scale=1` и не содержит `maximum-scale` и `user-scalable`

#### Scenario: Аудит доступности без предупреждений о зуме
- **WHEN** Lighthouse-audit выполняется на публичной странице
- **THEN** отсутствуют предупреждения о запрете масштабирования (viewport allows zoom)
