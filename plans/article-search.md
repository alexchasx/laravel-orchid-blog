# План: функционал поиска статей (публичный фронтенд)

> Статус: черновик для согласования. Реализация не начата.
> Бэкенд поиска **уже готов** — вся работа в публичном фронтенде
> (Blade + SCSS), без изменений сервиса, контроллера, БД и Orchid.
> Все тексты на русском, без привязки к домену и личным данным
> (правила публичного шаблона).

## 1. Цель и место в архитектуре

- **Что делаем:** даём посетителю **видимый способ искать статьи** и
  приводим страницу результатов к нормальному виду. Сейчас поиск «работает»
  только если вручную открыть `/?search=...`: в новом шаблоне `techlog`
  формы поиска нигде нет.
- **Что уже готово (менять не нужно):**
  - [`ArticleService::getPublic(?string $search)`](app/Services/ArticleService.php:21)
    — фильтрует опубликованные статьи по `title` и `content_html` (`LIKE`),
    пагинация 9;
  - [`ArticleController::index()`](app/Http/Controllers/ArticleController.php:23)
    — читает `?search=`, передаёт `$search`, ставит `metaTitle`
    «Результаты поиска для: …» и `robots = noindex, nofollow` для поиска и
    пагинации;
  - [`layouts/techlog.blade.php`](resources/views/layouts/techlog.blade.php:29)
    — canonical для `?search=` указывает на главную;
  - роут `home` (`GET /`) переиспользуется как страница результатов —
    отдельный маршрут **не создаём**;
  - [`includes/jsonld.blade.php`](resources/views/includes/jsonld.blade.php:42)
    — `WebSite` + `SearchAction` уже указывает на `/?search=`.
- **Куда встраиваем (конвенция проекта, см. AGENTS.md):**
  - форма поиска — [`resources/views/layouts/techlog.blade.php`](resources/views/layouts/techlog.blade.php)
    (шапка, внутри `.nav-links`, рядом с выпадающим меню «Темы»);
  - состояние страницы результатов — [`resources/views/index.blade.php`](resources/views/index.blade.php);
  - стили — [`resources/sass/techlog/_components.scss`](resources/sass/techlog/_components.scss)
    и при необходимости [`_forms.scss`](resources/sass/techlog/_forms.scss) /
    [`_responsive.scss`](resources/sass/techlog/_responsive.scss);
  - тесты — [`tests/Feature/PublicPagesTest.php`](tests/Feature/PublicPagesTest.php).
- **Ключевые правила:**
  - никаких новых маршрутов, контроллеров, миграций и JS — форма обычный
    `GET` на `route('home')`;
  - переиспользуем существующие CSS-токены techlog (`--surface`, `--surface-2`,
    `--line`, `--muted`, `--green`), чтобы автоматически работала светлая тема;
  - H1 на странице ровно один (правило SEO из `docs/architecture.md`, 17.8);
  - фича публичная — админка Orchid и её права не затрагиваются.

## 2. Форма поиска в шапке (`layouts/techlog.blade.php`)

- [ ] Добавить форму внутри `.nav-links` (после ссылки «Статьи» или перед
  «О блоге» — чтобы на десктопе стояла в ряду навигации):

```blade
<form class="nav-search" role="search" method="GET" action="{{ route('home') }}">
    <label class="sr-only" for="nav-search-input">{{ __('Поиск по статьям') }}</label>
    <input id="nav-search-input"
           type="search"
           name="search"
           value="{{ request()->routeIs('home') ? request('search') : '' }}"
           placeholder="{{ __('Поиск по статьям') }} …"
           autocomplete="off">
    <button type="submit" aria-label="{{ __('Найти') }}">⌕</button>
</form>
```

- [ ] `role="search"`, `<label class="sr-only">` (класс уже есть в проекте) и
  `aria-label` у кнопки — доступность.
- [ ] Предзаполнение: `value` берём из `request('search')` **только на
  главной** (`routeIs('home')`), чтобы запрос не «протекал» в шапку других
  страниц.
- [ ] Поведение: обычный `GET` на `route('home')` → попадание на страницу
  результатов (`/?search=...`), JS не нужен.
- [ ] Мобильное меню: `.nav-links` на `<=850px` разворачивается в колонку
  (`_responsive.scss:7`), форма станет во всю ширину — отдельного JS не надо;
  убедиться, что обработчик закрытия меню по клику на ссылку
  ([`techlog.js`](resources/js/techlog.js:100)) не мешает submit формы.
- [ ] Не ломать существующие тесты шапки: `test_header_shows_topics_dropdown_with_rubric_link`,
  `test_header_hides_topics_dropdown_when_no_rubrics_with_articles`.

## 3. Страница результатов (`resources/views/index.blade.php`)

- [ ] В начале секции определить контекст:

```blade
@php($isSearch = request()->routeIs('home') && request()->filled('search'))
```

- [ ] **Hero скрывать при поиске:** условие `@if (($showHero ?? true) && !$isSearch)`
  — на странице результатов промо-блок не нужен.
- [ ] **Заголовок:** единственный `<h1>` на странице результатов:
  - при поиске — `<h1>Результаты поиска по запросу «{search}»</h1>` и
    количество найденного (`$articles->total()`) — например, «Найдено: N»;
  - без поиска — как сейчас: на главной `<h2>Свежие статьи</h2>`, на
    рубрике/теге `<h1>{sectionTitle}</h1>` (не трогаем).
- [ ] **Ссылка «Все статьи →»** в `.section-head` при поиске ведёт на
  `route('home')` и фактически сбрасывает поиск — оставить, но при желании
  подписать «Сбросить поиск». Не дублировать заголовок.
- [ ] **Пустое состояние:** сейчас текст «Ничего не нашлось». Улучшить:
  - при поиске: «По запросу «{search}» ничего не найдено. Попробуйте изменить
    запрос.» + ссылка «Ко всем статьям»;
  - сохранить текущую ветку `@empty` для остальных случаев.
- [ ] **Пагинация сохраняет `?search=`:** вызывать `$articles->withQueryString()`
  перед `->links()` (или `->appends(['search' => request('search')])`), иначе
  переход на стр. 2 теряет запрос. Проверить, что это не ломает рубрику/тег
  (у них параметров в query нет — поведение не меняется).
- [ ] Не забыть: featured-карточка (`$index === 0`) на результатах поиска
  допустима, менять masonry-разметку не нужно.

## 4. Стили (SCSS)

- [ ] В [`_components.scss`](resources/sass/techlog/_components.scss) рядом с
  блоком навигации/шапки добавить `.nav-search`:

```scss
.nav-search {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid var(--line);
    border-radius: 999px;
    background: var(--surface-2);
    padding: 4px 6px 4px 14px;
    transition: border-color .2s ease;

    &:focus-within { border-color: var(--green); }

    input {
        border: 0;
        background: transparent;
        color: var(--text);
        font-size: var(--fs-sm);
        padding: 6px 0;
        width: 150px;
        outline: 0;

        &::placeholder { color: var(--muted); }
    }

    button {
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--muted);
        cursor: pointer;
        transition: color .2s ease, background .2s ease;

        &:hover,
        &:focus-visible {
            color: var(--green);
            background: var(--surface);
        }

        &:focus-visible { outline: 2px solid var(--green); outline-offset: 2px; }
    }
}
```

- [ ] Использовать только существующие токены — светлая тема
  (`html.light`) подхватится автоматически.
- [ ] В [`_responsive.scss`](resources/sass/techlog/_responsive.scss) на
  мобильном (`@media (max-width: 850px)`) сделать `.nav-search` шириной 100%
  и `input { width: 100%; flex: 1; }`, чтобы форма аккуратно встала в
  развёрнутое меню-колонку.
- [ ] Убедиться, что поле не «схлопывается» между длинными пунктами меню на
  средних экранах (при необходимости — `flex-shrink` / перенос).

## 5. Тесты (`tests/Feature/PublicPagesTest.php`)

Исходные тесты поиска уже есть (`test_home_page_search_filters_by_title`).
Добавляем проверки именно фронтенда:

- [ ] Форма поиска есть в шапке: `assertSee('name="search"', false)` и
  `assertSee('role="search"', false)` на `GET /`.
- [ ] Страница результатов показывает заголовок: на `GET /?search=Laravel`
  `assertSee('Результаты поиска', false)` и виден текст запроса.
- [ ] Hero скрыт при поиске: `assertDontSee('class="hero container', false)`
  на `GET /?search=Laravel`.
- [ ] `?search=` сохраняется в пагинации: создать > 9 подходящих статей,
  запросить `/?search=...`, `assertSee('search=', false)` в ссылках пагинации.
- [ ] Пустое состояние: `GET /?search=неттакоготекста` показывает сообщение
  «ничего не найдено».
- [ ] Не ломать существующие: `test_home_page_keeps_default_articles_heading`
  (`<h2>Свежие статьи</h2>`), рубрика/тег (`<h1>…</h1>`), `test_home_page_hides_hero_section`.

## 6. Документация

- [ ] [`README.md`](README.md:18) — пункт «Поиск» уже заявлен как работающий
  «из коробки»; после появления формы он станет правдой. При необходимости
  уточнить формулировку (поиск доступен из шапки).
- [ ] [`AGENTS.md`](AGENTS.md) / [`docs/architecture.md`](docs/architecture.md)
  (раздел 10 «Публичный фронтенд») — кратко отметить форму поиска в шапке и
  страницу результатов на `route('home')` при `?search=`.

## 7. Проверка

- [ ] `make frontend-build` — сборка Vite без ошибок.
- [ ] `make test` — все тесты зелёные (включая существующие).
- [ ] Ручная проверка (`make up`, сайт `:8080`):
  - поиск из шапки на десктопе и в мобильном меню;
  - запрос с результатами → заголовок, количество, пагинация с сохранением
    `?search=`;
  - запрос без результатов → понятное пустое состояние и ссылка к статьям;
  - тёмная и светлая темы — поле и кнопка читаемы;
  - на странице рубрики/тега запрос поиска в шапке не предзаполняется.

## 8. Явно вне рамок задачи

- Изменения `ArticleService`, `ArticleController`, маршрутов и БД — не нужны
  (бэкенд готов).
- Полнотекстовый поиск MySQL (`MATCH … AGAINST`), сортировка по релевантности,
  подсветка совпадений, автодополнение (AJAX) — отдельные задачи.
- Поиск по рубрикам/тегам/пользователям — не требуется.
