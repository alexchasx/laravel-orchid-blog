# Инвентаризация Blade-шаблонов и CSS-классов (Фаза 0, задача 1.1)

Дата: 2026-09-14
Изменение: `2026-09-14-frontend-design-foundation`
Метод: ручной разбор публичных Blade-шаблонов и сопоставление селекторов `resources/sass/style.scss`.

## 1. Публичные шаблоны (подключают `style.scss` через `layouts.base`)

Шаблоны, наследующие `layouts/base.blade.php` (единственный макет, который подключает `@vite(['resources/sass/style.scss', ...])`):

| Шаблон | Назначение |
|---|---|
| `layouts/base.blade.php` | Базовый макет: шапка, меню, `.main_block`, `.wrap`, футер |
| `index.blade.php` | Главная — список статей + пагинация |
| `article.blade.php` | Страница статьи + комментарии |
| `contact.blade.php` | Обратная связь |
| `errors/404.blade.php` | Ошибка 404 (через `errors/template.blade.php`) |
| `errors/template.blade.php` | Общий шаблон ошибок |
| `includes/sidebar.blade.php` | Сайдбар: поиск, рубрики, теги |
| `includes/comments_list.blade.php` | Список комментариев |
| `includes/comments_form.blade.php` | Форма комментария |
| `includes/publication_date.blade.php` | Дата публикации, ID, теги |
| `includes/meta_tags.blade.php` | Мета-теги |
| `vendor/pagination/default.blade.php` | Пагинация (публичная часть) |

**Вне скоупа** (Breeze/Tailwind, подключают `resources/css/app.css`, а не `style.scss`): `layouts/app.blade.php`, `layouts/guest.blade.php`, `layouts/navigation.blade.php`, `auth/*`, `profile/*`, `dashboard.blade.php`, `components/*`, `welcome.blade.php`.

## 2. Используемые классы из `style.scss`

| Селектор в `style.scss` | Где используется |
|---|---|
| `html`, `body` | базовые элементы (все страницы) |
| `ul`, `li` | базовые элементы (все страницы) |
| `a` | ссылки (все страницы) |
| `a.user_name` | `base.blade.php` (имя авторизованного пользователя) |
| `h1`–`h5`, `code`, `pre` | контент статей, заголовки |
| `.main_block` | `base.blade.php` |
| `.wrap` | `base.blade.php` (условно при наличии рубрик) |
| `.clearfix` | `base.blade.php` (меню), `comments_list.blade.php` |
| `.nav_wrap`, `nav .nav_wrap` | `base.blade.php` |
| `.left`, `.right` | `base.blade.php` (пункты меню) |
| `.menu`, `.menu li a`, `.menuToggle`, `#menuCheck` | `base.blade.php` (бургер-меню) |
| `.left_block`, `.right_block` | `base.blade.php` |
| `.sub_logo` | `base.blade.php` |
| `.link` | `base`, `index` (нет), `sidebar`, `contact`, `comments_form`, `errors/template` |
| `.category_link` | `sidebar.blade.php` |
| `.continue_read` | `index.blade.php` |
| `.article_id` | `publication_date.blade.php` |
| `.publication_date` | `publication_date.blade.php` (и через includes в index/article/comments) |
| `.footer`, `.made_in` | `base.blade.php` |
| `.tag_title` | `publication_date.blade.php` |
| `.article_card`, `.article_card li`, `.article_card ul` | `index`, `article`, `comments_form`; списки в `content_html` |
| `.article_card_index` | `index.blade.php` |
| `.left_block h2/h3/h4` | `base.blade.php` (основная колонка) |
| `.submit` | `comments_form.blade.php` |
| `.form-group` | `contact.blade.php` |
| `ul.pagination`, `ul.pagination li a`, `ul.pagination li span`, `ul.pagination li` | `index.blade.php` + `vendor/pagination/default.blade.php` |
| `textarea` | `contact.blade.php`, `comments_form.blade.php` |
| `.textwrapper` | `comments_form.blade.php` |
| `.invalid-feedback` | `comments_form.blade.php` |
| `input.contact` | `contact.blade.php` |
| `.comment_user` | `comments_list.blade.php` |
| `.comments > h3` | `comments_list.blade.php` |
| `.pageTitle`, `.article_title` | `base.blade.php` (pageTitle), `index.blade.php` (article_title) |
| `.caret` | `base.blade.php` (меню) |
| `.category_block > h3`, `#tags` | `sidebar.blade.php` |
| `.tags_link` | `sidebar.blade.php` |
| `.error-page`, `.error-title` | `errors/template.blade.php` |
| `.alert`, `.alert-success`, `.alert-error` | `base.blade.php` (alert-success), `contact.blade.php` (success/error) |
| `.search_submit` | `sidebar.blade.php` |

## 3. Мёртвые классы (определены в `style.scss`, не встречаются в шаблонах)

| Селектор | Примечание |
|---|---|
| `.hr-lines` | не используется ни в одном шаблоне |
| `.logo` | в шаблоне используется только `id="logo"`, класса нет → правило не срабатывает |
| `.tag` | в шаблонах только `.tag_title`; сам класс `.tag` отсутствует |
| `.wrap_img`, `.wrap_img img` | нет в шаблонах; возможно, генерируется контентом статей (`content_html`) — требуется точечная проверка |
| `.align-left`, `.align-rigt` | не используются; вдобавок опечатка `rigt` (правильно `right`) |
| `.star` | не используется |

## 4. Классы в шаблонах без соответствующих стилей

Эти классы встречаются в публичных шаблонах, но в `style.scss` (и других подключаемых стилях) для них нет правил:

| Класс | Шаблон | Примечание |
|---|---|---|
| `.menu_block` | `base.blade.php` | контейнер меню, стилей нет |
| `.login` (a.login) | `base`, `contact`, `comments_form` | стилей нет |
| `.mb-0 .rounded-0 .text-center .small .py-2` | `base.blade.php` | Bootstrap-утилиты, Bootstrap не подключён |
| `.response-text-left`, `.response-text-right` | `comments_list.blade.php` | стилей нет |
| `.comment-form`, `.comment-form-comment`, `.form-submit` | `comments_form.blade.php` | стилей нет (только `.submit`) |
| `.search-form`, `.search-field`, `.screen-reader-text` | `sidebar.blade.php` | стилей нет (кроме `.search_submit`) |
| `.category_list`, `.tags_link_title` | `sidebar.blade.php` | стилей нет |
| `.error-content` | `errors/template.blade.php` | стилей нет |
| `.container .row .col-md-* .col-md-offset-* .panel .panel-default .panel-body .form-horizontal .has-error .control-label .form-control .help-block .btn .btn-primary` | `contact.blade.php` | Bootstrap-классы без подключённого Bootstrap (легаси) |
| `.disabled`, `.active` | `vendor/pagination/default.blade.php` | стилей нет |

## 5. Зафиксированные дефекты `style.scss`

1. **`@media (max-widht: ...)`** — опечатка `max-widht` (строки 86–105): все медиазапросы `.wrap` не работают.
2. **`.main_block { max-width: 1170; }`** — отсутствуют единицы измерения (невалидное значение).
3. **`@media screen and (max-width: 900px) { html, body { font-size: 12px; } }`** — принудительное уменьшение базового шрифта на мобильных (нарушение доступности).
4. **`.align-rigt { float: rigt !important; }`** — опечатки в имени класса и значении.
5. **`.alert-danger`** используется в `base.blade.php`, но в `style.scss` определён только `.alert-error`; `.alert-danger` остаётся без специфичных стилей (нет красной заливки).
6. Контент формы обратной связи построен на Bootstrap-классах, но Bootstrap CSS в публичной части не подключён → форма отображается «голо».
7. Viewport в `layouts/base.blade.php:6` запрещает масштабирование (`maximum-scale=1.0, user-scalable=0`).

## 6. Выводы для Фазы 1

- При разбиении монолита на партиалы мёртвые классы (раздел 3) сохраняются как есть — удаление выходит за скоуп change (только инвентаризация).
- Классы из раздела 4 учитывать не нужно — они не имеют стилей; их стилизация — предмет последующих фаз плана.
- Баги из раздела 5 чинятся точечно в рамках задач 3.2 (max-widht, 1170px) и 4.1 (font-size 12px).
