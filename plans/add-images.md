Вот промт, который можно скормить AI-ассистенту (Cursor, Claude, ChatGPT) для реализации загрузки изображений к статьям в этом Laravel-блоге. Он учитывает текущую архитектуру проекта из плана: Blade-шаблоны, `Article`-модель с полем `image`, `Storage::url()`, SEO-требования (абсолютные URL, og:image, JSON-LD, sitemap) и Core Web Vitals (lazy-loading, размеры, WebP).

---

Промт ниже — копируйте целиком:

---

### Реализуй функционал загрузки изображений к статьям для Laravel-блога

**Контекст проекта.** Laravel 11, Blade-шаблоны, SCSS. Структура:
- `app/Models/Article.php` — модель статьи, есть поле `image` (string, nullable), связи `user_id`, `rubric_id`, `tags`.
- `resources/views/layouts/techlog.blade.php` — главный layout, мета-теги (OG, Twitter, JSON-LD).
- `resources/views/includes/jsonld.blade.php` — JSON-LD Article-разметка, использует `image` поле.
- `app/Http/Controllers/SitemapController.php` — sitemap с `<image:image>`.
- `app/Support/Seo.php` — хелпер `absoluteUrl()` для приведения URL к абсолютному виду.
- `Storage::url()` используется для отдачи изображений, диск `public`.
- nginx обслуживает `/storage` напрямую.

**Задача.** Создать полноценный функционал загрузки изображений к статьям: превью-картинка (hero) для статьи и загрузка изображений внутрь контента статьи через WYSIWYG/загрузчик.

### Требования

#### 1. Загрузка hero-изображения статьи

- Форма загрузки в админ-панели (илиFilament/nova-ресурс, или обычная Blade-форма — ориентируйся на существующий подход в проекте).
- Поддерживаемые форматы: JPEG, PNG, WebP. Максимальный размер — 5 МБ.
- При загрузке:
  - Сохранить оригинал в `storage/app/public/articles/{article_id}/original.{ext}`.
  - Сгенерировать 3 варианта (через `intervention/image` или `spatie/laravel-medialibrary`):
    - **large**: 1600px по длинной стороне, WebP, качество 82 — для статьи.
    - **medium**: 800px по длинной стороне, WebP, качество 80 — для карточек в списке.
    - **thumbnail**: 400px по длинной стороне, WebP, качество 75 — для OG-превью и sitemap.
  - Сохранить в `articles/{article_id}/large.webp`, `medium.webp`, `thumbnail.webp`.
  - В поле `Article::image` сохранить относительный путь к medium-варианту (например, `articles/42/medium.webp`).
- При обновлении изображения — удалить старые файлы (все варианты + оригинал).
- При удалении статьи — каскадно удалить всю папку `articles/{article_id}/`.

#### 2. Alt-текст

- Добавить миграцию: поле `image_alt` (string, nullable) в таблицу `articles`.
- В форме загрузки — поле «Alt-текст изображения» с подсказкой: описать для скринридеров и SEO.
- Если `image_alt` пустой — выводить заголовок статьи как fallback в шаблонах.

#### 3. Вывод в шаблонах

- `resources/views/article.blade.php` — hero-изображение:
  ```html
  <img
    src="{{ \App\Support\Seo::absoluteUrl(\Illuminate\Support\Facades\Storage::url($article->image)) }}"
    alt="{{ $article->image_alt ?? $article->title }}"
    width="1600" height="900"
    loading="eager"
    fetchpriority="high"
    decoding="async"
  >
  ```
  Если изображение не загружено — не выводить `<img>`, оставить существующий градиент-заглушку.
- `resources/views/index.blade.php` — карточка статьи в списке:
  ```html
  <img
    src="{{ \App\Support\Seo::absoluteUrl(\Illuminate\Support\Facades\Storage::url(str_replace('medium.webp', 'thumbnail.webp', $article->image))) }}"
    alt="{{ $article->image_alt ?? $article->title }}"
    width="400" height="225"
    loading="lazy"
    decoding="async"
  >
  ```
  Или добавить отдельное поле/accessor для thumbnail-пути вместо `str_replace`.

#### 4. SEO-интеграция

- `techlog.blade.php`: `og:image` и `twitter:image` — использовать `thumbnail.webp` через `Seo::absoluteUrl(Storage::url(...))`. Если изображения нет — fallback на `config('seo.og_image')`.
- `jsonld.blade.php`: `image.url` — `large.webp`, `image.width` = 1600, `image.height` = 900. Если нет изображения — не выводить блок `image`.
- `SitemapController.php`: `<image:url>` — `thumbnail.webp` через `Seo::absoluteUrl()`.
- Все URL — абсолютные, через существующий хелпер `App\Support\Seo::absoluteUrl()`.

#### 5. Валидация

- Request-класс `StoreArticleRequest` / `UpdateArticleRequest` (или существующий):
  - `image` — `nullable|image|mimes:jpeg,png,webp|max:5120`.
  - `image_alt` — `nullable|string|max:255`.

#### 6. Загрузка изображений в контент статьи (опционально, отдельным эндпоинтом)

- Если в проекте есть WYSIWYG-редактор (Trix, TipTap, EasyMDE) — добавить endpoint `POST /admin/articles/{article}/upload-image`:
  - Принимает `file` (image), валидация как выше.
  - Сохраняет в `storage/app/public/articles/{article_id}/content/{timestamp}.webp`.
  - Возвращает JSON `{ url: "..." }` с абсолютным URL для вставки в редактор.
  - Конвертирует в WebP автоматически.
- Если WYSIWYG нет — пропустить этот пункт, оставить только hero-изображение.

#### 7. Миграции

```php
Schema::table('articles', function (Blueprint $table) {
    $table->string('image_alt')->nullable()->after('image');
});
```

#### 8. Тесты

- `tests/Feature/ArticleImageTest.php`:
  - Загрузка валидного изображения → файл создан, `Article::image` заполнен, 3 варианта + оригинал существуют.
  - Загрузка невалидного файла (txt, > 5 МБ) → ошибка валидации.
  - Обновление изображения → старые файлы удалены.
  - Удаление статьи → папка `articles/{id}` удалена.
  - Страница статьи с изображением → `og:image` содержит абсолютный URL с `thumbnail.webp`.
  - Страница статьи без изображения → `og:image` fallback на `config('seo.og_image')`.

#### 9. Очереди

- Конвертация изображений (3 размера + WebP) — через queued job `ProcessArticleImage`, если пакет `intervention/image` ставится. Для маленьких файлов можно синхронно. Добавить connection `sync` по умолчанию с возможностью переключить на `redis`/`database`.

### Не использовать

- Сторонние CDN (Cloudinary, Imgix и т. п.) — только локальный Storage.
- Сторонние JS-виджеты загрузки, если в проекте уже есть подходящий подход.

### Выдай

1. Миграцию.
2. Service-класс `ArticleImageService` (загрузка, конвертация, удаление).
3. Обновлённые контроллеры/requests с валидацией.
4. Обновлённые Blade-шаблоны (`article.blade.php`, `index.blade.php`, `techlog.blade.php`, `jsonld.blade.php`).
5. Обновлённый `SitemapController`.
6. Тесты.
7. Краткое описание изменений и что нужно добавить в `composer.json` / `php artisan` команды.
