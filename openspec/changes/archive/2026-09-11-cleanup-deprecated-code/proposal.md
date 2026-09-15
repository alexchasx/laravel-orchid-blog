## Why

Проект уже мигрирован на Laravel 13 (`v13.31.0`) и Orchid Platform `v14.53.0`, но в коде остались депрецированные вызовы этих версий и мёртвый код от старого стека. Нужно привести код в соответствие с целевыми версиями и убрать устаревшие конструкции, чтобы избежать ошибок совместимости в будущих релизах и упростить поддержку.

## What Changes

- **Заменить депрецированные API Orchid 14** (не-BREAKING, обратно совместимо):
  - `Orchid\Access\UserSwitch` → `Orchid\Access\Impersonation` в экране пользователя (`loginAs`).
  - `getStatusPermission()` → `statusOfPermissions()` в экранах пользователя и роли.
- **Заменить депрецированный `Request::get()` → `input()`** (Laravel 13) во всех экранах и фильтре панели. Не затрагиваются `->get()` на `Collection`/`Repository`.
- **Удалить мёртвый auth-слой** (неиспользуемый код): контроллеры `LoginController`, `RegisterController`, `ForgotPasswordController`, `ResetPasswordController`, `VerificationController`, `ConfirmPasswordController` и старые представления `auth/passwords/*`, `auth/verify.blade.php`.

## Capabilities

### New Capabilities

Нет новых возможностей.

### Modified Capabilities

- `orchid-platform`: изменение требований к коду панели — переход с депрецированных API Orchid 14 (`UserSwitch`, `getStatusPermission`) на актуальные (`Impersonation`, `statusOfPermissions`) и замена депрецированного `Request::get()` на `input()`.

## Impact

- **Код панели**: `app/Orchid/Screens/User/UserEditScreen.php`, `app/Orchid/Screens/Role/RoleEditScreen.php`, `app/Orchid/Screens/User/UserProfileScreen.php`, `app/Orchid/Screens/User/UserListScreen.php`, `app/Orchid/Filters/RoleFilter.php`.
- **Удаляемые файлы**: 6 auth-контроллеров и старые auth-view.
- **Зависимости/стек**: без изменения версий; только очистка кода под текущие Laravel 13 / Orchid 14.53.
