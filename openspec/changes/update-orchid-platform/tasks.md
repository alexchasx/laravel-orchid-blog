## 1. Обновление объявления версии orchid/platform

- [ ] 1.1 Изменить в `composer.json` значение `"orchid/platform": "^12.4"` на `"^14.53"` и убедиться, что строка теперь `"orchid/platform": "^14.53"`, а файл остаётся валидным JSON (`php -r "json_decode(file_get_contents('composer.json')); echo json_last_error() === JSON_ERROR_NONE ? 'valid' : 'invalid';"` возвращает `valid`)
- [ ] 1.2 Убедиться, что `composer update`/`composer install` НЕ запускались и `composer.lock` остался без изменений (проверка: `git status` не показывает изменённого `composer.lock`)

## 2. Проверка соответствия спецификации

- [ ] 2.1 Убедиться, что секция `require` в `composer.json` содержит `"orchid/platform": "^14.53"` (соответствует `Requirement: Объявление версии orchid/platform` из `specs/orchid-platform/spec.md`)

## 3. Отложенные шаги (выполняет пользователь отдельно, вне рамок этого изменения)

> Эти шаги НЕ выполняются в рамках apply-фазы данного изменения. Они документируют обязательное продолжение после обновления окружения пользователем.

- Обновить окружение до совместимого с Orchid 14: Laravel `^10.0 || ^11.0 || ^12.0 || ^13.0` и более новый PHP.
- Обновить сопутствующие пакеты Orchid (`orchid/blade-icons`, `tabuna/breadcrumbs`, `laravel/scout` и др.).
- Выполнить фактическую установку: `composer update orchid/platform` (или полное обновление) и пересборку `composer.lock`.
- Адаптировать код `app/Orchid/` и `config/platform.php` под API Orchid 14 и проверить работоспособность панели.
