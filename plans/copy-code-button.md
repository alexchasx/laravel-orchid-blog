# План: функционал «Копировать код» для блоков `<pre><code>`

> Статус: черновик для согласования. Реализация не начата.
> Задача — добавить кнопку «Копировать» к блокам кода в статьях блога
> (Laravel 13 + Orchid, контент хранится в БД, рендерится в Blade).
> Все тексты на русском, без привязки к доменам и личным данным (правила
> публичного шаблона).

## 1. Цель и место в архитектуре

- **Что делаем:** у каждого блока кода в теле статьи появляется кнопка
  «Копировать», копирующая чистый текст кода в буфер обмена. Реализация
  **полностью клиентская** — новых маршрутов, контроллеров и изменений
  Orchid/БД нет.
- **Куда встраиваем (конвенция проекта, см. AGENTS.md):**
  - интерактив — [`resources/js/techlog.js`](resources/js/techlog.js) (там уже
    живут тема, модалки, формы подписки/обратной связи, reveal);
  - стили — [`resources/sass/techlog/_components.scss`](resources/sass/techlog/_components.scss)
    (секция `Prose`) и при необходимости
    [`resources/sass/techlog/_responsive.scss`](resources/sass/techlog/_responsive.scss);
  - документация — [`README.md`](README.md) (раздел «✨ Возможности из коробки»)
    и [`AGENTS.md`](AGENTS.md) (раздел Gotchas).
- **Ключевые правила:**
  - кнопки навешиваются на `.prose pre`, поэтому фича автоматически работает
    на странице статьи ([`resources/views/article.blade.php`](resources/views/article.blade.php:40))
    и на любых других `.prose`-блоках без правки Blade;
  - бэкенд не меняется: `content_html` уже генерируется из Markdown
    (`league/commonmark`, `Article::booted()`), GFM-код-блоки дают
    `<pre><code class="language-…">`;
  - копируется `code.textContent` (чистый код), а не HTML;
  - без новых npm-зависимостей (никаких clipboard-пакетов, highlight.js и т.п.);
  - подсветка синтаксиса **не входит** в задачу.

## 2. Точка встраивания в JS

- [ ] В [`resources/js/techlog.js`](resources/js/techlog.js) добавить
  самодостаточный блок инициализации (по образцу существующих секций, до
  закрывающей `})();`).
- [ ] Функция `initCodeCopy()`:
  - `document.querySelectorAll('.prose pre')` — обходим все блоки кода;
  - если блок уже обработан (`.code-block`), пропускаем — идемпотентность;
  - создаём `<button type="button" class="code-copy" aria-label="Скопировать код">Копировать</button>`;
  - оборачиваем `pre` в `<div class="code-block">` (позиционирование кнопки
    относительно блока, а не относительно `pre` со `overflow: auto`);
  - вешаем обработчик клика.

## 3. Логика копирования

- [ ] Основной путь — `navigator.clipboard.writeText(text)`:
  - берём `pre.querySelector('code')?.textContent ?? pre.textContent`;
  - обрезаем завершающий `\n` (`text.replace(/\n$/, '')`);
  - `await` промиса, при успехе — состояние «Скопировано».
- [ ] **Фолбэк** для небезопасных контекстов (http без localhost, где
  `navigator.clipboard` недоступен) — временный `textarea`:
  - `textarea.value = text; textarea.setAttribute('readonly', '')`;
  - `position: fixed; top: -9999px; opacity: 0`, добавить в `body`;
  - `textarea.select()` + `document.execCommand('copy')`, удалить `textarea`.
- [ ] **Обработка ошибки:** если оба способа недоступны/упали — показать на
  кнопке «Не удалось» (или вернуть исходный текст), состояние снимается через
  2 секунды; в консоль — `console.warn`, без падения скрипта.
- [ ] **Состояние «Скопировано»:** текст кнопки меняется на «Скопировано» и
  добавляется класс `.is-copied`; через 2 секунды возвращается исходный текст.
  Таймер хранить на элементе, чтобы повторный клик сбрасывал предыдущий
  (`clearTimeout`) и не «залипал».
- [ ] **Доступность:** `type="button"`, осмысленный `aria-label`,
  доступность с клавиатуры (нативный `<button>`), состояние не только цветом,
  но и текстом.

## 4. Стили (SCSS)

- [ ] В [`resources/sass/techlog/_components.scss`](resources/sass/techlog/_components.scss)
  рядом с `.prose pre` (строка ~601) добавить:

```scss
.code-block {
    position: relative;

    pre { margin: 0; }
}

.code-copy {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 6px 12px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: var(--surface-2);
    color: var(--muted);
    font: 600 var(--fs-xs) "JetBrains Mono", monospace;
    cursor: pointer;
    transition: color .2s ease, border-color .2s ease, background .2s ease;

    &:hover,
    &:focus-visible {
        color: var(--green);
        border-color: var(--green);
    }

    &:focus-visible {
        outline: 2px solid var(--green);
        outline-offset: 2px;
    }

    &.is-copied {
        color: var(--green);
        border-color: var(--green);
    }
}
```

- [ ] Кнопка видна **всегда** (не только по hover) — чтобы работала на
  тач-устройствах; прозрачность/приглушённость в покое и акцент при hover/focus.
- [ ] Светлая тема подхватывается автоматически: используются существующие
  CSS-токены `--surface-2`, `--line`, `--muted`, `--green` из
  [`resources/sass/techlog/_variables.scss`](resources/sass/techlog/_variables.scss)
  (переопределяются в `html.light`).
- [ ] При необходимости скорректировать `pre { padding }` / `padding-top`,
  чтобы кнопка не перекрывала первую строку кода.
- [ ] В [`resources/sass/techlog/_responsive.scss`](resources/sass/techlog/_responsive.scss)
  (мобильный `@media`) при необходимости уменьшить отступы/размер кнопки и
  проверить, что она не перекрывает длинные строки и не мешает горизонтальному
  скроллу `pre`.
- [ ] Проверить наследование: базовый `pre` в
  [`resources/sass/_base.scss`](resources/sass/_base.scss:60) задаёт светлый фон
  `#eee`; убедиться, что `.prose pre` (тёмный фон `#050805`) имеет приоритет и
  кнопка читается в обеих темах.

## 5. Проверка

- [ ] `make frontend-build` — сборка Vite без ошибок.
- [ ] Ручная проверка на странице статьи (`make up`, открыть статью с кодом):
  - кнопка присутствует у **каждого** блока кода, включая блоки без указания
    языка (` ``` ` без `language-…`);
  - копирование кладёт в буфер чистый код без завершающего перевода строки;
  - показывается «Скопировано» и через 2 секунды текст возвращается;
  - повторные быстрые клики не «залипают»;
  - **тёмная и светлая** темы — кнопка читаема, не перекрывает код;
  - мобильный вид (узкий экран) — кнопка доступна, не мешает скроллу блока;
  - при отключённом `navigator.clipboard` (http) срабатывает фолбэк.
- [ ] Проверить, что на прочих `.prose`-страницах
  ([`about`](resources/views/about.blade.php:12), [`privacy`](resources/views/privacy.blade.php:40),
  согласия, ошибки) при отсутствии `pre` скрипт не падает.
- [ ] Регресс: тема, модалки, формы подписки/обратной связи, reveal,
  cookie-баннер работают как прежде (блок добавлен в конец IIFE, порядок
  существующих обработчиков не меняем).

## 6. Документация

- [ ] [`README.md`](README.md:9), раздел «✨ Возможности из коробки» → «Публичная
  часть (Blade + Vite)»: добавить пункт о кнопке «Копировать» у блоков кода
  (клиентская реализация, фолбэк буфера, без зависимостей).
- [ ] [`AGENTS.md`](AGENTS.md), раздел Gotchas: короткая заметка о том, что
  копирование кода — чисто клиентское (в `resources/js/techlog.js`), бэкенд и
  `content_html` не затрагиваются, подсветки синтаксиса нет.

## 7. Явно вне рамок задачи

- Подсветка синтаксиса (highlight.js/Shiki/Prism) — отдельная задача.
- Кнопки «Копировать» в админке Orchid — не требуется.
- Изменения модели `Article`, `ArticleService`, миграций и маршрутов — не
  требуются.
