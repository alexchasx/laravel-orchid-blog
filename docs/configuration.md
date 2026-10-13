# 🔧 Что поменять после создания

Шаблон отдаётся с нейтральными настройками и демо-контентом. Перед публикацией
замените их на свои — список от обязательного до «по желанию».

## Обязательно

| Что | Где | Зачем |
|---|---|---|
| `APP_NAME` | `.env` | Имя сайта: `<title>` и OG-теги, футер, префиксы кэша/сессий/Redis (`../config/app.php`, `../resources/views/layouts/techlog.blade.php`) |
| `APP_URL` | `.env` | Адрес сайта: генерация URL, ссылки в письмах (рассылка, отписка), URL загрузок (`../config/app.php`, `../config/filesystems.php`) |
| Пароль админа | Orchid | `make setup` создаёт `admin@localhost.ru` / `123456` — **смените пароль сразу** |
| Пароль тестового автора | `../database/seeders/DatabaseSeeder.php` | Сид создаёт пользователя `author@example.test` / `password` — удалите или пересоздайте |

> После смены `APP_NAME` выполните `make clear`: префиксы кэша, сессий и Redis
> считаются через `Str::slug(env('APP_NAME'))`, старые ключи останутся в мусоре.

## Названия и тексты проекта

| Что | Где | Зачем |
|---|---|---|
| `composer.json` → `name` | корневой файл | Имя пакета (по умолчанию `laravel/laravel`) |
| `composer.json` → `description`, `keywords` | корневой файл | Описание пакета (по умолчанию «The Laravel Framework.», ключи Laravel) |
| `package.json` → `name` | корневой файл | Имя npm-пакета (в шаблоне — `laravel-blog-template`; пакет помечен `private`, менять необязательно) |
| Название в шапке | `../resources/views/layouts/techlog.blade.php:44`  задан вручную, а не через `APP_NAME` |
| `robots.txt` | `GET /robots.txt` → `RobotsController` | Динамический: блокирует служебные пути и содержит `Sitemap: {APP_URL}/sitemap.xml` |

## Логотип и favicon

| Что | Где | Зачем |
|---|---|---|
| `favicon.ico` | `../public/favicon.ico` | Заглушка Laravel. Подключается в `techlog` — замените на свой |
| `apple-touch-icon.png` | `../public/apple-touch-icon.png` | Иконка для iOS-закладок (180×180), заглушка-акцент |
| `site.webmanifest` | `../public/site.webmanifest` | PWA-манифест (name, icons, theme_color), опционально |
| Логотип / OG-изображение | `public/` | Не поставляются: OG-картинка генерируется из загруженного изображения статьи |

Ассеты Orchid (`../public/vendor/orchid/`) — это опубликованная копия ассетов пакета,
она нужна для работы админки. После обновления `orchid/platform` переопубликуйте их:
`php artisan orchid:publish`.

## Настройки Orchid (`../config/platform.php`)

| Параметр | Env-переменная | По умолчанию | Зачем менять |
|---|---|---|---|
| `prefix` | `PLATFORM_PREFIX` | `/nexus` | Адрес админки (например `/panel`) |
| `domain` | `PLATFORM_DOMAIN` | — | Домен, если админка на отдельном поддомене |
| `middleware` | — | стандартный стек | Дополнительные middleware админки |
| `template.header` / `template.footer` | — | `''` | Свои header/footer-шаблоны Orchid |
| `notifications.enabled` | — | `true` | Отключить периодические уведомления Orchid |
| `search` | — | `[]` | Добавить модели (`\App\Models\User::class`) в поиск по сайдбару |
| `attachment.disk` | `PLATFORM_FILESYSTEM_DISK` | `public` | Диск для вложений Orchid |

> Права, меню и экраны админки заданы в `../app/Orchid/PlatformProvider.php`
> (пункты меню, пермишены `platform.custom.*`).

## Контакты и подписи (`../config/my_config.php`)

Все значения читаются из `.env` — править PHP-конфиг не нужно:

| Переменная | Где используется |
|---|---|
| `SLOGAN` | футер `../resources/views/layouts/techlog.blade.php` |
| `MY_GITHUB`, `MY_TELEGRAM` | ссылки в футере (показываются, только если значение не пустое) |
| `CONTACT_EMAIL` | страница «Контакты» (`../resources/views/contact.blade.php`); ссылка показывается, только если значение не пустое |
| `MAIL_FROM_ADDRESS` | отправитель писем рассылки |

## Демо-контент

`../database/seeders/DatabaseSeeder.php` наполняет базу стартовым содержимым:
6 рубрик, 21 метку, 25 статей (все опубликованы) и тестового автора
`author@example.test`. Свои данные удобно добавить
в этот же сидер; чтобы начать с чистой базы — отключите вызов сидера и выполните
`docker compose -f docker/docker-compose.yml exec app php artisan migrate:fresh`
(⚠️ команда **удаляет** все данные).

## Перед деплоем

> Полный порядок действий — в разделе [deployment.md](deployment.md); этот чек-лист — краткая выжимка для локальной подготовки.

- [ ] `docker/docker-compose.yml` — замените `phpMyAdmin`/`MailHog`/Vite-dev-сервисы на то, что нужно на сервере; собственный `nginx` конфиг — в `docker/nginx/`.
- [ ] `MAIL_*` в `.env` — реальный SMTP вместо `mailhog`; `QUEUE_CONNECTION=database` требует запущенного `php artisan queue:work` (контейнер `blog_queue`).
- [ ] `php artisan schedule:work` (контейнер `blog_schedule`) — без него запланированные статьи не публикуются.
- [ ] `APP_DEBUG=false` и корректный `APP_ENV=production` на сервере.
- [ ] Права `www-data` на `storage/` и `bootstrap/cache` (`make storage-perm`).
- [ ] Сборка фронтенда: `make frontend-build` (Vite, 5 входных точек в `../vite.config.js`).
