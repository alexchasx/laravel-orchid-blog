## Why

Секция «Исследуйте по темам» на главной странице содержит захардкоженный список из 4 тем (Архитектура, Backend, DevOps, AI & Data) со ссылками по slug (`/rubric/architecture` и т.п.). При этом:

- новые рубрики не попадают на главную автоматически;
- пустые рубрики (без статей) остаются в списке и ведут на пустые страницы;
- ссылки по slug при route model binding по `id` не резолвятся (маршрут `showByRubric` биндится по id).

Требование: на главной в списке категорий выводить только те рубрики, у которых есть связанные статьи.

## What Changes

- Секция Topics на главной переводится на динамический список рубрик из БД
- Выводятся только рубрики, у которых есть хотя бы одна **опубликованная** статья (`is_published = true` и `published_at <= now()`, как в `Article::published()`)
- При отсутствии таких рубрик секция Topics полностью скрывается
- Новый метод `ArticleService::getRubricsWithArticles()` — единая выборка рубрик с опубликованными статьями
- Новый View Composer `RubricsComposer`, привязанный к вьюхе `index`, — внедряет `$rubrics` без дублирования в 4 методах контроллера, рендерящих шаблон `index` (главная, поиск, рубрика, тег, notpublic)
- Ссылки строятся через `route('showByRubric', $rubric)` (binding по id), порядок — алфавитный по `title`

## Capabilities

### New Capabilities

Новые capability не вводятся.

### Modified Capabilities

- `frontend-techlog-pages`: раздел **Topics** главной страницы — динамический список рубрик с опубликованными статьями вместо фиксированных 4 тем.

## Impact

**Файлы:**
- `app/Services/ArticleService.php` — новый метод `getRubricsWithArticles()`
- `app/View/Composers/RubricsComposer.php` — новый composer
- `app/Providers/AppServiceProvider.php` — регистрация composer для вьюхи `index`
- `resources/views/index.blade.php` — секция Topics: цикл по `$rubrics` вместо хардкода

**Контроллеры:** не изменяются (данные внедряет composer).

**Тесты:**
- `tests/Feature/PublicPagesTest.php` — новые кейсы: рубрика с опубликованной статьёй видна, рубрика без опубликованных статей скрыта, секция скрыта при пустом списке
