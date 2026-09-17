## 1. Foundation: SCSS-модули

- [x] 1.1 Создать каталог `resources/sass/techlog/` и перенести CSS из `it-blog-html/styles.css` в модули SCSS
  - Создать `_variables.scss` с `:root` и `html.light` CSS custom properties
  - Создать `_reset.scss` с `*`, `box-sizing`, `html`, `body`
  - Создать `_base.scss` с типографикой, ссылками, кнопками
  - Создать `_components.scss` с header, nav, hero, posts, masonry, topics, article, footer
  - Создать `_forms.scss` с формами, инпутами, кнопками
  - Создать `_utilities.scss` с `.container`, `.reveal`, `.skip-link`, `.sr-only`
  - Создать `_responsive.scss` с `@media` (850px, 520px, `prefers-reduced-motion`)
  - Создать `index.scss` как entry point
  - **Проверка**: `npm run build` проходит без ошибок

## 2. Foundation: JavaScript

- [x] 2.1 Создать `resources/js/techlog.js` и перенести логику из `it-blog-html/script.js`
  - Мобильное меню (`.menu-toggle` ↔ `.nav-links`)
  - Тема (`.theme-toggle` + `localStorage`)
  - Scroll reveal (`IntersectionObserver`)
  - Кнопка «Наверх» (`.to-top`)
  - Валидация форм (`[data-validate]`, `[data-contact-form]`)
  - **Проверка**: `npm run build` собирает JS, браузерная консоль без ошибок

## 3. Foundation: Layout

- [x] 3.1 Создать `resources/views/layouts/techlog.blade.php`
  - `<head>` с `@vite(['resources/sass/techlog/index.scss', 'resources/js/techlog.js'])`
  - Meta-теги: charset, viewport, theme-color, CSRF
  - Динамические meta: title, description, OG (title, description, url, locale)
  - Header: brand (TECH//LOG), nav-links, theme-toggle, menu-toggle
  - Main: `@yield('content')`
  - Footer: copyright, social links
  - Кнопка «Наверх» `.to-top`
  - **Проверка**: `php artisan view:clear` проходит, страница рендерится

## 4. Foundation: Vite

- [x] 4.1 Обновить `vite.config.js` — добавить entry-пункты techlog
  - `resources/sass/techlog/index.scss`
  - `resources/js/techlog.js`
  - **Проверка**: `npm run build` создаёт `public/build/techlog-*` файлы

## 5. Pages: Главная

- [x] 5.1 Переписать `resources/views/index.blade.php`
  - `@extends('layouts.techlog')`
  - Hero-секция: eyebrow, h1, lead, CTA-кнопки, terminal-note, code-card
  - Articles-секция: masonry-сетка, первая статья `.featured` с `.post-image`, остальные с мета
  - Topics-секция: сетка 4 тем
  - About-секция
  - Newsletter-секция: форма подписки (frontend-only)
  - **Проверка**: `php artisan route:list` → `/` возвращает 200, страница отображается с данными

## 6. Pages: Статья

- [x] 6.1 Переписать `resources/views/article.blade.php`
  - `@extends('layouts.techlog')`
  - Article head: back-link, eyebrow, h1, lead, meta
  - Article layout: aside `.toc` + `.prose` с `{!! $article->content_html !!}`
  - Hero-image: `@if($article->image)` с `/storage/{image}`
  - Tags внизу статьи
  - Комментарии: `@include('includes/comments_list')` и `@include('includes/comments_form')`
  - **Проверка**: `php artisan route:list` → `article.{slug}` возвращает 200, статья отображается

## 7. Pages: Контакты

- [x] 7.1 Переписать `resources/views/contact.blade.php`
  - `@extends('layouts.techlog')`
  - Contact intro: eyebrow, h1, описание, ссылки
  - Contact form: name, email, message, кнопка «Отправить →»
  - Сессии: `session('success')`, `session('error')`
  - **Проверка**: `/contact` возвращает 200, форма отображается

## 8. Controllers: Article

- [x] 8.1 Обновить `ArticleController@index`
  - Поиск: `WHERE title LIKE '%q%' OR content_html LIKE '%q%'`
  - Данные: `Article::published()->with(['user', 'rubric', 'tags'])->paginate(6)`
  - Передача в view: `articles`, `search`
  - **Проверка**: `/?search=архитектура` возвращает отфильтрованные статьи

- [ ] 8.2 Обновить `ArticleController@show`
  - Данные: `$article = Article::with(['user', 'rubric', 'tags'])->where('slug', $slug)->firstOrFail()`
  - Передача в view: `article`, `metaTitle`, `metaDesc`
  - **Проверка**: `/article.{slug}` возвращает 200, правильная статья

- [ ] 8.3 Обновить `ArticleController@showByRubric` и `showByTag`
  - Передача данных в `index` view (новый дизайн)
  - **Проверка**: `/rubric.{slug}` и `/tag.{slug}` возвращают 200

## 9. Controllers: Contact

- [ ] 9.1 Обновить `ContactController@store`
  - Поля: `name`, `email`, `message` (из `ContactRequest`)
  - Сохранение: `Contact::create($validated)`
  - Redirect с `session('success')` или `session('error')`
  - **Проверка**: POST `/contact` создаёт запись в `contacts`

## 10. Controllers: Routing

- [ ] 10.1 Обновить `routes/web.php`
  - Публичный блог: `/`, `article.{slug}`, `contact` → новый дизайн
  - Legacy: `notpublic`, `rubric.{slug}`, `tag.{slug}` → новый дизайн
  - **Проверка**: `php artisan route:list` показывает все маршруты

## 11. Models: Contact

- [ ] 11.1 Обновить `Contact` модель
  - Добавить `name` и `email` в `$fillable`
  - **Проверка**: `Contact::create(['name' => 'Test', 'email' => 'test@test.com', 'message' => 'Hello'])` работает

## 12. Integration: Валидация и сборка

- [ ] 12.1 Запустить `npm run build` и проверить отсутствие ошибок
  - **Проверка**: `public/build/` содержит `techlog-*` файлы

- [ ] 12.2 Проверить все публичные страницы в браузере
  - Главная: hero, masonry, topics, about, newsletter
  - Статья: TOC, prose, tags, комментарии
  - Контакты: форма
  - **Проверка**: Все страницы рендерятся, CSS и JS загружаются, theme-toggle работает

- [ ] 12.3 Проверить мобильную адаптивность (850px, 520px)
  - **Проверка**: Меню открывается, masonry перестраивается, текст читаем

- [ ] 12.4 Проверить legacy-маршруты
  - **Проверка**: `/notpublic` (с auth), `/rubric.{slug}`, `/tag.{slug}` работают

- [ ] 12.5 Проверить, что `base.blade.php` не сломан
  - **Проверка**: Orchid-админка работает, Breeze-страницы работают
