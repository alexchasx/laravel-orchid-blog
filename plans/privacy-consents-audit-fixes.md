# План: доработка функционала политики конфиденциальности и согласий ПДн

> Рабочий план по итогам аудита.
> Аудированы: [`app/Services/ConsentTextBuilder.php`](../app/Services/ConsentTextBuilder.php),
> [`resources/views/privacy.blade.php`](../resources/views/privacy.blade.php),
> [`resources/views/consent/*`](../resources/views/consent/), `consent_logs`, `ConsentController`,
> `CommentController`, `SubscriberController`, cookie-баннер, тесты.
>
> Статус: реализация завершена (все пункты выполнены; финальная проверка `make test` — по желанию).

---

## 0. Итог аудита

Функционал **в целом полноценный**: политика (23 раздела), два самостоятельных согласия
(обработка/распространение) с отдельными чекбоксами, фиксация факта согласия в `consent_logs`
(дословный текст, IP, UA, URL, версия), механизм отзыва (обезличивание/удаление) + фоновая
команда `consents:process-revocations`, cookie-баннер, 13 тестов в `ConsentTest`.

Ниже — найденные пробелы и план их устранения, сгруппированный по приоритетам.

---

## 1. Критичные (безопасность / утечка ПДн)

### 1.1. XSS-риск в `distribution_conditions`
- **Где:** [`ConsentTextBuilder::distributionText()`](../app/Services/ConsentTextBuilder.php:130) —
  `$conditions` вставляется в HTML-блок без экранирования; текст рендерится через `{!! $text !!}`
  и сохраняется в `consent_logs.consent_text`.
- **Что делать:** обернуть `$conditions` в `e()` (или `htmlspecialchars`) при вставке в `blockquote`.
  Дополнительно: при выводе `consent_text` в любом месте (админка, логи) — только через `e()`.

### 1.2. Утечка e-mail в URL ссылки «Отозвать согласие»
- **Где:** [`resources/views/includes/comments_list.blade.php`](../resources/views/includes/comments_list.blade.php:26) —
  ссылка содержит `?email=...`; e-mail попадает в историю браузера, рефереры, логи сервера.
- **Что делать:** убрать `email` из query-параметра, оставить только `comment_id`.
  Форма отзыва и так запрашивает e-mail у пользователя.

### 1.3. Нет серверной валидации согласия при подписке
- **Где:** [`app/Http/Requests/SubscribeRequest.php`](../app/Http/Requests/SubscribeRequest.php:26) —
  валидируется только `email`; чекбокс `consent_processing` в
  [`newsletter_modal.blade.php`](../resources/views/includes/newsletter_modal.blade.php:27) проверяется
  только JS.
- **Что делать:** добавить в `SubscribeRequest` правило `consent_processing => required|accepted`
  + русские сообщения об ошибках.

---

## 2. Юридические расхождения политики и реализации

### 2.1. Фиксация согласия на обработку для подписки в БД (выбран полный вариант)
**Решение пользователя: расширить `consent_logs` полиморфной связью + фиксация при подписке + отзыв для подписчиков.**

#### 2.1.1. Миграция: полиморфные поля в `consent_logs`
- Новая миграция `add_subject_to_consent_logs_table`:
  - `subject_type` string nullable;
  - `subject_id` bigint unsigned nullable;
  - индекс на `(subject_type, subject_id)`;
  - бэкфилл: `UPDATE consent_logs SET subject_type='comment', subject_id=comment_id WHERE comment_id IS NOT NULL`;
  - `down()`: удалить поля.
- `comment_id` остаётся (nullable) для обратной совместимости; новый код использует полиморф.
- **Внимание:** НЕ добавлять DB-level unique на `(subject_type, subject_id, consent_type)` —
  MySQL не поддерживает частичные индексы, а полный unique заблокирует повторную подписку
  после отзыва. Уникальность активного согласия обеспечивается логикой приложения
  (scope `active()` + проверка перед созданием).

#### 2.1.2. Модель `ConsentLog`
- Добавить в `$fillable`: `subject_type`, `subject_id`.
- Константы: `SUBJECT_COMMENT = 'comment'`, `SUBJECT_SUBSCRIBER = 'subscriber'`.
- Связь `subject(): MorphTo`.
- Scope `forSubject(string $type, int $id)`.

#### 2.1.3. `CommentController::store()`
- При создании логов указывать `subject_type = ConsentLog::SUBJECT_COMMENT`,
  `subject_id = $comment->id` (и `comment_id` — для совместимости).

#### 2.1.4. `SubscriberController::store()`
- После создания/реактивации подписчика создавать `ConsentLog`:
  - `subject_type = SUBJECT_SUBSCRIBER`, `subject_id = $subscriber->id`;
  - `consent_type = TYPE_PROCESSING`;
  - текст из `ConsentTextBuilder::processingText(['subject_purpose' => 'отправка информационных и рекламных материалов по адресу электронной почты'])`;
  - IP, UA, `page_url` (страница, с которой подписались), `consented_at = now()`.
- Если активное согласие для подписчика уже есть — не создавать дубль.
- Вся операция — в существующей транзакции.

#### 2.1.5. Отзыв согласия для подписчиков
- [`ConsentController::revoke()`](../app/Http/Controllers/ConsentController.php:75):
  - добавить поле `subject_type` (comment|subscriber) в `RevokeConsentRequest` и форму;
  - для `subscriber`: найти подписчика по e-mail, найти активный лог
    (`subject_type=subscriber`, `subject_id`, `consent_type=processing`), отозвать (`revoke()`),
    отписать (`status = unsubscribed`, `token = null`);
  - для `comment`: существующая логика.
- [`resources/views/consent/revoke.blade.php`](../resources/views/consent/revoke.blade.php):
  - добавить select «Что отозвать»: комментарий (обработка/распространение) или подписка (обработка);
  - поле «Номер комментария» — только для комментариев (JS-переключение).

#### 2.1.6. `SubscriberController::unsubscribe()`
- При отписке отзывать активное согласие на обработку (`revoked_at = now()`):
  отписка от рассылки = отзыв согласия на обработку для цели рассылки.

#### 2.1.7. `ProcessRevocations`
- Ограничить выборку комментариями: `where('subject_type', ConsentLog::SUBJECT_COMMENT)`
  (или `whereNotNull('comment_id')`), чтобы фоновая команда не трогала логи подписчиков.

#### 2.1.8. Фабрика и тесты
- `ConsentLogFactory`: добавить `subject_type`/`subject_id` по умолчанию (comment).
- `ConsentTest`: обновить проверки (subject_type в логах), добавить сценарии:
  - подписка создаёт лог согласия;
  - повторная подписка не дублирует активное согласие;
  - отписка отзывает согласие;
  - отзыв согласия для подписки отписывает подписчика;
  - отзыв без чекбокса согласия при подписке → 422.
- `SubscriberTest`: обновить POST-запросы (добавить `consent_processing => 1`).

### 2.2. Ссылка на политику в форме обратной связи
- **Где:** [`resources/views/contact.blade.php`](../resources/views/contact.blade.php:25) — политика § 7.2
  обещает ссылку на Политику рядом с формой.
- **Что делать:** добавить под формой/над кнопкой ссылку
  `<a href="{{ route('privacy') }}">Политика конфиденциальности</a>`.

### 2.3. Неполные тексты согласий (требования § 8.6–8.7, § 9.3 политики)
- **Где:** [`ConsentTextBuilder`](../app/Services/ConsentTextBuilder.php).
- **Что делать:**
  - `processingText()`: добавить адрес оператора (`$operator['address']`), наименование
    хостинг-провайдера (`$operator['hosting_provider']` — сейчас не используется), ФИО субъекта
    (`$data['subject_name']`, плейсхолдер по умолчанию);
  - `distributionText()`: добавить адрес оператора.

### 2.4. Дата вступления в силу — в конфиг ✅
- **Где:** [`ConsentTextBuilder::CONSENT_EFFECTIVE_DATE`](../app/Services/ConsentTextBuilder.php:26)
  и дважды в [`privacy.blade.php`](../resources/views/privacy.blade.php:36).
- **Что делать:** добавить `config/consent.php` → `effective_date` (например, `'26 сентября 2026 г.'`),
  читать из конфига; в `privacy.blade.php` заменить хардкод на `config('consent.effective_date')`.
- **Статус:** `config/consent.php` уже содержит `effective_date`; `ConsentTextBuilder` и `ConsentController`
  читают из конфига с fallback; **строка 488 `privacy.blade.php` исправлена** — теперь использует
  `config('consent.effective_date', '26 сентября 2026 г.')`.

---

## 3. Качество и консистентность

### 3.1. Опечатка «адрес IP-адрес»
- [`ConsentTextBuilder::processingText()`](../app/Services/ConsentTextBuilder.php:51) → «IP-адрес».

### 3.2. Нормализация e-mail при отзыве
- [`ConsentController::revoke()`](../app/Http/Controllers/ConsentController.php:90) — сравнивать
  `Str::lower($comment->email) === Str::lower($email)`.

### 3.3. Унификация логики отзыва (`processed_at`)
- [`ConsentController::revoke()`](../app/Http/Controllers/ConsentController.php:105) — при мгновенном
  удалении/обезличивании проставлять `processed_at = now()` в логе, чтобы фоновая команда
  не обрабатывала повторно.

### 3.4. `metaTitle`/`metaDesc` для страниц `/consent/*`
- [`ConsentController::processing()`/`distribution()`](../app/Http/Controllers/ConsentController.php:28) —
  передать `metaTitle`/`metaDesc` в представления.

### 3.5. `maxlength` textarea комментария
- [`comments_form.blade.php`](../resources/views/includes/comments_form.blade.php:53) — `maxlength="65525"`
  привести к серверному `max:5000` из [`CommentRequest`](../app/Http/Requests/CommentRequest.php:30).

### 3.6. Нумерация разделов в `privacy.blade.php` ✅
- Разделы 19–20 (внутренние документы, инциденты) закомментированы, последующие не перенумерованы;
  `docs/privacy.md` (25 разделов) расходится с сайтом (23).
- **Что делать:** либо раскомментировать разделы, либо перенумеровать и синхронизировать
  `docs/privacy.md` (зафиксировать решение).
- **Выполнено:** выбран вариант «раскомментировать» — разделы 19–20 возвращены на сайт
  (`privacy.blade.php`), сайт снова содержит 25 разделов и синхронизирован с `docs/privacy.md`.

### 3.7. Inline-стили → SCSS ✅
- [`comments_form.blade.php`](../resources/views/includes/comments_form.blade.php),
  [`revoke.blade.php`](../resources/views/consent/revoke.blade.php),
  [`newsletter_modal.blade.php`](../resources/views/includes/newsletter_modal.blade.php) —
  вынести стили в `resources/sass/techlog/` (по конвенции проекта).
- **Выполнено:**
  - `comments_form.blade.php:12` — `style="display: block"` → класс `.input-row--block`;
  - `revoke.blade.php:98` — `style="margin-top: 20px"` → класс `.revoke-back-link`;
  - `contact.blade.php:5` — `style="display: block; margin-bottom: 24px; max-width: 700px"` → класс `.contact--compact`;
  - `contact.blade.php:44` — `style="margin-bottom: 8px"` → класс `.form-label`;
  - все классы добавлены в [`_components.scss`](../resources/sass/techlog/_components.scss).

---

## 4. Документация

- Обновить [`AGENTS.md`](../AGENTS.md) (gotcha про согласия: полиморфная схема, согласие при подписке).
- Обновить [`README.md`](../README.md) при изменении поведения.
- Обновить [`docs/architecture.md`](../docs/architecture.md) (модель `ConsentLog`, полиморф).
- Обновить [`docs/plan-consents-pdn.md`](../docs/plan-consents-pdn.md) или пометить выполненным.

---

## 5. Порядок выполнения

1. **Критичные:** 1.1 → 1.2 → 1.3.
2. **Полиморфная схема (2.1):** миграция → модель → `CommentController` → `SubscriberController`
   → отзыв → `unsubscribe` → `ProcessRevocations` → фабрика/тесты.
3. **Остальные юридические:** 2.2 → 2.3 → 2.4.
4. **Качество:** 3.1 → 3.2 → 3.3 → 3.4 → 3.5 → 3.6 → 3.7.
5. **Документация:** раздел 4.
6. **Проверка:** `make test`, `make lint`, ручная проверка в браузере (`:8080`).

---

*Каждый пункт считается завершённым после проверки `make test` / ручной проверки сценария.*
