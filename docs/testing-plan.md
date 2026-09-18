# План: юнит- и feature-тесты для публичной части сайта

## Контекст

Публичная часть: `ArticleController`, `ContactController`, `CommentController`, `MainController`, модели `Article`, `Tag`, `Rubric`, `Comment`, `Contact`, сервис `ArticleService`. Текущие тесты — только Breeze-заготовки, доменной логики не покрывают.

## Шаг 1. Предусловия

- Создать БД `testing` в MySQL-контейнере (нет в `blog_db`):
  ```bash
  docker exec blog_db mysql -uroot -proot -e \
    "CREATE DATABASE IF NOT EXISTS testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  ```
- Запуск тестов — только внутри `blog_app` (хостовая PHP 8.4 < `^8.5`, а `.env` указывает `DB_HOST=db`, который резолвится только в docker-сети; внутри контейнера `phpunit.xml` переопределяет `DB_DATABASE=testing`, остальные параметры `.env`: root/root):
  ```bash
  docker exec -w /var/www blog_app php artisan test
  ```
- `public/build/manifest.json` существует — `@vite` в тестах рендерится нормально.

## Шаг 2. Исправления в коде (утверждены)

1. **`app/Models/Tag.php:83`** — `Tag::updateCountArticles()` ищет тег по id статьи в таблице `tags` → `ModelNotFoundException` при сохранении статьи на пустой БД. Исправить:
   ```php
   $tags = $article->tags()->get();   // вместо self::query()->findOrFail($article->id)->get()
   ```
2. **`app/Services/ArticleService.php:49`** — `ArticleService::checkAccess()` пропускает гостей для неопубликованных статей. Исправить:
   ```php
   if (!$article->is_published && (!($user = Auth::user()) || !$user->isAdmin())) abort(403);
   ```
3. **`routes/web.php:25-27`** — комментарии:
   - добавить `auth` middleware группе `CommentController` (rebuild: `Route::middleware('auth')->controller(CommentController::class)->group(...)`) — `store()` крашится для гостя (`Auth::user()->id` на null);
   - удалить мёртвый route `Route::post('comment.update', 'update')` — метод `update()` в контроллере не существует.

## Шаг 3. Feature-тесты (`tests/Feature/`, ~20 тестов)

### `PublicPagesTest.php`
| Тест | Суть |
|---|---|
| `test_home_page_returns_ok` | GET `/` → 200, содержит title первого опубликованного заголовка |
| `test_home_page_shows_only_published_articles` | Неопубликованные статьи не отображаются на главной |
| `test_home_page_search_filters_by_title` | `?search=` фильтрует по `title` |
| `test_article_show_page` | GET `/article/{slug}` → 200, title и контент в ответе |
| `test_guest_cannot_view_unpublished_article` | Гость → 403 для неопубликованной |
| `test_admin_can_view_unpublished_article` | Админ → 200 для неопубликованной |
| `test_show_by_rubric` | GET `/rubric/{id}` → только статьи данной рубрики |
| `test_show_by_tag` | GET `/tag/{id}` → только статьи с указанным тегом |
| `test_set_locale` | GET `/setlocale/ru` → редирект + `session('user_locale') == 'ru'` |

### `ContactPageTest.php`
| Тест | Суть |
|---|---|
| `test_contact_page_returns_ok` | GET `/contact` → 200 |
| `test_store_valid_data_redirects_with_success` | POST валидными данными → redirect + flash success + запись в БД |
| `test_store_valid_data_json_returns_json` | POST с `Accept: application/json` → JSON `{success: true}` |
| `test_store_invalid_data_returns_validation_errors` | POST без name → 302 + ошибки валидации |
| `test_store_with_authenticated_user_sets_user_id` | `actingAs(user)` → `Contact.user_id` == id юзера |

### `CommentTest.php`
| Тест | Суть |
|---|---|
| `test_auth_user_can_store_comment` | `actingAs(user)` + POST `/comment.create` → redirect + комментарий создан |
| `test_guest_cannot_store_comment` | Гость → редирект на login |
| `test_store_comment_with_empty_body_fails_validation` | POST без `comment` → ошибка валидации |
| `test_delete_comment` | DELETE `/delete.{id}` → комментарий soft-deleted |

### `UnpublishedArticlesPageTest.php`
| Тест | Суть |
|---|---|
| `test_guest_redirected_to_login` | GET `/notpublic` гостем → 302 login |
| `test_regular_user_gets_403` | Обычный user → 403 |
| `test_admin_sees_unpublished_articles` | User с `permissions = ['platform.custom.articles' => true]` → 200 |

## Шаг 4. Unit-тесты (`tests/Unit/`, ~25 тестов)

### `ArticleModelTest.php`
- `test_published_scope_returns_only_published_articles` — is_published=true и published_at<=now
- `test_published_scope_excludes_future_articles` — published_at в будущем не попадает
- `test_published_scope_excludes_unpublished`
- `test_auto_slug_generation_from_title`
- `test_slug_uniqueness_on_save` — дубликат ⇒ `slug-1`
- `test_content_raw_converts_to_html_on_save` — markdown → HTML
- `test_search_scope_filters_by_title`
- `test_user_rubric_tags_comments_relations`

### `ArticleServiceTest.php`
- `test_get_public_returns_only_published_paginated` (12 на страницу)
- `test_get_not_public_returns_only_unpublished`
- `test_get_by_rubric_filters_correctly`
- `test_get_by_tag_filters_correctly`
- `test_check_access_aborts_403_for_guest_viewing_unpublished`
- `test_check_access_aborts_403_for_user_viewing_unpublished`
- `test_check_access_allows_admin_viewing_unpublished`
- `test_check_access_allows_published_for_everyone`
- `test_with_toc_extracts_h2_h3_items`
- `test_with_toc_adds_unique_ids`
- `test_with_toc_honors_existing_id` — `<h2 id="custom">` сохраняет id
- `test_with_toc_empty_content_returns_empty_toc`

### `TagModelTest.php`
- `test_article_published_scope_returns_active_tags_with_published_articles`
- `test_article_published_scope_excludes_inactive_tags`
- `test_article_published_scope_excludes_tags_without_articles`

### `RubricModelTest.php`
- `test_article_published_scope_returns_rubrics_with_published_articles`
- `test_article_published_scope_excludes_empty_rubrics`

### `CommentModelTest.php`
- `test_belongs_to_article` / `test_belongs_to_user`
- `test_fillable_fields`
- `test_soft_deletes`

## Шаг 5. Проверка

- `docker exec -w /var/www blog_app php artisan test` — все тесты зелёные (существующие Breeze-тесты + новые)
- `make lint` для изменённых файлов (php -l)

## Замечания

- Фабрики имеют жёсткие FK: `ArticleFactory` (rubric_id 1–3, user_id 1–5), `CommentFactory` (article_id 1–15) — в тестах переопределять через аргументы `create([...])`.
- Теги к статье в тестах — через `$article->tags()->attach($tag->id)`.
- Админ в тестах: `User::factory()->create(['permissions' => ['platform.custom.articles' => true]])` (метод `hasAccess` у Orchid проверяет ключ массива `permissions`).
- `ContactFactory` отсутствует (модель подключена к HasFactory) — создавать `Contact` через `Contact::create([...])`, не через фабрику.