# frontend-techlog-layout

## Purpose

Единый Blade-layout `techlog.blade.php` для всех публичных страниц блога: header с навигацией, full-width контентная область, footer, кнопка «наверх», мобильное меню. Все страницы расширяют этот layout; старый `base.blade.php` сохраняется как fallback.

## Requirements

### Requirement: Единый layout techlog

Все публичные страницы (`index`, `article`, `contact`) **SHALL** расширять `layouts/techlog.blade.php`. Layout **SHALL** содержать:

- `<head>` с `@vite` для подключения стилей и скриптов techlog, meta-тегами, OG-разметкой, `lang="ru"`
- Header: фиксированная позиция, `z-index: 50`, backdrop-blur, brand (TECH//LOG), навигационные ссылки, кнопка переключения темы, мобильное меню
- Main: `@yield('content')`
- Footer: copyright, социальные ссылки
- Кнопка «наверх» (`.to-top`)

#### Scenario: Страница расширяет techlog layout

- **WHEN** открывается главная, статья или контакты
- **THEN** Blade-шаблон содержит `@extends('layouts.techlog')` и подключённые через `@vite` ассеты techlog

#### Scenario: Header фиксирован

- **WHEN** страница прокручивается вниз
- **THEN** header остаётся видимым (`position: fixed; top: 0`)

#### Scenario: Legacy-страницы используют base layout

- **WHEN** открывается legacy-маршрут (`notpublic`) или Orchid-админка
- **THEN** используется `layouts/base.blade.php`, а не `techlog.blade.php`

### Requirement: Meta-теги

Layout **SHALL** включать:

- `<meta charset="utf-8">`
- `<meta name="viewport" content="width=device-width, initial-scale=1">`
- `<meta name="theme-color" content="#0a0a0a">`
- CSRF-токен
- Динамические meta: title, description (из `$metaDesc`), Open Graph (title, description, url, locale)

#### Scenario: Meta-теги статьи

- **WHEN** открывается статья
- **THEN** meta description берётся из `$article->meta_desc`, og:title — из `$article->title`

#### Scenario: Meta-теги главной

- **WHEN** открывается главная страница
- **THEN** meta description берётся из `$metaDesc`, og:title — из `$metaTitle`

### Requirement: Подключение ассетов

Layout **SHALL** подключать ассеты через `@vite`:

- `resources/sass/techlog/index.scss` — стили
- `resources/js/techlog.js` — скрипты

#### Scenario: Ассеты подключены через Vite

- **WHEN** страница загружается
- **THEN** в `<head>` есть `<link>` для CSS, в `<body>` — `<script>` для JS
