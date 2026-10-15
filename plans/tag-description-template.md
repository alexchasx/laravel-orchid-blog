# План: шаблонное описание меток «Статьи по тегу …»

> Статус: черновик для согласования. Реализация не начата.
> Все тексты на русском, без привязки к домену и личным данным (правила публичного шаблона).

## 1. Цель и место в архитектуре

- **Что делаем:** при создании метки (тега) автоматически подставляем шаблонное
  описание **«Статьи по тегу «{tag}»: обзоры, гайды, примеры кода и не только»**,
  если администратор не ввёл своё. Плюс миграция, которая заполняет этим шаблоном
  **только** уже существующие метки с пустым/NULL `description` (свои описания
  сохраняются — решение согласовано с пользователем).
- **Зачем:** `description` метки используется как `meta description` страницы
  `/tag/{slug}` ([`ArticleController::showByTag()`](app/Http/Controllers/ArticleController.php:90)
  → `metaDesc => $tag->description`). Пустое описание → на страницу метки падает
  дефолт из `config/seo.php`. Шаблон даёт осмысленный SEO-текст «из коробки».
- **Что уже готово (менять не нужно):**
  - поле `tags.description` (varchar 255, nullable) — миграция
    [`2026_09_27_000000_add_description_to_tags_table.php`](database/migrations/2026_09_27_000000_add_description_to_tags_table.php);
  - вывод `description` на странице метки — [`ArticleController::showByTag()`](app/Http/Controllers/ArticleController.php:90);
  - поле «Описание» в модалке метки — [`CreateOrUpdateTag`](app/Orchid/Layouts/CreateOrUpdateTag.php:34);
  - валидация `tag.description` (nullable, string, max:500) — [`TagListScreen::createOrUpdateTag()`](app/Orchid/Screens/Tag/TagListScreen.php:109).
- **Куда встраиваем (конвенция проекта, см. AGENTS.md):**
  - автозаполнение — в событие `saving` модели [`Tag`](app/Models/Tag.php:61)
    (там уже живёт генерация slug; событие покрывает все пути создания: админка,
    сидеры, фабрики, будущий код);
  - единый источник шаблона — статический метод `Tag::defaultDescription()`.
- **Ключевые правила:**
  - шаблон применяется **только при создании** (`!$tag->exists`) и **только если
    описание пустое**; при редактировании существующей метки описание не трогаем;
  - кавычки — русские «ёлочки» (как в коде: `__('Записи с меткой «')`), а не
    прямые `"`, чтобы не ломать стиль проекта;
  - миграция — самодостаточная (raw SQL, без зависимости от модели), т.к. это
    разовый backfill; на свежей установке (`migrate:fresh --seed`) она no-op,
    потому что миграции выполняются до сидеров, а новые метки получают шаблон
    через событие модели.

## 2. Шаблонная фраза — единый источник

- [ ] Добавить в [`app/Models/Tag.php`](app/Models/Tag.php) статический метод:

```php
/**
 * Шаблонное описание метки (SEO: meta description страницы метки).
 */
public static function defaultDescription(string $title): string
{
    return sprintf('Статьи по тегу «%s»: обзоры, гайды, примеры кода и не только', $title);
}
```

- [ ] Метод — единственное место, где задан текст шаблона для **новых** меток.
  В миграции (п. 4) текст продублирован намеренно (raw SQL) — там нужен
  самодостаточный backfill; в обоих местах оставить комментарий-перекрёстную
  ссылку, чтобы шаблон меняли согласованно.

## 3. Автозаполнение при создании (модель `Tag`)

- [ ] В [`Tag::booted()`](app/Models/Tag.php:61) дополнить существующий обработчик
  `saving` (перед логикой slug):

```php
static::saving(function (Tag $tag): void {
    // Шаблонное описание подставляем только при создании и только если не задано вручную.
    if (! $tag->exists && empty($tag->description)) {
        $tag->description = self::defaultDescription($tag->title);
    }

    // ... существующая генерация slug (без изменений)
});
```

- [ ] Проверить совместимость с `Tag::updateOrCreate()` в
  [`TagListScreen::createOrUpdateTag()`](app/Orchid/Screens/Tag/TagListScreen.php:121):
  при создании (`id` отсутствует) `$tag->exists === false` → шаблон подставится;
  при редактировании (`id` есть) `$tag->exists === true` → описание не трогается.
- [ ] Убедиться, что `TagFactory` (создаёт метки без `description`) не ломает
  существующие тесты: тесты не проверяют `description === null`, поэтому
  автозаполнение безопасно.

## 4. Миграция: backfill существующих меток

- [ ] Создать миграцию `database/migrations/2026_10_15_000000_backfill_tag_descriptions.php`
  (дата-префикс по конвенции последних миграций, напр.
  `2026_10_14_000000_add_is_scheduled_to_articles_table.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Заполняет шаблонным описанием метки с пустым/NULL description.
     * Текст шаблона дублирует Tag::defaultDescription() (см. app/Models/Tag.php).
     */
    public function up(): void
    {
        DB::table('tags')
            ->whereNull('description')
            ->orWhere('description', '')
            ->update([
                'description' => DB::raw(
                    "CONCAT('Статьи по тегу «', title, '»: обзоры, гайды, примеры кода и не только')"
                ),
            ]);
    }

    public function down(): void
    {
        // Необратимо: нельзя отличить сгенерированные описания от заданных вручную.
    }
};
```

- [ ] Поведение: обновляются **только** метки с `description IS NULL` или
  `description = ''`; метки со своим описанием не затрагиваются.
- [ ] `down()` — пустой с комментарием (необратимо), это допустимо для data-backfill.
- [ ] На свежей установке миграция ничего не обновляет (меток ещё нет) — это
  нормально, новые метки получают шаблон через событие модели (п. 3).

## 5. UX в админке (опционально, рекомендуется)

- [ ] Обновить placeholder поля «Описание» в
  [`CreateOrUpdateTag`](app/Orchid/Layouts/CreateOrUpdateTag.php:37) на что-то вроде:
  `Оставьте пустым — подставится шаблон: «Статьи по тегу …: обзоры, гайды, примеры кода и не только»`.
- [ ] Никакой логики в layout не добавляем — автозаполнение делает модель.

## 6. Тесты

- [ ] [`tests/Unit/TagModelTest.php`](tests/Unit/TagModelTest.php) — добавить:
  - `test_creating_tag_without_description_gets_template_description()`: создать
    метку `title = 'Laravel'` без `description` → `description === 'Статьи по тегу «Laravel»: обзоры, гайды, примеры кода и не только'`;
  - `test_creating_tag_with_custom_description_keeps_it()`: создать с `description`
    → значение сохраняется, шаблон не подставляется;
  - `test_updating_tag_does_not_overwrite_description()`: создать метку, затем
    обновить `title` → `description` не меняется.
- [ ] [`tests/Feature/TagListScreenTest.php`](tests/Feature/TagListScreenTest.php)
  (новый файл, по образцу `ArticleListScreenTest`) — проверить
  `createOrUpdateTag()` экрана:
  - создание без `tag.description` → в БД шаблонное описание;
  - создание с `tag.description` → своё описание;
  - редактирование существующей метки → описание не перезаписывается.
- [ ] (Опционально) тест миграции: в feature-тесте создать метки с NULL/пустым/своим
  описанием, вызвать `up()` миграции напрямую и проверить, что заполнились только
  пустые. Если тест миграции окажется хрупким — ограничиться ручной проверкой
  (п. 8).

## 7. Документация

- [ ] [`docs/features.md`](docs/features.md) — в раздел «Рубрики и метки» (строка 9)
  или SEO-раздел добавить: «Описание метки (meta description страницы `/tag/{slug}`)
  автозаполняется шаблонной фразой „Статьи по тегу …“ при создании, если не задано
  вручную; существующие метки с пустым описанием заполняются миграцией».
- [ ] [`AGENTS.md`](AGENTS.md) — в секцию Gotchas добавить пункт: автозаполнение
  `Tag::description` шаблоном при создании (событие `saving`, только `!$tag->exists`
  и пустое описание; единый источник — `Tag::defaultDescription()`).

## 8. Проверка

- [ ] `make test` — все тесты (в т.ч. новые) зелёные.
- [ ] `make lint` — `php -l` по `app database routes` без ошибок.
- [ ] Ручная проверка в админке (`/nexus` → Метки):
  - создать метку без описания → в модалке/списке после сохранения описание
    заполнено шаблоном;
  - создать метку со своим описанием → своё сохраняется;
  - отредактировать существующую метку (сменить заголовок) → описание не меняется.
- [ ] Ручная проверка миграции на копии БД: метки с NULL/пустым/своим описанием →
  после `make migrate` заполнены только пустые.
- [ ] Проверить страницу `/tag/{slug}`: `meta description` = шаблонная фраза
  (через DevTools или `curl -s <url> | grep description`).

## 9. Затрагиваемые файлы

| Файл | Действие |
|------|----------|
| [`app/Models/Tag.php`](app/Models/Tag.php) | добавить `defaultDescription()`, дополнить `saving` |
| `database/migrations/2026_10_15_000000_backfill_tag_descriptions.php` | создать |
| [`app/Orchid/Layouts/CreateOrUpdateTag.php`](app/Orchid/Layouts/CreateOrUpdateTag.php) | обновить placeholder (опционально) |
| [`tests/Unit/TagModelTest.php`](tests/Unit/TagModelTest.php) | добавить 3 теста |
| `tests/Feature/TagListScreenTest.php` | создать (тесты экрана) |
| [`docs/features.md`](docs/features.md) | добавить строку о шаблоне |
| [`AGENTS.md`](AGENTS.md) | добавить gotcha |
