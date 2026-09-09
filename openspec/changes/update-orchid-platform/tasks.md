## 1. Пересборка зависимостей на Laravel 13 + Orchid 14

- [ ] 1.1 Запустить `composer update` в среде с совместимым PHP (Docker `php:8.5-fpm` либо хост с `--ignore-platform-req=php` из-за требования `^8.5`) и убедиться, что `composer.lock` пересобран
- [ ] 1.2 Проверить фактический результат: `grep -A1 '"name": "laravel/framework"' composer.lock` → версия 13.x; `grep -A1 '"name": "orchid/platform"' composer.lock` → версия 14.x
- [ ] 1.3 Устранить конфликты пакетов, несовместимых с Laravel 13 (например, `laravel/ui`): скорректировать или удалить объявления в `composer.json`, повторить `composer update` до успешного разрешения графа зависимостей
- [ ] 1.4 Установить зависимости: `composer install` → появился каталог `vendor/`
- [ ] 1.5 Проверить валидность `composer.json` (`php -r "json_decode(file_get_contents('composer.json')); echo json_last_error()===JSON_ERROR_NONE?'valid':'invalid';"` → `valid`)

## 2. Миграция ядра приложения на структуру Laravel 11+/13

- [ ] 2.1 Создать `bootstrap/providers.php` со списком провайдеров приложения (AppServiceProvider, Orchid-провайдеры и др.)
- [ ] 2.2 Переписать `bootstrap/app.php` на новый стиль: `->withRouting(...)`, `->withMiddleware(...)`, `->withExceptions(...)`; перенести конфигурацию middleware и групп из старого `app/Http/Kernel.php`
- [ ] 2.3 Удалить `app/Http/Kernel.php` и `app/Console/Kernel.php` (их конфигурация перенесена в `bootstrap/app.php`)
- [ ] 2.4 Модернизировать `app/Exceptions/Handler.php` под новый формат (логика регистрации исключений переносится в `withExceptions()` в `bootstrap/app.php`)
- [ ] 2.5 Адаптировать сопутствующие конфиги под структуру Laravel 11+/13 (при необходимости) и проверить `php artisan about` / загрузку приложения

## 3. Сохранение аутентификации после удаления Auth::routes()

- [ ] 3.1 Заменить `Auth::routes()` в `routes/web.php` явной регистрацией маршрутов входа/регистрации/восстановления пароля на существующие контроллеры в `app/Http/Controllers/Auth/`, сохранив прежние имена маршрутов
- [ ] 3.2 Проверить `php artisan route:list` — маршруты auth присутствуют

## 4. Адаптация кода панели под Orchid 14

- [ ] 4.1 Обновить `config/platform.php` под новые ключи/требования Orchid 14 (сверка с официальной документацией)
- [ ] 4.2 Адаптировать `app/Orchid/PlatformProvider.php` под API Orchid 14 (меню, права, dashboard)
- [ ] 4.3 Адаптировать экраны, layout, presenter и фильтры в `app/Orchid/` под Orchid 14 по фактическим ошибкам совместимости
- [ ] 4.4 Проверить `routes/platform.php` и доступность экранов панели

## 5. Проверка работоспособности

- [ ] 5.1 `php artisan route:list`, `php artisan about` — приложение корректно загружается
- [ ] 5.2 Открыть публичные страницы (главная, статья, комментарии) и панель `/admin` — без ошибок совместимости
- [ ] 5.3 Проверить вход/регистрацию пользователя (сохранённая функциональность auth)

## 6. Вне рамок этого изменения (отдельные будущие изменения)

- Миграция фронтенда с `laravel-mix` на Vite.
- Модернизация аутентификации (переход на Breeze/Fortify).
- Пересборка публичных ассетов и прочая модернизация публичной части.
