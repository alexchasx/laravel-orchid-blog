## Context

Проект — Laravel 11-блог с Breeze (auth) и Orchid (admin). Публичная часть использует `layouts/base.blade.php` с legacy-меню, Bootstrap-классами, sidebar с рубриками. SASS модульный (`tokens.scss` → `base.scss` → `layout.scss` → `components.scss` → `dark.scss`). Vite собирает `style.scss` + `app.js`.

Статическая вёрстка в `it-blog-html/` содержит современный неоморфный дизайн с green-accent палитрой, masonry-сеткой, light/dark темой, scroll-reveal анимациями.

## Goals / Non-Goals

**Goals:**
- Единый layout `techlog.blade.php` для всех публичных страниц
- Модульная SCSS-структура `techlog/` с CSS custom properties
- JS-модуль `techlog.js` для интерактивности
- Интеграция с данными из моделей `Article`, `Tag`, `Rubric`, `Contact`
- Legacy-маршруты (`rubric.*`, `tag.*`, `notpublic`) — новый дизайн

**Non-Goals:**
- Миграция Orchid-админки на новый дизайн
- Миграция Breeze-страниц (login, register, profile)
- Backend для newsletter-подписки
- FULLTEXT-индекс для поиска (будет отдельным change)
- Удаление legacy-файлов (`base.blade`, `style.scss`, `app.js`)

## Decisions

### D1: Отдельная ветка SASS вместо модификации существующей

```
Решение: Создать `resources/sass/techlog/` с нуля.
Не модифицировать `resources/sass/style.scss` и `resources/sass/_tokens.scss`.

Альтернатива: Разделить `style.scss` на `legacy.scss` + `techlog.scss`.
Проблема: Сложнее поддерживать, больше рисков сломать legacy.

Рациона: Изоляция — новый дизайн не зависит от legacy-токенов,
старый блог работает независимо.
```

### D2: CSS columns для masonry вместо JS

```
Решение: CSS `columns` для masonry-раскладки.

Альтернатива 1: JS-библиотека (Masonry.js, Isotope).
Проблема: Дополнительная зависимость, блокировка рендера.

Альтернатива 2: CSS Grid с `grid-auto-flow: dense`.
Проблема: Не настоящий masonry (элементы могут оставлять дыры).

Рациона: CSS columns — zero dependency, работает в современных браузерах.
Недостаток: порядок элементов top-to-bottom (не left-to-right).
```

### D3: LIKE-поиск по content_html вместо FULLTEXT

```
Решение: `WHERE content_html LIKE '%...%'` для поиска.

Альтернатива: FULLTEXT-индекс + MATCH() AGAINST().
Проблема: Требует ALTER TABLE, не работает с HTML-тегами корректно.

Рациона: LIKE работает из коробки, не требует миграций БД.
Недостаток: медленнее на больших объёмах, ищет HTML-теги тоже.
```

### D4: TOC вручную через поле в админке

```
Решение: В админке Orchid — отдельное поле `toc` (textarea) для ручного ввода оглавления.

Альтернатива: Автоматический парсинг `<h2 id="...">` из `content_html`.
Проблема: Markdown-автор может забыть добавить id, парсинг HTML ненадёжен.

Рациона: Полный контроль автора, нет багов с парсингом.
Недостаток: автор должен поддерживать toc в актуальном состоянии.
```

### D5: Contact-форма доступна гостям

```
Решение: Любой посетитель может отправить сообщение через `/contact`.

Альтернатива: Только авторизованные пользователи.
Проблема: Ограничивает обратную связь, HTML-форма несовместима с текущей логикой `saveContact()`.

Рациона: Соответствует новой вёрстке (гостевая форма),
старая форма (только для авторов) будет удалена.
```

## Risks / Trade-offs

| Risk | Вероятность | Влияние | Митигация |
|---|---|---|---|
| Masonry порядок top-to-bottom (не left-to-right) | Высокая | Среднее | Пользователи не замечают порядок, контент важнее |
| LIKE-поиск медленный на 1000+ статьях | Низкая | Низкое | Пагинация (6 на страницу), поиск по title+content HTML |
| CSS custom properties не работают в старых браузерах | Низкая | Низкое | Целевая аудитория — разработчики, используют современные браузеры |
| TOC вручную расходуется автором | Средняя | Низкое | Orchid-поле с placeholder-примером |
| Конфликт legacy- и techlog-токов при одновременной загрузке | Низкая | Среднее | Токены в разных файлах, применяются к разным layout |

## Migration Plan

### Этап 1: Foundation
1. Создать `resources/sass/techlog/` (копирование `styles.css`)
2. Создать `resources/js/techlog.js` (копирование `script.js`)
3. Создать `layouts/techlog.blade.php`
4. Обновить `vite.config.js`
5. Проверить `npm run build`

### Этап 2: Pages
1. Переписать `index.blade.php`
2. Переписать `article.blade.php`
3. Переписать `contact.blade.php`
4. Обновить `ArticleController` и `ContactController`
5. Обновить маршруты (legacy-маршруты → новый дизайн)

### Rollback
- `base.blade.php` не модифицируется → rollback = revert `vite.config.js` и удаление `techlog/` файлов
- Публичные страницы revert на `@extends('layouts.base')`

## Open Questions

Нет. Все вопросы решены в плане (`docs/blade-migration-plan.md`).
