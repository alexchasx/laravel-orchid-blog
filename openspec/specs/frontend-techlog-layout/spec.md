# frontend-techlog-layout

## Purpose

Единый Blade-layout `techlog.blade.php` для всех публичных страниц блога: header с навигацией, full-width контентная область, footer, кнопка «наверх», мобильное меню. Все страницы расширяют этот layout; старый `base.blade.php` сохраняется как fallback.

## Requirements

### Requirement: Единый layout techlog

Все публичные страницы (`index`, `article`, `contact`) **SHALL** расширять `layouts/techlog.blade.php`. Layout **SHALL** содержать:

- `<head>` с `@vite` для подключения стилей и скриптов techlog, meta-тегами, OG-разметкой, `lang="ru"`
- Header: фиксированная позиция, `z-index: 50`, backdrop-blur, brand (TECH//LOG), навигационные ссылки, выпадающее меню «Темы», кнопка переключения темы, мобильное меню
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

### Requirement: Выпадающее меню «Темы» в хедере

Layout **SHALL** содержать в навигации выпадающее меню «Темы» (`.nav-dropdown`):

- Список — динамические рубрики из БД через View Composer `RubricsComposer`, привязанный к `layouts.techlog`
- Выводятся только рубрики с хотя бы одной опубликованной статьёй (`is_published = true` и `published_at <= now()`), порядок — алфавитный по `title`
- Ссылки строятся через маршрут `showByRubric` с объектом рубрики
- Если таких рубрик нет, кнопка «Темы» и меню скрыты целиком
- Секции «Темы» на главной странице быть не должно (`index.blade.php` без `#topics`)

#### Scenario: Рубрика с опубликованной статьёй отображается

- **WHEN** в базе есть рубрика с хотя бы одной опубликованной статьёй
- **THEN** в выпадающем меню хедера выводится ссылка на эту рубрику с её названием

#### Scenario: Рубрика без опубликованных статей скрыта

- **WHEN** у рубрики нет опубликованных статей (включая рубрики только с черновиками или запланированными статьями)
- **THEN** ссылка на рубрику не выводится в меню

#### Scenario: Нет рубрик с опубликованными статьями

- **WHEN** ни одна рубрика не имеет опубликованных статей
- **THEN** кнопка «Темы» не отображается в хедере

#### Scenario: Главная без секции «Темы»

- **WHEN** открывается главная страница
- **THEN** секция `#topics` отсутствует — список тем доступен только через хедер

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
