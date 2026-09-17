# frontend-techlog-styles

## Purpose

Дизайн-система публичного фронтенда в модульных SCSS-файлах: CSS custom properties для light/dark тем, неоморфные тени, masonry-сетка, адаптивность, scroll-reveal анимации.

## Requirements

### Requirement: CSS custom properties для тем

`resources/sass/techlog/_variables.scss` **SHALL** определять CSS custom properties в `:root` и `html.light`:

- `:root` (dark по умолчанию): `--bg`, `--surface`, `--surface-2`, `--text`, `--muted`, `--green`, `--green-dark`, `--line`, `--shadow`, `--max`, `--radius`
- `html.light`: переопределение тех же переменных для светлой темы
- `--bg`: `#0a0a0a` (dark) / `#e8eee9` (light)
- `--green`: `#00ff88`
- `--text`: `#f0f5f2` (dark) / `#112018` (light)
- `--muted`: `#8b9992` (dark) / `#53645b` (light)
- `--shadow`: неоморфные тени (внешняя тень + внутреннее свечение)

#### Scenario: Dark-тема по умолчанию

- **WHEN** страница загружена без сохранённой темы
- **THEN** `:root` задает `--bg: #0a0a0a` и `--text: #f0f5f2`

#### Scenario: Light-тема применяется

- **WHEN** пользователь нажал `.theme-toggle`
- **THEN** на `<html>` добавляется класс `.light` и переменные переопределяются

#### Scenario: Тема сохраняется в localStorage

- **WHEN** страница перезагружена
- **THEN** тема из `localStorage('techlog-theme')` применяется до отрисовки

### Requirement: Модульная организация SCSS

`resources/sass/techlog/` **SHALL** содержать модули:

- `_variables.scss` — CSS custom properties
- `_reset.scss` — `*`, `box-sizing`, `html`, `body`
- `_base.scss` — типографика, ссылки, кнопки
- `_components.scss` — header, nav, hero, posts, masonry, topics, article, footer
- `_forms.scss` — формы, инпуты, кнопки
- `_utilities.scss` — `.container`, `.reveal`, `.skip-link`, `.sr-only`
- `_responsive.scss` — `@media` (850px, 520px, `prefers-reduced-motion`)
- `index.scss` — entry point (orchestrator)

#### Scenario: Сборка проходит без ошибок

- **WHEN** выполняется `npm run build`
- **THEN** Vite собирает `resources/sass/techlog/index.scss` без ошибок

### Requirement: Неоморфные тени

Компоненты (`.post`, `.topic`, `.button`, `.contact-form`) **SHALL** использовать неоморфные тени из `--shadow`:

- Внешняя тень: `10px 10px 28px rgba(0,0,0,.65)` (dark) / `10px 10px 25px rgba(91,110,99,.18)` (light)
- Внутреннее свечение: `-8px -8px 24px rgba(255,255,255,.025)` (dark) / `-8px -8px 22px rgba(255,255,255,.85)` (light)

#### Scenario: Карточка статьи имеет неоморфную тень

- **WHEN** рендерится `.post`
- **THEN** применяется `box-shadow: var(--shadow)`

### Requirement: Masonry-сетка

Секция `#articles` **SHALL** использовать CSS `columns` для masonry-раскладки:

- Desktop: 3 колонки
- Tablet (≤850px): 2 колонки
- Mobile (≤520px): 1 колонка
- `.featured`-статья: `grid-column: span 2` (визуально крупнее)

#### Scenario: Masonry-сетка на desktop

- **WHEN** viewport > 850px
- **THEN** статьи отображаются в 3 колонки

#### Scenario: Masonry на мобильном

- **WHEN** viewport ≤ 520px
- **THEN** статьи отображаются в 1 колонку

### Requirement: Адаптивность

`_responsive.scss` **SHALL** определять брейкпоинты:

- `@media (max-width: 850px)`: мобильное меню, 2 колонки masonry, single-column layout для article/about/newsletter/contact
- `@media (max-width: 520px)`: 1 колонка masonry, уменьшенные заголовки
- `@media (prefers-reduced-motion: reduce)`: отключение анимаций

#### Scenario: Мобильное меню

- **WHEN** viewport ≤ 850px
- **THEN** `.nav-links` скрыт, кнопка `.menu-toggle` видна

#### Scenario: Reduced motion

- **WHEN** пользователь запросил `prefers-reduced-motion: reduce`
- **THEN** `.reveal` имеет `opacity: 1; transform: none; transition: none`

### Requirement: Scroll-reveal анимации

Элементы с классом `.reveal` **SHALL** анимироваться при появлении в viewport:

- Начальное состояние: `opacity: 0; transform: translateY(30px)`
- Конечное состояние: `opacity: 1; transform: translateY(0)`
- `IntersectionObserver` с `threshold: 0.12`
- После появления добавляется класс `.visible`

#### Scenario: Элемент появляется при скролле

- **WHEN** элемент `.reveal` входит в viewport
- **THEN** ему добавляется класс `.visible` и запускается анимация
