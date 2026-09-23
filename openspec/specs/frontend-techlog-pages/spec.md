# frontend-techlog-pages

## Purpose

Контентные страницы публичного блога: главная (hero, masonry-сетка статей, newsletter), статья (hero-image, TOC, prose, tags, комментарии), контакты (имя, email, сообщение).

## Requirements

### Requirement: Главная страница

Страница `index` **SHALL** содержать секции:

- **Hero**: заголовок, описание, CTA-кнопки («Читать статьи», «Подписаться»), terminal-note, code-card (визуальный элемент)
- **Articles**: masonry-сетка из 6 статей (`paginate(6)`). Первая статья — `.featured` с изображением
- **About**: описание блога
- **Newsletter**: форма подписки (frontend-only)

Секции «Темы» на главной нет — список рубрик вынесен в выпадающее меню хедера (см. `frontend-techlog-layout`).

Данные: `$articles = Article::published()->with(['user', 'rubric', 'tags'])->paginate(6)`

#### Scenario: Статьи отображаются

- **WHEN** на главной загружены статьи
- **THEN** первая статья отображается с `.featured` и `.post-image`, остальные — в masonry-сетке

#### Scenario: Пустая главная

- **WHEN** нет опубликованных статей
- **THEN** отображается сообщение «Ничего не нашлось»

#### Scenario: Пагинация

- **WHEN** статей больше 6
- **THEN** отображается пагинация с номерами страниц

### Requirement: Страница статьи

Страница `article.{slug}` **SHALL** содержать:

- **Article head**: ссылка «Назад к статьям», eyebrow (категория + дата), заголовок, lead-описание, мета-информация (время чтения, автор)
- **Article layout**: TOC (aside) + prose (основной контент)
- **Hero-image**: если `$article->image` существует (`/storage/{image}`)
- **Tags**: хэштеги внизу статьи
- **Комментарии**: список + форма (переиспользовать `includes/comments_list.blade.php` и `includes/comments_form.blade.php`)

Данные: `$article = Article::with(['user', 'rubric', 'tags'])->where('slug', $slug)->firstOrFail()`

#### Scenario: Статья с изображением

- **WHEN** у статьи есть `$article->image`
- **THEN** отображается `.article-hero` с фоновым изображением из `/storage/{image}`

#### Scenario: Статья без изображения

- **WHEN** у статьи нет `$article->image`
- **THEN** `.article-hero` не отображается

#### Scenario: TOC отображается

- **WHEN** у статьи есть поле `toc`
- **THEN** aside `.toc` содержит ссылки `<a href="#id">` на заголовки статьи

#### Scenario: Комментарии отображаются

- **WHEN** у статьи есть комментарии
- **THEN** отображается список с именами, датами и текстом комментариев

### Requirement: Страница контактов

Страница `contact` **SHALL** содержать:

- **Contact intro**: eyebrow, заголовок, описание, ссылки на email и GitHub
- **Contact form**: поля name, email, message; кнопка «Отправить сообщение →»
- Форма использует `data-contact-form` для валидации

#### Scenario: Форма контактов отображается

- **WHEN** пользователь открывает `/contact`
- **THEN** отображается форма с полями name, email, message

#### Scenario: Сообщение об успехе

- **WHEN** форма успешно отправлена
- **THEN** отображается сессия `session('success')`

#### Scenario: Сообщение об ошибке

- **WHEN** форма не отправлена
- **THEN** отображается сессия `session('error')`
