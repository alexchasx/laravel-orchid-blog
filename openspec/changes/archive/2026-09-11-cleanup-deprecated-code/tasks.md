## 1. Устранение депрекаций Orchid 14

- [x] 1.1 Заменить `UserSwitch` на `Impersonation` в `app/Orchid/Screens/User/UserEditScreen.php` (импорт стр. 14 и вызов `loginAs` стр. 225) и убедиться, что `grep -n "UserSwitch" app/` не даёт результатов
- [x] 1.2 Заменить `getStatusPermission()` → `statusOfPermissions()` в `app/Orchid/Screens/User/UserEditScreen.php:43` и `app/Orchid/Screens/Role/RoleEditScreen.php:36`, после чего `grep -rn "getStatusPermission" app/` пуст

## 2. Устранение депрекаций Laravel 13 (Request::get → input)

- [x] 2.1 Заменить `$request->get(...)` → `$request->input(...)` в `app/Orchid/Filters/RoleFilter.php` (стр. 41, 54, 64) и проверить, что чтение параметра `role` работает (grep-проверка без `->get(` на объекте Request)
- [x] 2.2 Заменить `$request->get(...)` → `$request->input(...)` в экранах пользователя и роли: `UserEditScreen.php` (172, 179), `RoleEditScreen.php` (126, 128), `UserProfileScreen.php` (108, 126), `UserListScreen.php` (131)
- [x] 2.3 Убедиться, что `->get()` остались только на объектах `Collection`/`Repository` (например, `RolePermissionLayout.php`, `ExampleScreen.php`) и не затрагивают `Illuminate\Http\Request`; проверка: `grep -rn "\$request->get\|->request->get" app/` пуст

## 3. Удаление мёртвого auth-кода

- [x] 3.1 Удалить контроллеры `LoginController`, `RegisterController`, `ForgotPasswordController`, `ResetPasswordController`, `VerificationController`, `ConfirmPasswordController` из `app/Http/Controllers/Auth/` и убедиться, что `php artisan route:list` по-прежнему показывает все маршруты Breeze (login, register, password.*, verification.*, logout)
- [x] 3.2 Удалить папку `resources/views/auth/passwords/` и файл `resources/views/auth/verify.blade.php`, убедившись, что действующие представления (`forgot-password`, `reset-password`, `confirm-password`, `verify-email`, `login`, `register`) остались на месте

## 4. Верификация

- [x] 4.1 Выполнить `php artisan route:list` и убедиться, что приложение корректно загружается без ошибок совместимости после всех изменений
- [x] 4.2 Выполнить grep-проверку отсутствия депрецированных символов: `grep -rnE "UserSwitch|getStatusPermission|\$request->get" app/` не даёт результатов
- [x] 4.3 При доступности окружения PHP >= 8.5 (Docker `php:8.5-fpm` либо `--ignore-platform-req=php`) прогнать `php -l` по изменённым файлам и открыть панель `/admin` для подтверждения работоспособности
