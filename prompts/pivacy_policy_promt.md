# Задача: реализовать согласия на обработку и распространение персональных данных для блога на Laravel + Blade

## Контекст

Блог на Laravel (последняя версия) с шаблонизатором Blade. Есть форма комментариев под статьями. Нужно добавить два отдельных согласия (на обработку ПДн и на распространение ПДн) в соответствии с 152-ФЗ и Приказом Роскомнадзора № 18 от 24.02.2021.

Требования закона:
- Согласие на обработку и согласие на распространение — два самостоятельных документа, два отдельных чекбокса (ч. 4 ст. 9 и ст. 10.1 152-ФЗ).
- Чекбоксы по умолчанию НЕ отмечены.
- Кнопка отправки неактивна, пока не отмечены оба чекбокса.
- Факт согласия фиксируется в БД: IP, дата/время, User-Agent, дословный текст согласия, URL страницы, какой чекбокс отмечен.
- При отзыве согласия на распространение — удалить или обезличить комментарий в течение 7 рабочих дней.
- E-mail пользователя НЕ публикуется — согласие на распространение касается только имени/никнейма и текста комментария.

## Что нужно реализовать

### 1. Миграция: таблица consent_logs

Создать миграцию для таблицы `consent_logs`, которая фиксирует каждый факт согласия:

```
Schema::create('consent_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('comment_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('consent_type'); // 'processing' или 'distribution'
    $table->text('consent_text');   // дословный текст согласия, с которым ознакомился пользователь
    $table->string('consent_version'); // версия текста согласия, например '1.0'
    $table->string('ip_address', 45);
    $table->text('user_agent');
    $table->string('page_url');
    $table->timestamp('consented_at');
    $table->timestamp('revoked_at')->nullable();
    $table->timestamps();
});
```

### 2. Миграция: поля в таблице comments

Добавить в таблицу `comments`:
- `consent_processing_log_id` (foreign → consent_logs.id, nullable) — ссылка на запись о согласии на обработку.
- `consent_distribution_log_id` (foreign → consent_logs.id, nullable) — ссылка на запись о согласии на распространение.
- `is_anonymized` (boolean, default false) — пометка, что комментарий обезличен при отзыве согласия на распространение.

### 3. Модель ConsentLog

Создать модель `app/Models/ConsentLog.php`:
- Связь `belongsTo` с `Comment`.
- Метод `revoke()` — устанавливает `revoked_at = now()`.

### 4. Тексты согласий

#### 4.1. Страница с полным текстом согласия на обработку

Создать route `GET /consent/processing` и Blade-шаблон `resources/views/consent/processing.blade.php`.

Текст согласия на обработку должен содержать:
- Наименование и адрес оператора (вынести в конфиг config/operator.php — поля: name, address, inn, ogrn, email, phone, site_url).
- Цель обработки: «модерация и идентификация комментариев на сайте [URL]».
- Перечень данных: имя/никнейм, e-mail, IP-адрес, технические данные сессии.
- Перечень действий: сбор, запись, хранение, использование, передача хостинг-провайдеру, уничтожение.
- Наименование третьих лиц, которым передаются данные: хостинг-провайдер (из конфига).
- Срок действия: до отзыва согласия.
- Порядок отзыва: по запросу на [e-mail из конфига].

Текст брать из конфига operator.php, чтобы можно было менять реквизиты без редактирования шаблона.

#### 4.2. Страница с полным текстом согласия на распространение

Создать route `GET /consent/distribution` и Blade-шаблон `resources/views/consent/distribution.blade.php`.

Текст согласия на распространение должен содержать все обязательные элементы Приказа Роскомнадзора № 18:
- ФИО субъекта (заполняется из поля «Имя» формы — в тексте страницы ставится плейсхолдер «[Заполняется автоматически из формы]»).
- Наименование и адрес оператора (из конфига).
- Цель: публичное отображение комментария к статье на сайте [URL].
- Перечень данных: имя/никнейм, текст комментария, дата и время публикации.
- Перечень действий: предоставление доступа неопределённому кругу лиц, отображение на сайте.
- Перечень запрещённых действий: поле «Дополнительные условия» — необязательное текстовое поле в форме комментария, пользователь может указать запреты (например, «не использовать в рекламных целях»). Если поле пусто — писать «Запреты не указаны».
- Срок действия: до отзыва согласия.
- Условие: предоставление согласия не влечёт обязанности оператора по сохранению авторских прав субъекта.
- Порядок отзыва: по запросу на [e-mail из конфига].

### 5. Форма комментария (Blade)

Обновить шаблон формы комментария, добавив:

1. Два отдельных чекбокса (оба unchecked по умолчанию):

Чекбокс 1 — согласие на обработку:
```html
<label>
  <input type="checkbox" name="consent_processing" value="1" required>
  Я даю согласие на обработку моих персональных данных (имя, e-mail, IP-адрес)
  в целях модерации и идентификации комментариев в соответствии с
  <a href="/consent/processing" target="_blank">Политикой конфиденциальности</a>.
  Согласие действует до момента его отзыва.
</label>
```

Чекбокс 2 — согласие на распространение:
```html
<label>
  <input type="checkbox" name="consent_distribution" value="1" required>
  Я даю согласие на распространение (публичное отображение) моих данных —
  имени/никнейма и текста комментария на сайте.
  <a href="/consent/distribution" target="_blank">Текст согласия</a>.
  Согласие действует до момента его отзыва.
</label>
```

2. Необязательное поле «Дополнительные условия» (textarea, не обязательно):
```html
<label>
  Дополнительные условия / запреты (необязательно):
  <textarea name="distribution_conditions" rows="2"
    placeholder="Например: запрещаю использование моего комментария в рекламных целях"></textarea>
</label>
```

3. Кнопка отправки disabled по умолчанию:
```html
<button type="submit" id="comment-submit" disabled>Отправить комментарий</button>
```

### 6. JavaScript: активация кнопки

Добавить скрипт (в Blade-шаблон или в отдельный JS-файл), который:
- Слушает изменение обоих чекбоксов.
- Активирует кнопку отправки ТОЛЬКО когда оба отмечены.
- Если хотя бы один снят — снова деактивирует.

```javascript
const processing = document.querySelector('input[name="consent_processing"]');
const distribution = document.querySelector('input[name="consent_distribution"]');
const submitBtn = document.getElementById('comment-submit');

function updateSubmitState() {
  submitBtn.disabled = !(processing.checked && distribution.checked);
}
processing.addEventListener('change', updateSubmitState);
distribution.addEventListener('change', updateSubmitState);
```

### 7. Контроллер: сохранение комментария и фиксация согласий

В контроллере комментариев (например, `CommentController@store`):

1. Валидация:
   - `consent_processing` — required, accepted.
   - `consent_distribution` — required, accepted.
   - `distribution_conditions` — nullable, string, max:1000.

2. После валидации и перед сохранением комментария:
   - Получить IP: `$request->ip()`.
   - Получить User-Agent: `$request->userAgent()`.
   - Получить URL: `$request->headers->get('referer')` или `url()->previous()`.
   - Сформировать текст согласия на обработку (из конфига operator.php + цель + перечень данных).
   - Сформировать текст согласия на распространение (из конфига + цель + перечень данных + условия/запреты из поля `distribution_conditions`).

3. Сохранить комментарий, затем создать две записи в `consent_logs`:
```php
$processingLog = ConsentLog::create([
    'comment_id' => $comment->id,
    'consent_type' => 'processing',
    'consent_text' => $processingConsentText,
    'consent_version' => config('consent.versions.processing', '1.0'),
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'page_url' => url()->previous(),
    'consented_at' => now(),
]);

$distributionLog = ConsentLog::create([
    'comment_id' => $comment->id,
    'consent_type' => 'distribution',
    'consent_text' => $distributionConsentText,
    'consent_version' => config('consent.versions.distribution', '1.0'),
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'page_url' => url()->previous(),
    'consented_at' => now(),
]);
```

4. Привязать логи к комментарию:
```php
$comment->update([
    'consent_processing_log_id' => $processingLog->id,
    'consent_distribution_log_id' => $distributionLog->id,
]);
```

### 8. Конфиг

Создать `config/operator.php`:
```php
return [
    'name' => env('OPERATOR_NAME', 'Иванов Иван Иванович'),
    'address' => env('OPERATOR_ADDRESS', 'г. Москва, ул. Примерная, д. 1'),
    'inn' => env('OPERATOR_INN', '123456789012'),
    'ogrn' => env('OPERATOR_OGRN', '1234567890123'),
    'email' => env('OPERATOR_EMAIL', 'privacy@example.com'),
    'phone' => env('OPERATOR_PHONE', '+7 (000) 000-00-00'),
    'site_url' => env('APP_URL', 'https://example.com'),
    'hosting_provider' => env('HOSTING_PROVIDER', 'ООО «Региональный хостинг»'),
];
```

Создать `config/consent.php`:
```php
return [
    'versions' => [
        'processing' => '1.0',
        'distribution' => '1.0',
    ],
    'distribution' => [
        'max_conditions_length' => 1000,
    ],
];
```

### 9. Отзыв согласия

Создать контроллер `ConsentController` с методом `revoke(Request $request)`:
- Принимает `comment_id`, `consent_type` ('processing' или 'distribution'), e-mail для идентификации.
- Ищет активную (где `revoked_at IS NULL`) запись `ConsentLog` по `comment_id` и `consent_type`.
- Если consent_type == 'distribution':
  - Установить `revoked_at = now()`.
  - Обезличить комментарий: очистить поле `author_name` (заменить на «Аноним»), установить `is_anonymized = true`.
  - НЕ удалять текст комментария.
- Если consent_type == 'processing':
  - Установить `revoked_at = now()`.
  - Удалить комментарий целиком (или пометить на удаление).
- Вернуть ответ с подтверждением.

Создать route `POST /consent/revoke`.

Добавить простую форму отзыва (или инструкцию): ссылка «Отозвать согласие» рядом с комментарием (доступна только владельцу по e-mail) или через запрос на e-mail оператора.

### 10. Тесты

Написать feature-тесты:
1. Форма комментария отображается с двумя чекбоксами (оба unchecked).
2. Отправка без отмеченных чекбоксов — валидация не проходит.
3. Отправка с одним чекбоксом — валидация не проходит.
4. Отправка с двумя чекбоксами — комментарий создаётся, в consent_logs две записи.
5. Страницы /consent/processing и /consent/distribution доступны и содержат обязательные элементы.
6. Отзыв согласия на распространение — комментарий обезличен, текст сохранён.
7. Отзыв согласия на обработку — комментарий удалён.

## Требования к коду

- Следуй конвенциям Laravel (PSR-12,命名 в стиле Laravel).
- Используй Eloquent, не сырые SQL-запросы.
- Тексты согласий формируй на сервере (в контроллере или сервис-классе), не в JS.
- Конфигурация оператора — через config-файлы и .env, не хардкод.
- Добавь комментарии к коду на русском языке, объясняющие юридические требования.
- Миграции должны быть обратимы (метод down()).

## Структура файлов для создания/изменения

- database/migrations/xxxx_create_consent_logs_table.php
- database/migrations/xxxx_add_consent_fields_to_comments_table.php
- app/Models/ConsentLog.php
- app/Http/Controllers/ConsentController.php
- app/Http/Controllers/CommentController.php (изменить)
- config/operator.php
- config/consent.php
- resources/views/consent/processing.blade.php
- resources/views/consent/distribution.blade.php
- resources/views/comments/_form.blade.php (или где находится форма комментария — изменить)
- routes/web.php (добавить routes)
- tests/Feature/ConsentTest.php
