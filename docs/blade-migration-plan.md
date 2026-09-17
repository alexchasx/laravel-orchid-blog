# План: Вёрстка it-blog-html → Blade-шаблоны

## Контекст

Статическая вёрстка в `it-blog-html/` (HTML + CSS + vanilla JS) должна быть интегрирована
в Laravel-блог как Blade-шаблоны с динамическими данными из моделей `Article`, `Tag`, `Rubric`, `Comment`.

### Что есть сейчас

- **3 страницы**: `index.blade.php`, `article.blade.php`, `contact.blade.php`
- **Layout**: `layouts/base.blade.php` — legacy-меню, sidebar с рубриками, Bootstrap-классы
- **SASS**: модульная структура (`tokens.scss`, `base.scss`, `layout.scss`, `components.scss`, `dark.scss`)
- **Модели**: `Article`, `Rubric`, `Tag`, `Comment`, `Contact`, `User`
- **Контроллеры**: `ArticleController`, `ContactController` — передают данные в views
- **Старый `base.blade`** остаётся как fallback для Orchid и legacy-маршрутов

### Что переносим

| Файл it-blog-html | Blade-шаблон | Описание |
|---|---|---|
| `index.html` | `layouts/techlog.blade.php` + `index.blade.php` | Hero, masonry-сетка, темы, about, newsletter |
| `article.html` | `article.blade.php` | Статья с TOC, hero-image, prose, tags |
| `contact.html` | `contact.blade.php` | Форма контактов (имя, email, сообщение) |
| `styles.css` | `resources/sass/techlog/` | Полная новая палитра, неоморфизм, light/dark |
| `script.js` | `resources/js/techlog.js` | Меню, тема, scroll-reveal, to-top, валидация |

## Этап 1: Foundation — layout, стили, тема, навигация

### 1.1 Новый layout `layouts/techlog.blade.php`

```
┌──────────────────────────────────────┐
│ Header (nav.container)               │
│  - brand (TECH//LOG)                 │
│  - nav-links (Статьи, Темы, О блоге) │
│  - theme-toggle ☼                     │
│  - mobile menu-toggle                │
├──────────────────────────────────────┤
│ @yield('content')                    │
├──────────────────────────────────────┤
│ Footer                               │
│  - copyright                         │
│  - social links                      │
├──────────────────────────────────────┤
│ to-top button ↑                      │
└──────────────────────────────────────┘
```

- Расширяется из `layouts/techlog.blade.php` в страницах
- `<head>` с `@vite`, meta-тегами, OG-разметкой
- `@stack('styles')` и `@stack('scripts')` для страниц-специфичных ассетов
- `lang="ru"` из `it-blog-html`

### 1.2 Миграция стилей

**Структура**: `resources/sass/techlog/`

```
techlog/
├── _variables.scss    -- CSS custom properties из styles.css (:root, html.light)
├── _reset.scss        -- *, box-sizing, html, body
├── _base.scss         -- typography, links, buttons
├── _components.scss   -- header, nav, hero, posts, masonry, topics, article, footer
├── _forms.scss        -- forms, inputs, buttons
├── _utilities.scss    -- container, reveal, skip-link, sr-only
├── _responsive.scss   -- @media queries (850px, 520px, reduced-motion)
└── index.scss         -- entry point (orchestrator)
```

- Перенос `styles.css` → SCSS-модули
- CSS custom properties (`--bg`, `--surface`, `--green`, `--text`, `--muted`, `--line`, `--shadow`)
- `html.light` с переопределением переменных
- Подключение через `@vite('resources/sass/techlog/index.scss')` в `techlog.blade.php`

### 1.3 Миграция JavaScript

**Файл**: `resources/js/techlog.js`

- Мобильное меню (`.menu-toggle` ↔ `.nav-links`)
- Тема (`.theme-toggle` + `localStorage`)
- Scroll reveal (`IntersectionObserver`)
- Кнопка «Наверх» (`.to-top`)
- Валидация форм (`[data-validate]`, `[data-contact-form]`)
- Подключение через Vite в `resources/js/app.js` или как отдельный entry

### 1.4 Подключение через Vite

**Изменения в `vite.config.js`**:

```js
input: [
  'resources/css/app.css',
  'resources/sass/style.scss',       // legacy — остаётся
  'resources/sass/techlog/index.scss', // новая тема
  'resources/js/app.js',
  'resources/js/techlog.js',          // новая логика
],
```

**Изменения в `techlog.blade.php`**:

```blade
@vite([
    'resources/sass/techlog/index.scss',
    'resources/js/techlog.js',
])
```

Legacy-ассеты (`style.scss`, `app.js`) остаются в `base.blade.php`.

## Этап 2: Pages — контентные страницы

### 2.1 Главная `index.blade.php`

```blade
@extends('layouts.techlog')

@section('content')
    <section class="hero">...</section>
    <section class="section" id="articles">
        <div class="masonry">
            @foreach($articles as $article)
                <article class="post">...</article>
            @endforeach
        </div>
    </section>
    <section class="section" id="topics">...</section>
    <section class="about" id="about">...</section>
    <section class="newsletter" id="newsletter">...</section>
@endsection
```

- `featured`-статья (первая в массиве) — с `.post-image`
- Остальные — masonry-grid
- Данные: `$articles = Article::published()->paginate(6)`
- Теги и рубрики для навигации

### 2.2 Статья `article.blade.php`

```blade
@extends('layouts.techlog')

@section('content')
    <article class="article">
        <div class="article-head">...</div>
        <div class="article-layout">
            <aside class="toc">...</aside>
            <div class="prose">{!! $article->content_html !!}</div>
        </div>
    </article>
    @include('includes.comments')
@endsection
```

- TOC генерируется **вручную** через отдельное поле `toc` в админке Orchid
  (список `<a href="#id">Заголовок</a>`)
- `article-hero` — если есть `$article->image`
- Tags внизу статьи
- Комментарии (переиспользовать `includes/comments_list.blade.php` и `comments_form.blade.php`)

### 2.3 Контакты `contact.blade.php`

```blade
@extends('layouts.techlog')

@section('content')
    <section class="contact">
        <div class="contact-intro">...</div>
        <form class="contact-form" data-contact-form>
            <label>Имя <input name="name" required></label>
            <label>Email <input name="email" required></label>
            <label>Сообщение <textarea name="message" required></textarea></label>
            <button type="submit">Отправить →</button>
        </form>
    </section>
@endsection
```

- Форма для гостей (не только авторизованных)
- Поля: `name`, `email`, `message`
- POST → `ContactController@store`
- Сохранение в `Contact` модель (уже есть поля `name`, `email`, `message`)

### 2.4 Обновление контроллеров

**ArticleController**:

```php
public function index(Request $request): View
{
    $query = Article::published()->with(['user', 'rubric', 'tags']);
    
    if ($request->filled('search')) {
        $q = $request->input('search');
        // title LIKE + content_html LIKE (с HTML-тегами, но работает без FULLTEXT)
        $query->where(function ($q2) use ($q) {
            $q2->where('title', 'LIKE', "%{$q}%")
               ->orWhereRaw('content_html LIKE ?', ["%{$q}%"]);
        });
    }
    
    return view('index', [
        'articles' => $query->paginate(6),
        'search' => $request->input('search'),
    ]);
}
```

public function show(Article $article): View
{
    $this->service->checkAccess($article);
    return view('article', [
        'article' => $article->load(['user', 'rubric', 'tags']),
    ]);
}
```

**ContactController**:

```php
public function index(): View
{
    return view('contact');
}

public function store(ContactRequest $request): RedirectResponse
{
    $contact = Contact::create($request->validated());
    // TODO: отправить email-уведомление
    
    return back()->with('success', __('Сообщение отправлено!'));
}
```

### 2.5 Обновление маршрутов

`routes/web.php`:

```php
// Публичный блог — новый дизайн
Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::get('article.{slug}', [ArticleController::class, 'show'])->name('articleShow');
Route::get('contact', [ContactController::class, 'index'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store');

// Legacy (сохраняем)
Route::get('notpublic', [ArticleController::class, 'showNotPublic'])->name('notpublic')
    ->middleware(['auth', 'access:platform.custom.articles']);
Route::get('rubric.{slug}', [ArticleController::class, 'showByRubric'])->name('showByRubric');
Route::get('tag.{slug}', [ArticleController::class, 'showByTag'])->name('showByTag');
```

## Решения

| Вопрос | Решение |
|---|---|
| TOC | Вручную через поле `toc` в админке Orchid |
| Newsletter | Frontend-only валидация, без бэкенда |
| Изображения статей | Хранятся в `public/storage` |
| Legacy-маршруты (`rubric.*`, `tag.*`) | Новый дизайн |
| Пагинация | Стилизовать под новый дизайн |
| Поиск | По title + по контенту (`content_html`) |
| Contact — поля | `name`, `email` в таблице есть |

## Риски

1. **Поиск по контенту** — `content_html` содержит HTML-теги; нужен text-only поиск (strip tags перед WHERE)
2. **TOC вручную** — автор должен добавлять `id` к `<h2>` в markdown-редакторе
3. **Изображения** — `public/storage` требует `php artisan storage:link` или symlink
4. **Masonry-сетка** — CSS `grid` с `grid-template-rows: masonry` не везде поддерживается; нужен `columns`-подход или JS
