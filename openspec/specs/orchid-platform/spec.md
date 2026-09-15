## Purpose

Определяет требования к миграции проекта на последние стабильные версии Laravel 13 и Orchid 14.53, включая приведение зависимостей, структуры ядра приложения и кода панели в соответствие с новым стеком.

## Requirements

### Requirement: Объявление версий laravel/framework и orchid/platform
Проект MUST объявлять в `composer.json` ограничения версий для последних стабильных релизов: `laravel/framework: ^13.0` и `orchid/platform: ^14.53`.

#### Scenario: Версии объявлены корректно
- **WHEN** проверяется секция `require` в `composer.json`
- **THEN** присутствуют записи `"laravel/framework": "^13.0"` и `"orchid/platform": "^14.53"`

#### Scenario: Согласованность lock-файла
- **WHEN** выполняется `composer install`
- **THEN** `composer.lock` MUST содержать совместимые версии `laravel/framework` (13.x) и `orchid/platform` (14.x), а установка завершается без ошибок несовместимости

### Requirement: Пересборка composer.lock и установка vendor
Lock-файл MUST быть пересобран под новые объявления (`composer update`), а каталог `vendor/` — установлен, чтобы проект собирался на целевом стеке.

#### Scenario: Пересборка зависимостей
- **WHEN** запускается `composer update` в среде с совместимым PHP
- **THEN** `composer.lock` обновляется до Laravel 13.x и Orchid 14.x, а `vendor/` заполняется соответствующими пакетами

#### Scenario: Разрешение конфликтов пакетов
- **WHEN** какие-либо пакеты (`laravel/ui`, `mews/captcha` и др.) несовместимы с Laravel 13
- **THEN** их объявления корректируются или пакеты удаляются так, чтобы Composer разрешил граф зависимостей

### Requirement: Миграция ядра приложения на структуру Laravel 11+/13
Структура ядра MUST соответствовать современному стилю Laravel: конфигурация приложения сосредоточена в `bootstrap/app.php`, список провайдеров в `bootstrap/providers.php`, а старые `app/Http/Kernel.php` и `app/Console/Kernel.php` отсутствуют.

#### Scenario: Bootstrap приложения
- **WHEN** приложение загружается
- **THEN** middleware, маршруты и обработчик исключений регистрируются через `bootstrap/app.php` (`withMiddleware`, `withRouting`, `withExceptions`), а список провайдеров задан в `bootstrap/providers.php`

#### Scenario: Отсутствие устаревших kernel-классов
- **WHEN** проверяется структура приложения
- **THEN** файлы `app/Http/Kernel.php` и `app/Console/Kernel.php` удалены, а их конфигурация перенесена в `bootstrap/app.php`

### Requirement: Работоспособность аутентификации после миграции
Замена удалённого `Auth::routes()` MUST обеспечить сохранение существующей аутентификации без изменения её логики.

#### Scenario: Маршруты аутентификации
- **WHEN** проверяются маршруты
- **THEN** маршруты входа/регистрации/восстановления пароля определены явно (вместо `Auth::routes()`) и указывают на существующие контроллеры в `app/Http/Controllers/Auth/`

### Requirement: Адаптация кода панели под Orchid 14
Код Orchid (конфигурация, провайдер, экраны, layout, presenter, фильтры, маршруты) MUST работать без ошибок совместимости на Orchid 14.

#### Scenario: Работоспособность панели
- **WHEN** открываются экраны панели и выполняются запросы через `routes/platform.php`
- **THEN** конфигурация `config/platform.php`, `app/Orchid/PlatformProvider.php` и все экраны/layout/presenters/фильтры работают без ошибок совместимости с Orchid 14

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
