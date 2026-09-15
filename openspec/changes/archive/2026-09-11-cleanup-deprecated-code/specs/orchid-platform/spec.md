## ADDED Requirements

### Requirement: Исключение депрецированных API Orchid 14
Код панели MUST использовать актуальные API Orchid 14 и SHALL NOT использовать депрецированные символы: класс `Orchid\Access\UserSwitch` и метод `getStatusPermission()`.

#### Scenario: Режим «войти как» использует актуальный API
- **WHEN** выполняется вход от имени пользователя (`loginAs`) в экране пользователя
- **THEN** используется класс `Orchid\Access\Impersonation`, а устаревший `Orchid\Access\UserSwitch` отсутствует в коде панели

#### Scenario: Статус разрешений через актуальный метод
- **WHEN** экраны пользователя и роли получают статус разрешений
- **THEN** вызывается метод `statusOfPermissions()`, а вызовов депрецированного `getStatusPermission()` в коде панели нет

### Requirement: Исключение депрецированного Request::get() в панели
Обращения к данным HTTP-запроса в экранах и фильтрах панели MUST использовать `input()` и SHALL NOT использовать депрецированный метод `get()` интерфейса запроса.

#### Scenario: Чтение параметров запроса
- **WHEN** экраны или фильтр панели читают параметры из объекта `Illuminate\Http\Request`
- **THEN** используется `$request->input(...)`, а вызовов `$request->get(...)` на объектах запроса в коде панели нет

### Requirement: Отсутствие мёртвого auth-кода
Репозиторий MUST не содержать неиспользуемых контроллеров и представлений старой аутентификации (`LoginController`, `RegisterController`, `ForgotPasswordController`, `ResetPasswordController`, `VerificationController`, `ConfirmPasswordController`, `auth/passwords/*`, `auth/verify.blade.php`).

#### Scenario: Старые auth-контроллеры удалены
- **WHEN** проверяется каталог `app/Http/Controllers/Auth/`
- **THEN** в нём отсутствуют перечисленные устаревшие контроллеры, а действующие маршруты аутентификации работают без их участия

#### Scenario: Старые auth-представления удалены
- **WHEN** проверяется каталог `resources/views/auth/`
- **THEN** папка `passwords/` и файл `verify.blade.php` отсутствуют, а используемые представления Breeze (`login`, `register`, `forgot-password`, `reset-password`, `confirm-password`, `verify-email`) остаются работоспособными
