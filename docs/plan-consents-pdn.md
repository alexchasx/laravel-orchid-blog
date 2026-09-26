# План: согласия на обработку и распространение персональных данных (152-ФЗ, Приказ РКН № 18)

> Рабочий план реализации. Источник требований: [`prompts/pivacy_policy_promt.md`](../prompts/pivacy_policy_promt.md).
> Правовая база: ч. 4 ст. 9 и ст. 10.1 Федерального закона № 152-ФЗ, Приказ Роскомнадзора № 18 от 24.02.2021.
>
> Статус: план (реализация ещё не начата).

---

## 0. Цель и принципы

0.1. Добавить на форму комментариев два самостоятельных согласия: **на обработку ПДн** и **на распространение ПДн**.

0.2. Зафиксировать каждый факт согласия в БД (`consent_logs`): IP, дата/время, User-Agent, дословный текст согласия, URL страницы, тип согласия.

0.3. Реализовать механизм отзыва: распространение → обезличивание комментария (текст сохраняется), обработка → удаление комментария.

0.4. E-mail пользователя публично не выводится; согласие на распространение касается только имени/никнейма и текста комментария.

0.5. Чекбоксы по умолчанию не отмечены; кнопка отправки неактивна, пока не отмечены оба.

0.6. Все тексты согласий формируются на сервере из конфигов (`config/operator.php`, `config/consent.php`), без хардкода в шаблонах и JS.

0.7. Соблюдать конвенции проекта: русские комментарии в коде, Eloquent вместо сырого SQL, обратимые миграции (`down()`), работа через Docker (`make ...`).

---

## 1. Анализ текущего кода (что уже есть)

1.1. Форма комментария: [`resources/views/includes/comments_form.blade.php`](../resources/views/includes/comments_form.blade.php) — подключена в шаблон статьи, содержит поля `name`, `email`, `captcha` (для гостей), `comment`, скрытый `article_id`, кнопку отправки без состояния disabled.

1.2. Контроллер: [`app/Http/Controllers/CommentController.php`](../app/Http/Controllers/CommentController.php) — метод `store(CommentRequest $request)`; для гостей `active = false` (комментарий уходит на модерацию).

1.3. Request-валидация: [`app/Http/Requests/CommentRequest.php`](../app/Http/Requests/CommentRequest.php) — сюда добавляются правила согласий и «дополнительных условий».

1.4. Роуты: [`routes/web.php`](../routes/web.php) — `POST comment.create` с `throttle:10,1` (строка 51–53); уже есть маршрут `GET privacy`.

1.5. Модель `Comment`: [`app/Models/Comment.php`](../app/Models/Comment.php) — нуждается в новых связях и `fillable`.

1.6. Тесты: [`tests/Feature/CommentTest.php`](../tests/Feature/CommentTest.php) — существующие тесты нужно дополнить новыми сценариями (и убедиться, что старые не сломались).

1.7. Уже есть страница [`resources/views/privacy.blade.php`](../resources/views/privacy.blade.php) (роут `privacy`) — проверить, не дублирует ли она тексты согласий; при необходимости связать ссылками со страницами `/consent/*`.

---

## 2. Конфигурация (реквизиты оператора и версии текстов)

2.1. Создать [`config/operator.php`](../config/operator.php):
- поля: `name`, `address`, `inn`, `ogrn`, `email`, `phone`, `site_url`, `hosting_provider`;
- значения — из `env(...)` с дефолтами-плейсхолдерами (шаблонный репозиторий: никаких реальных данных!).

2.2. Создать [`config/consent.php`](../config/consent.php):
- `versions.processing` = `'1.0'`, `versions.distribution` = `'1.0'`;
- `distribution.max_conditions_length` = `1000`;
- при необходимости: ключ `expiration_days_distribution` / сроки обработки отзывов.

2.3. Дополнить [`.env.example`](../.env.example) новыми переменными с пустыми/плейсхолдерными значениями:
`OPERATOR_NAME`, `OPERATOR_ADDRESS`, `OPERATOR_INN`, `OPERATOR_OGRN`, `OPERATOR_EMAIL`, `OPERATOR_PHONE`, `HOSTING_PROVIDER`.

2.4. Проверить, что `config('operator.*')` нигде не конфликтует с существующим [`config/my_config.php`](../config/my_config.php) (там уже есть `CONTACT_EMAIL` и т.п.) — при пересечении решить, какой конфиг источник правды.

---

## 3. База данных: миграция `consent_logs`

3.1. Создать миграцию `create_consent_logs_table`:
- `id`;
- `comment_id` (nullable FK → `comments.id`, `cascadeOnDelete`);
- `consent_type` string: `'processing' | 'distribution'` (+ индекс);
- `consent_text` text — дословный текст, с которым ознакомился пользователь;
- `consent_version` string (например, `'1.0'`);
- `ip_address` string(45);
- `user_agent` text;
- `page_url` string;
- `consented_at` timestamp;
- `revoked_at` timestamp nullable (+ индекс для выборки активных согласий);
- `timestamps()`.

3.2. Реализовать `down()`: `Schema::dropIfExists('consent_logs')`.

3.3. Подумать над ограничением уникальности: одна активная запись распределения на комментарий (частичный уникальный индекс на `comment_id + consent_type` где `revoked_at IS NULL`) — зафиксировать решение в коде.

---

## 4. База данных: миграция полей таблицы `comments`

4.1. Создать миграцию `add_consent_fields_to_comments_table`:
- `consent_processing_log_id` bigint unsigned nullable FK → `consent_logs.id` (`nullOnDelete`);
- `consent_distribution_log_id` bigint unsigned nullable FK → `consent_logs.id` (`nullOnDelete`);
- `is_anonymized` boolean default `false`.

4.2. Реализовать `down()`: удалить поля и внешние ключи.

4.3. Внимание: две таблицы ссылаются друг на друга (комментарий → лог и лог → комментарий) — в миграциях сначала создать лог-таблицу с `comment_id` (уже существует к этому моменту), затем добавить FK из `comments`. Порядок создания описан в п. 3–4.

---

## 5. Модель `ConsentLog`

5.1. Создать [`app/Models/ConsentLog.php`](../app/Models/ConsentLog.php):
- `protected $fillable` = все поля миграции;
- `$casts`: `consented_at` / `revoked_at` → `datetime`, `is_*` не требуется (булево в `comments`);
- константы типов: `TYPE_PROCESSING = 'processing'`, `TYPE_DISTRIBUTION = 'distribution'`;
- связь `belongsTo(Comment::class)`.

5.2. Метод `revoke(): bool` — устанавливает `revoked_at = now()` и сохраняет.

5.3. Scope `active()` — `whereNull('revoked_at')`.

5.4. Фабрика `database/factories/ConsentLogFactory.php` (для тестов).

---

## 6. Модель `Comment` — новые связи и fillable

6.1. В [`app/Models/Comment.php`](../app/Models/Comment.php):
- добавить в `$fillable`: `consent_processing_log_id`, `consent_distribution_log_id`, `is_anonymized` (существующий список полей дополнить аккуратно);
- `$casts`: `is_anonymized` → `boolean`;
- связи: `hasOne(ConsentLog::class, 'id', 'consent_processing_log_id')` и аналогично для распределения, либо `belongsTo` через соглашение имен — выбрать единый стиль и задокументировать.

6.2. Убедиться, что связь «лог → комментарий» и «комментарий → лог» не конфликтуют с `cascadeOnDelete` из п. 3.1 (при удалении комментария логи удаляются каскадом — это ок, FK из `comments` → `nullOnDelete` защищает от цикла).

---

## 7. Сервис формирования текстов согласий

7.1. Создать [`app/Services/ConsentTextBuilder.php`](../app/Services/ConsentTextBuilder.php) — единственное место, где собираются тексты:
- `processingText(array $data): string` — из `config('operator')` + цель «модерация и идентификация комментариев», перечень данных, действий, третьих лиц (хостинг-провайдер), срок, порядок отзыва;
- `distributionText(array $data): string` — по Приказу РКН № 18: данные субъекта (плейсхолдер из формы), оператор, цель, перечень данных и действий, «дополнительные условия/запреты» (или «Запреты не указаны»), срок, условие про авторские права, порядок отзыва;
- версии текстов брать из `config('consent.versions')`.

7.2. Разделить «полный текст для страницы» (п. 8) и «дословный текст для лога» (может совпадать или быть краткой версией с версионированием) — решение зафиксировать.

7.3. Согласие на распространение формируется **динамически** (включает имя пользователя и условия/запреты из формы) — значит, для лога сохраняем именно тот текст, который соответствовал отметке чекбокса.

---

## 8. Публичные страницы текстов согласий

8.1. Маршруты в [`routes/web.php`](../routes/web.php):
- `GET /consent/processing` → `ConsentController@processing` (name `consent.processing`);
- `GET /consent/distribution` → `ConsentController@distribution` (name `consent.distribution`);
- обе без middleware `auth` (страницы должны открываться гостям).

8.2. Контроллер [`app/Http/Controllers/ConsentController.php`](../app/Http/Controllers/ConsentController.php) — методы `processing()` и `distribution()`, рендерят представления с текстом из `ConsentTextBuilder` (страница распространения — с плейсхолдером вместо имени).

8.3. Blade-шаблоны:
- [`resources/views/consent/processing.blade.php`](../resources/views/consent/processing.blade.php);
- [`resources/views/consent/distribution.blade.php`](../resources/views/consent/distribution.blade.php);
- использовать общий layout `layouts.techlog`, стили — существующие классы SCSS.

8.4. На каждой странице — дата вступления в силу и версия текста (`consent_version`), ссылка назад.

---

## 9. Форма комментария: чекбоксы и поле условий

9.1. Изменить [`resources/views/includes/comments_form.blade.php`](../resources/views/includes/comments_form.blade.php):
- добавить чекбокс «Согласие на обработку ПДн» (`name="consent_processing"`, `value="1"`, по умолчанию unchecked, ссылка на `/consent/processing`, `target="_blank"`);
- добавить чекбокс «Согласие на распространение ПДн» (`name="consent_distribution"`, аналогично, ссылка на `/consent/distribution`);
- добавить необязательное поле `distribution_conditions` (textarea, `maxlength` из конфига, плейсхолдер с примером запрета);
- кнопке отправки задать `id="comment-submit"` и атрибут `disabled` (по умолчанию заблокирована);
- чекбоксы расположить после поля «Комментарий», перед кнопкой.

9.2. Сохранить значение `old('consent_processing')` / `old('consent_distribution')` при re-render после ошибки валидации (атрибут `checked`).

9.3. Убедиться, что ошибки валидации по новым полям выводятся рядом с чекбоксами (блок `@error`).

---

## 10. JavaScript: активация кнопки

10.1. Добавить скрипт (в `comments_form.blade.php` через `@push('scripts')` или в отдельный JS-модуль, подключённый через Vite — по конвенции проекта):
- слушать `change` по обоим чекбоксам;
- `submitBtn.disabled = !(processing.checked && distribution.checked)`;
- при загрузке страницы пересчитать состояние (для `old()` значений после ошибки валидации);
- прокомментировать на русском.

10.2. Убедиться, что скрипт работает и при повторной отправке формы с ошибками (чекбоксы могут восстановиться из `old()`).

10.3. Серверная валидация (п. 11) — обязательный рубеж; JS — только UX (защита от отключённого JS).

---

## 11. Валидация: `CommentRequest`

11.1. В [`app/Http/Requests/CommentRequest.php`](../app/Http/Requests/CommentRequest.php) добавить правила:
- `consent_processing` → `required|accepted`;
- `consent_distribution` → `required|accepted`;
- `distribution_conditions` → `nullable|string|max:1000` (лимит из `config('consent.distribution.max_conditions_length')`).

11.2. Добавить русские сообщения об ошибках (в `messages()` или через `lang/ru/validation.php`).

---

## 12. Сохранение комментария и фиксация согласий

12.1. Переработать `store()` в [`app/Http/Controllers/CommentController.php`](../app/Http/Controllers/CommentController.php):
- получить `$ip = $request->ip()`, `$userAgent = $request->userAgent()`, `$pageUrl = url()->previous()` (или реферер) **до** создания комментария;
- собрать тексты согласий через `ConsentTextBuilder`;
- создать комментарий (существующая логика сохраняется);
- создать две записи `ConsentLog` (`comment_id`, тип, текст, версия, IP, UA, URL, `consented_at = now()`);
- обновить комментарий: `consent_processing_log_id`, `consent_distribution_log_id`;
- весь блок — в транзакции (`DB::transaction`) с откатом при ошибке.

12.2. Логику вынести в сервис (например, `CommentService` или расширение `ConsentTextBuilder`) для читаемости и переиспользования в тестах — решение зафиксировать.

12.3. Обработать краевой случай: если согласия нет (например, прямой POST мимо формы) — валидация всё равно не пропустит (п. 11).

---

## 13. Отзыв согласия

13.1. В `ConsentController` метод `revoke(Request $request)`:
- входные данные: `comment_id`, `consent_type`, e-mail для идентификации владельца;
- найти активную запись `ConsentLog` (`comment_id` + `consent_type` + `revoked_at IS NULL`) через scope `active()`;
- сверить e-mail (и, возможно, IP) с данными комментария — только владелец может отозвать;
- `consent_type === 'distribution'`: `revoke()` лога + обезличить комментарий (`author_name = 'Аноним'`, `is_anonymized = true`, текст не трогать);
- `consent_type === 'processing'`: `revoke()` лога + удалить комментарий целиком;
- вернуть подтверждение (JSON или flash-сообщение + редирект).

13.2. Маршрут `POST /consent/revoke` (name `consent.revoke`), под `throttle` (например, `throttle:10,1`).

13.3. Форма/инструкция отзыва: ссылка «Отозвать согласие» рядом с комментарием для владельца (по e-mail), плюс упоминание в текстах согласий и на странице `privacy`.

13.4. **Законный срок**: прекратить распространение в течение 3 рабочих дней, удалить/обезличить — в сроки, заявленные в согласии (в промпте — 7 рабочих дней). Зафиксировать срок в конфиге (`config('consent.')`) и в тексте согласия.

---

## 14. Фоновая обработка отзывов (по желанию, по итогам ревью)

14.1. Если решено обрабатывать отзывы отложенно: команда `consents:process-revocations` (artisan) + расписание в [`routes/console.php`](../routes/console.php) (по аналогии с `articles:publish-scheduled`), запускаемая контейнером `schedule`.

14.2. Команда находит `ConsentLog` с `revoked_at` в прошлом и непогашенной обязанностью, выполняет обезличивание/удаление и логирует результат.

14.3. Пункт реализуется только если по результатам код-ревью решили не делать отзыв синхронно в `ConsentController`.

---

## 15. Тесты

15.1. Создать [`tests/Feature/ConsentTest.php`](../tests/Feature/ConsentTest.php):
1. форма комментария рендерит два чекбокса, оба unchecked, кнопка disabled;
2. отправка без чекбоксов → ошибка валидации (`consent_processing`, `consent_distribution`);
3. отправка с одним чекбоксом → ошибка валидации;
4. отправка с двумя чекбоксами → комментарий создаётся, в `consent_logs` две записи с корректными IP/UA/URL/текстом/версией;
5. `GET /consent/processing` и `GET /consent/distribution` доступны и содержат обязательные элементы (оператор, цель, перечень данных, порядок отзыва);
6. отзыв на распространение → `revoked_at` проставлен, `is_anonymized = true`, `author_name = 'Аноним'`, текст сохранён;
7. отзыв на обработку → комментарий удалён, лог помечен `revoked_at`;
8. отзыв чужим e-mail → отклонено;
9. `distribution_conditions` сохраняется в тексте согласия и в комментарии (если поле добавлено в модель);
10. лимит `distribution_conditions` > 1000 → ошибка валидации.

15.2. Обновить существующие тесты [`tests/Feature/CommentTest.php`](../tests/Feature/CommentTest.php): в фабрики/helpers добавлены чекбоксы согласий, чтобы старые сценарии не падали.

15.3. Фабрика [`database/factories/ConsentLogFactory.php`](../database/factories/ConsentLogFactory.php) и, при необходимости, фабрика `Comment` с заполненными согласиями.

15.4. Прогнать `make test` (тесты идут в контейнере `blog_app`, БД `testing`).

---

## 16. Доступы и права

16.1. Определить, нужен ли просмотр/редактирование `consent_logs` в админ-панели Orchid (`app/Orchid/`):
- если да — экран `ConsentLogListScreen` + пермишен (по аналогии с `platform.custom.comments`);
- если нет — доступ через БД/phpMyAdmin, решение зафиксировать в README.

16.2. Убедиться, что гости могут открывать страницы согласий (без `auth`).

---

## 17. Документация и README

17.1. Обновить [`README.md`](../README.md):
- раздел «✨ Возможности "из коробки"» — добавить согласия 152-ФЗ;
- раздел «чего пока нет» — убрать/дополнить упоминания;
- инструкция по настройке `.env` (новые переменные оператора).

17.2. Обновить [`docs/architecture.md`](../docs/architecture.md): модель `ConsentLog`, сервис `ConsentTextBuilder`, маршруты `/consent/*`, команды (если добавлены).

17.3. Обновить [`AGENTS.md`](../AGENTS.md) (Gotchas): описать новую сущность согласий, «дословный текст в БД», сроки отзыва.

17.4. Обновить [`prompts/pivacy_policy_promt.md`](../prompts/pivacy_policy_promt.md) или пометить его как выполненный, когда реализация завершена.

---

## 18. Финальная проверка

18.1. `make migrate` (или точечно новые миграции) в чистой БД и повторно на существующей (обратимость, отсутствие потери данных).

18.2. `make test` — все тесты зелёные.

18.3. `make lint` — `php -l` по изменённым файлам.

18.4. Ручная проверка в браузере (Docker, сайт `:8080`):
- форма без чекбоксов → кнопка заблокирована;
- отметить один → всё ещё заблокирована;
- отметить оба → кнопка активна;
- отправка → комментарий на модерации, в `consent_logs` две записи;
- страницы `/consent/processing` и `/consent/distribution` открываются и содержат реквизиты из конфига;
- отзыв по e-mail → обезличивание/удаление работает.

18.5. Проверить, что `public/build/manifest.json` не требуется менять вручную (сборка фронтенда через `make frontend-build`, если менялись JS/SCSS).

---

## 19. Список файлов для создания/изменения (сводно)

| Действие | Файл |
|---|---|
| создать | [`config/operator.php`](../config/operator.php) |
| создать | [`config/consent.php`](../config/consent.php) |
| создать | `database/migrations/xxxx_create_consent_logs_table.php` |
| создать | `database/migrations/xxxx_add_consent_fields_to_comments_table.php` |
| создать | [`app/Models/ConsentLog.php`](../app/Models/ConsentLog.php) |
| создать | [`app/Services/ConsentTextBuilder.php`](../app/Services/ConsentTextBuilder.php) |
| создать | [`app/Http/Controllers/ConsentController.php`](../app/Http/Controllers/ConsentController.php) |
| создать | [`resources/views/consent/processing.blade.php`](../resources/views/consent/processing.blade.php) |
| создать | [`resources/views/consent/distribution.blade.php`](../resources/views/consent/distribution.blade.php) |
| создать | [`database/factories/ConsentLogFactory.php`](../database/factories/ConsentLogFactory.php) |
| создать | [`tests/Feature/ConsentTest.php`](../tests/Feature/ConsentTest.php) |
| изменить | [`app/Models/Comment.php`](../app/Models/Comment.php) |
| изменить | [`app/Http/Controllers/CommentController.php`](../app/Http/Controllers/CommentController.php) |
| изменить | [`app/Http/Requests/CommentRequest.php`](../app/Http/Requests/CommentRequest.php) |
| изменить | [`routes/web.php`](../routes/web.php) |
| изменить | [`resources/views/includes/comments_form.blade.php`](../resources/views/includes/comments_form.blade.php) |
| изменить | [`.env.example`](../.env.example) |
| изменить | [`tests/Feature/CommentTest.php`](../tests/Feature/CommentTest.php) |
| изменить | [`README.md`](../README.md), [`docs/architecture.md`](../docs/architecture.md), [`AGENTS.md`](../AGENTS.md) |
| опционально | `database/migrations/...`, [`routes/console.php`](../routes/console.php) — фоновая команда отзывов |

---

## 20. Порядок выполнения (этапы)

20.1. **Этап 1 — фундамент:** конфиги (п. 2) → миграции (п. 3–4) → модели и фабрики (п. 5–6) → `make migrate`.

20.2. **Этап 2 — тексты и страницы:** сервис текстов (п. 7) → страницы `/consent/*` (п. 8).

20.3. **Этап 3 — форма и сохранение:** форма + JS (п. 9–10) → валидация (п. 11) → контроллер (п. 12).

20.4. **Этап 4 — отзыв:** `ConsentController@revoke` (п. 13) → опционально фоновая команда (п. 14).

20.5. **Этап 5 — качество:** тесты (п. 15) → документация (п. 17) → финальная проверка (п. 18).

---

*Каждый пункт считается завершённым после проверки `make test` / ручной проверки соответствующего сценария.*
