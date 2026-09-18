# План: комментарии без регистрации + капча + IP

Дата: 2026-09-18
Статус: реализован

## Решения

- Капча: **простая математическая** (вопрос «a + b = ?», ответ хранится в сессии, без внешних ключей и зависимостей).
- Гостевые комментарии: **на модерацию** — сохраняются с `active = false`, публикуются одобрением в админке Orchid.
- Время комментария уже хранится в `created_at` и отображается в `comments_list.blade.php` — отдельная работа не требуется.
- IP сохраняется через новый столбец `comments.ip` (varchar 45, nullable — поддерживает IPv4/IPv6).

## Изменения

### 1. Миграция
- Новый: `database/migrations/2026_09_18_000000_add_ip_to_comments_table.php`
  - `string('ip', 45)->nullable()`.

### 2. Капча
- Новый: `app/Support/MathCaptcha.php`
  - `question()`: генерирует «a + b = ?», кладёт ответ в `session('captcha_answer')`, возвращает текст для отображения.
  - `check($value)`: сверяет ответ и очищает сессию.
- Новый: `app/Rules/MathCaptchaRule.php` — правило валидации на этот хелпер.

### 3. Модель / фабрика / юнит-тест
- `app/Models/Comment.php`: добавить `ip` в `$fillable` и PHPDoc.
- `app/Http/Requests/CommentRequest.php`:
  - Обязательные: `article_id` (`required|exists:articles,id`), `comment` (`required|max:5000`).
  - Для гостей (`! $this->user()`): `name` (`required|string|min:2|max:255`), `email` (`required|email|max:255`), `captcha` (`required|MathCaptchaRule`).
  - Русские `messages()`.
- `database/factories/CommentFactory.php`: `ip => faker->ipv4()`, `user_id` иногда `null` (гости), `website` nullable.
- `tests/Unit/CommentModelTest.php`: обновить `test_fillable_fields` (добавить `ip`).

### 4. Контроллер, маршруты
- `app/Http/Controllers/CommentController.php` `store()`:
  - `Article::findOrFail()`.
  - Сохраняет `ip => $request->ip()`.
  - Авторизованный: `user_id`, имя/email из профиля, `active = true`.
  - Гость: имя/email из формы, `active = false`, `user_id = null`.
  - Редиректы: гость → `#comments` + flash «Комментарий отправлен и появится после модерации»; авторизованный → `#comment{id}`.
- `routes/web.php`: `commentStore` вынести из группы `auth`, добавить `throttle:10,1` (10 POST в минуту на IP); `commentDelete` остаётся под `auth`.

### 5. Представления
- `app/Http/Controllers/ArticleController.php` `show()`: передать `'captcha' => MathCaptcha::question()` во view.
- `resources/views/includes/comments_form.blade.php`:
  - Убрать заглушку «Авторизуйтесь, чтобы прокомментировать».
  - Единая форма: для гостей поля Имя/Email (как в `contact.blade.php`, в `.input-row`) + вопрос капчи с полем ответа; авторизованным — как сейчас.
- `resources/views/includes/comments_list.blade.php`: показывать только активные комментарии — `$article->comments->where('active', true)` (в заголовке и в цикле).

### 6. Админка Orchid (модерация + IP)
- `app/Orchid/Screens/Comment/CommentScreen.php`:
  - Смотры `ip` и `active`.
  - Кнопка «Одобрить» в `commandBar` (видна при `active === false`), метод `approve()` → `active = true`.
- `app/Orchid/Screens/Comment/CommentListScreen.php`: колонки `ip` и `active` (бейдж «На модерации» / «Опубликован»).

### 7. Feature-тесты
- `tests/Feature/CommentTest.php`:
  - Заменить `test_guest_cannot_store_comment` на успешный гостевой сценарий: `active = false`, `ip` сохранён, `user_id = null`.
  - Новые: гость без капчи → ошибка; гость с неверной капчей → ошибка; гостю обязательны `name`/`email`; несуществующий `article_id` → ошибка.
  - Существующие (авторизованный, пустой текст, удаление) — адаптировать.

### 8. Проверка
- `make test`, `make lint`.
- Вручную через `make serve`: гостевой комментарий не появляется сразу; в админке `/admin` виден с IP и кнопкой «Одобрить».

## Не требуется
- Новых переменных `.env`, зависимостей, ключей reCAPTCHA.
- Правок SCSS: капча/поля наследуют стили `.comment-form` и `.input-row`.