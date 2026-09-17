## Why

Статическая вёрстка в `it-blog-html/` (современный неоморфный дизайн, masonry-сетка, light/dark тема) должна быть интегрирована в Laravel-блог как Blade-шаблоны с динамическими данными. Текущий `layouts/base.blade.php` устарел: legacy-меню, Bootstrap-классы, sidebar с рубриками — всё это не соответствует целевому дизайну и ограничивает развитие публичной части блога.

## What Changes

- Новый layout `layouts/techlog.blade.php` — header с навигацией, full-width контент, footer
- Миграция `styles.css` → модульная SCSS-структура `resources/sass/techlog/`
- Миграция `script.js` → `resources/js/techlog.js`
- Переписанные страницы: `index.blade.php` (hero + masonry), `article.blade.php` (prose + TOC), `contact.blade.php` (открытая форма)
- Обновлённые контроллеры: поиск по title + content_html, пагинация с новым дизайном
- Legacy-маршруты (`rubric.*`, `tag.*`, `notpublic`) — новый дизайн
- Старый `layouts/base.blade.php` остаётся как fallback для Orchid
- Vite: добавление новых entry-пунктов, старые не удаляются

## Capabilities

### New Capabilities

- `frontend-techlog-layout`: Единый layout `techlog.blade.php` с header (brand, навигация, theme-toggle), full-width content area, footer, кнопка «наверх», мобильное меню. Все публичные страницы расширяют этот layout.
- `frontend-techlog-styles`: Дизайн-система в SCSS-модулях (`resources/sass/techlog/`): CSS custom properties для light/dark тем, неоморфные тени, masonry-сетка, адаптивность (850px, 520px), scroll-reveal анимации.
- `frontend-techlog-interactions`: JavaScript-модуль: мобильное меню, переключение темы (localStorage), scroll-reveal (IntersectionObserver), кнопка «наверх», валидация форм.
- `frontend-techlog-pages`: Контентные страницы: главная (hero, masonry-сетка статей, topics, about, newsletter), статья (hero-image, TOC, prose, tags, комментарии), контакты (имя, email, сообщение).
- `frontend-techlog-search`: Поиск по заголовку и content_html через LIKE-запросы.
- `frontend-techlog-contact-form`: Открытая форма обратной связи для гостей (name, email, message), сохранение в модель Contact.

### Modified Capabilities

- `frontend-build`: Входные точки Vite расширяются (добавляются `techlog/index.scss` и `techlog.js`).
- `frontend-design`: Дизайн-токены расширяются новой палитрой techlog (зелёный акцент, неоморфизм).

## Impact

**Файлы:**
- `resources/views/layouts/techlog.blade.php` — новый layout
- `resources/views/index.blade.php` — полная переписывание
- `resources/views/article.blade.php` — полная переписывание
- `resources/views/contact.blade.php` — полная переписывание
- `resources/sass/techlog/` — новый каталог SCSS-модулей
- `resources/js/techlog.js` — новый JS-модуль
- `vite.config.js` — новые entry-пункты
- `routes/web.php` — обновление роутов

**Контроллеры:**
- `ArticleController` — обновление `index()` (поиск по контенту), `show()`
- `ContactController` — обновление `store()` для новой формы

**Модели:**
- `Article` — scope для поиска по content_html
- `Contact` — fillable для name/email

**Легаси:**
- `layouts/base.blade.php` — сохраняется
- `resources/sass/style.scss` — сохраняется
- `resources/js/app.js` — сохраняется
