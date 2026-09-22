## 1. Metadata изменения

- [x] 1.1 Создать `.openspec.yaml` в каталоге изменения (не создан автоматически)
  - Содержимое: `schema: spec-driven` и `created: 2026-09-22`
  - **Проверка**: файл существует рядом с `proposal.md`

## 2. Данные: метод выборки рубрик

- [x] 2.1 В `app/Services/ArticleService.php` добавить метод `getRubricsWithArticles(): \Illuminate\Database\Eloquent\Collection`
  - Добавить импорт `App\Models\Rubric`
  - Запрос: `Rubric::query()->whereHas('articles', static function (Builder $q): void { $q->where('published_at', '<=', now())->where('is_published', true); })`
  - Сортировка: `orderBy('title')`, затем `->get()`
  - **Проверка**: `make php` + REPL или tinker: метод возвращает только рубрики с опубликованными статьями

## 3. View Composer

- [x] 3.1 Создать `app/View/Composers/RubricsComposer.php`
  - Класс `RubricsComposer` в namespace `App\View\Composers`
  - Конструктор с зависимостью `App\Services\ArticleService`
  - Метод `compose(View $view): void` — `$view->with('rubrics', $this->service->getRubricsWithArticles())`
  - **Проверка**: класс резолвится контейнером без ошибок

- [x] 3.2 Зарегистрировать composer в `app/Providers/AppServiceProvider::boot()`
  - Добавить импорты `Illuminate\Support\Facades\View` и `App\View\Composers\RubricsComposer`
  - Вызов: `View::composer('index', RubricsComposer::class)`
  - **Проверка**: на главной доступна переменная `$rubrics` с коллекцией рубрик

## 4. Шаблон: секция Topics

- [x] 4.1 В `resources/views/index.blade.php` заменить хардкод-блок Topics (секция `#topics`, ~строки 82–112) на цикл по `$rubrics`
  - Сохранить разметку секции: `.section-head`, `.topic-grid`, карточки `.topic.reveal`
  - Номер карточки: `str_pad($i + 1, 2, '0', STR_PAD_LEFT)`
  - Заголовок: `$rubric->title`; подпись `<small>`: `$rubric->description`
  - Ссылка: `route('showByRubric', $rubric)`
  - Оборачивающее условие: `@if ($rubrics->isNotEmpty()) ... @endif`
  - **Проверка**: главная отображается; рубрики с опубликованными статьями видны, пустые — нет; секция скрыта при пустом списке

## 5. Тесты

- [x] 5.1 В `tests/Feature/PublicPagesTest.php` добавить тест «рубрика с опубликованной статьёй отображается на главной»
  - Создать рубрику с опубликованной статьёй через `createPublishedArticle`
  - `GET /` → `assertSee` названия рубрики и `assertOk`
  - **Проверка**: `make test` зелёный

- [x] 5.2 Добавить тест «рубрика без опубликованных статей скрыта»
  - Создать пустую рубрику и рубрику только с черновиком (`is_published = false`)
  - `GET /` → `assertDontSee` названий обеих рубрик
  - **Проверка**: `make test` зелёный

- [x] 5.3 Добавить тест «секция Topics скрыта, когда подходящих рубрик нет»
  - Без рубрик с опубликованными статьями
  - `GET /` → `assertDontSee('Исследуйте по темам')`
  - **Проверка**: `make test` зелёный

## 6. Финальная проверка

- [x] 6.1 Прогнать весь набор тестов: `make test`
  - **Проверка**: все тесты зелёные (включая существующие `PublicPagesTest`, `UnpublishedArticlesPageTest`)

- [x] 6.2 Ручная проверка главной в браузере (`:8080`)
  - Секция «Исследуйте по темам» показывает только рубрики со статьями
  - Клик по карточке ведёт на рабочий список статей рубрики
  - **Проверка**: страница и переходы работают
