## Why

Проект фактически работает на устаревшем стеке: [`composer.lock`](../../composer.lock) фиксирует **Laravel 9.28 / Orchid 12.6 / Sanctum 2.15**, а [`composer.json`](../../composer.json) уже объявляет последние версии — `laravel/framework: ^13.0`, `orchid/platform: ^14.53`, PHP `^8.5`. Объявления и lock-файл рассинхронизированы, `vendor/` не установлен, структура приложения и код панели остались в стиле Laravel 9.

Требуется завершить реальную миграцию на **Laravel 13** (последний стабильный) и **Orchid 14.53** (последний стабильный), чтобы стек соответствовал современному API, получал актуальные исправления и проект снова собирался/работал.

> Примечание: предыдущая версия этого change планировала лишь правку строки версии без запуска Composer. Эта предпосылка устарела: строка версии уже обновлена, и фактически осталась полноценная миграция. Артефакты переписаны под реальный объём работ.

## What Changes

- **Зависимости:** пересобрать `composer.lock` (`composer update`) и установить `vendor/`, приведя зависимости к `laravel/framework ^13` + `orchid/platform ^14.53`. Разрешить конфликты пакетов, несовместимых с Laravel 13 (например, `laravel/ui`), при необходимости скорректировав объявления.
- **Ядро Laravel (миграция 9 → 13):**
  - Переписать [`bootstrap/app.php`](../../bootstrap/app.php) на новый стиль Laravel 11+: регистрация middleware, маршрутов и обработчика исключений через `->withMiddleware()`, `->withRouting()`, `->withExceptions()`.
  - Создать [`bootstrap/providers.php`](../../bootstrap/providers.php) со списком провайдеров приложения.
  - Удалить [`app/Http/Kernel.php`](../../app/Http/Kernel.php) и [`app/Console/Kernel.php`](../../app/Console/Kernel.php); их конфигурация переносится в `bootstrap/app.php`.
  - Модернизировать [`app/Exceptions/Handler.php`](../../app/Exceptions/Handler.php) под новый формат (как правило, становится почти пустым; логика уходит в `withExceptions()`).
  - Адаптировать сопутствующие файлы конфигурации под структуру Laravel 11+/13.
- **Auth (минимально-вынужденно, без редизайна):** заменить удалённый `Auth::routes()` в [`routes/web.php`](../../routes/web.php:30) явной регистрацией маршрутов на существующие контроллеры в `app/Http/Controllers/Auth/`, чтобы вход/регистрация продолжали работать. Полноценная модернизация auth (Breeze/Fortify) — **вне рамок** этого изменения.
- **Orchid 12 → 14:** адаптировать [`config/platform.php`](../../config/platform.php), [`app/Orchid/PlatformProvider.php`](../../app/Orchid/PlatformProvider.php), экраны/layout/presenters/filters в `app/Orchid/` и [`routes/platform.php`](../../routes/platform.php) под API Orchid 14.
- **Фронтенд:** оставить на `laravel-mix` (миграция на Vite — **вне рамок**).

## Capabilities

### Modified Capabilities
- `orchid-platform`: Требования обновлены с «правки строки версии» на полную миграцию проекта на Laravel 13 и Orchid 14 (ядро, зависимости, код панели). Соответствует ранее созданной capability `specs/orchid-platform/spec.md`.

## Impact

- [`composer.json`](../../composer.json) — возможная корректировка объявлений для разрешения совместимости пакетов.
- [`composer.lock`](../../composer.lock) — полная пересборка; появление `vendor/`.
- [`bootstrap/app.php`](../../bootstrap/app.php), новый [`bootstrap/providers.php`](../../bootstrap/providers.php).
- Удаление [`app/Http/Kernel.php`](../../app/Http/Kernel.php) и [`app/Console/Kernel.php`](../../app/Console/Kernel.php); обновление [`app/Exceptions/Handler.php`](../../app/Exceptions/Handler.php).
- [`routes/web.php`](../../routes/web.php) — замена `Auth::routes()` на явные маршруты.
- `config/platform.php`, `app/Orchid/*`, `routes/platform.php` — адаптация под Orchid 14.
- Окружение: хост PHP 8.4 и Docker PHP 8.5; `composer.json` требует PHP `^8.5` — при локальном `composer update` на хосте может потребоваться флаг игнорирования платформенных требований либо запуск в Docker.
