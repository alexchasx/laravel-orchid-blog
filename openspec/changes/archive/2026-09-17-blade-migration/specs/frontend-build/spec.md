## MODIFIED Requirements

### Requirement: R2: Входные точки сборки

`vite.config.js` **SHALL** объявлять следующие входные точки:
- `resources/css/app.css` — Tailwind, для Breeze-шаблонов (`app`, `guest`);
- `resources/sass/style.scss` — кастомные стили публичного блога;
- `resources/js/app.js` — общий JavaScript (Alpine);
- `resources/sass/techlog/index.scss` — стили techlog (новый дизайн);
- `resources/js/techlog.js` — скрипты techlog (новый дизайн).

#### Scenario: Сборка включает techlog

- **WHEN** выполняется `npm run build`
- **THEN** Vite собирает `techlog/index.scss` и `techlog.js` без ошибок

#### Scenario: Techlog-ассеты доступны

- **WHEN** `techlog.blade.php` использует `@vite(['resources/sass/techlog/index.scss', 'resources/js/techlog.js'])`
- **THEN** браузер получает собранные CSS и JS файлы
