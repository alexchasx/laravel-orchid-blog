# Laravel Orchid Blog

Блог на базе **Laravel** с административной панелью **[Orchid](https://orchid.software/)**.

![Скрин главной](screenshots/home.png)

---

## 📋 Используемые технологии

### Backend
- **PHP** ^8.5
- **Laravel Framework** ^13.0
- **Orchid Platform** ^14.53 — административная панель
- **Laravel Sanctum** ^4.0 — аутентификация и API токены
- **Laravel Tinker** ^3.0 — интерактивная работа с приложением
- **mews/captcha** ^3.3 — капча (в связке с Google reCAPTCHA через middleware `GoogleRecaptcha`)
- **Guzzle HTTP** ^7.9 — HTTP-клиент

### Frontend (Blade-шаблоны Breeze/auth)
- **Vite** ^6.0 + **laravel-vite-plugin** — сборка ассетов Breeze
- **Tailwind CSS** ^3.4 + **@tailwindcss/forms**
- **Alpine.js** ^3.14
- **Sass** + **PostCSS** + **Autoprefixer**

### База данных и инфраструктура
- **MySQL** 8.0
- **Docker / Docker Compose** (nginx, PHP-FPM 8.5, Node.js, MySQL, phpMyAdmin, MailHog)

### Инструменты разработки
- **Laravel Breeze** — каркас аутентификации
- **Laravel IDE Helper**, **Faker**

---

## 🚀 Установка и запуск (только Docker)

> **Важно:** приложение запускается **только через Docker Compose**. Локальные
> PHP/Composer/npm/MySQL на хосте не используются — все команды выполняются
> внутри контейнеров через `Makefile`.

### Требования
- **Docker Engine** 24+ и **Docker Compose** (плагин `docker compose`)
- **Make** (для быстрой установки; без него — см. «Ручной запуск» ниже)

### 1. Клонирование репозитория

```bash
git clone git@github.com:alexchasx/laravel-orchid-blog.git
cd laravel-orchid-blog
```

### 2. Установка

```bash
make install
```

Команда выполняет полную настройку: собирает образы, копирует `.env.example` → `.env`,
устанавливает PHP- и JS-зависимости, генерирует `APP_KEY`, запускает
`migrate:fresh --seed`, создаёт администратора Orchid, делает `storage:link`
и собирает фронтенд.

Доступ после установки:
- Сайт: [http://localhost:8080](http://localhost:8080)
- Админ-панель Orchid: [http://localhost:8080/admin](http://localhost:8080/admin) — пользователь `admin@localhost.ru` / `123456`
- phpMyAdmin: [http://localhost:8899](http://localhost:8899)
- MailHog: [http://localhost:8026](http://localhost:8026)

> ⚠️ `make migrate` из `make install` выполняет **`migrate:fresh --seed`** — команда
> **разрушает** базу данных при повторном запуске.

### 3. Повседневные команды

```bash
make up     # собрать образы и поднять контейнеры
make down   # остановить контейнеры
make logs   # следить за логами
make shell  # войти в контейнер app (bash)
```

Приложение и его сервисы описаны в `docker/docker-compose.yml`:

| Сервис      | Контейнер        | Порт хост → контейнер |
|-------------|------------------|------------------------|
| nginx       | `blog_nginx`     | 8080 → 80              |
| PHP-FPM     | `blog_app`       | —                      |
| Node.js     | `blog_node`      | 5173 → 5173 (Vite dev) |
| MySQL 8.0   | `blog_db`        | 8101 → 3306            |
| phpMyAdmin  | `blog_phpmyadmin`| 8899 → 80              |
| MailHog     | `blog_mailhog`   | 8026 → 8025            |

Полный список команд Makefile — `make help`.

### Ручной запуск (без `make`)

```bash
cp .env.example .env

docker compose -f docker/docker-compose.yml up -d --build

docker compose -f docker/docker-compose.yml exec app composer install
docker compose -f docker/docker-compose.yml exec app php artisan key:generate
docker compose -f docker/docker-compose.yml exec app php artisan migrate:fresh --seed
docker compose -f docker/docker-compose.yml exec app php artisan orchid:admin admin admin@localhost.ru 123456
docker compose -f docker/docker-compose.yml exec app php artisan storage:link

docker compose -f docker/docker-compose.yml exec node npm install
docker compose -f docker/docker-compose.yml exec node npm run build
```

---

## 📂 Структура проекта

### Приложение `app/`
- `app/Models/` — доменные модели (`Article`, `Rubric`, `Tag`, `Comment`, `Contact`, `Subscriber`, `User`)
- `app/Http/Controllers/` — контроллеры публичной части и авторизации (Breeze)
- `app/Http/Requests/` — FormRequest-валидация (статьи, комментарии, подписка, контакты)
- `app/Http/Middleware/` — кастомные middleware (`Localize`, `GoogleRecaptcha`, `TrustProxies`)
- `app/Orchid/` — админ-панель Orchid: экраны (`Screens/`), layout'ы (`Layouts/`), фильтры, `PlatformProvider`
- `app/Services/` — сервисный слой (`ArticleService`, `CacheService`)
- `app/Observers/` — модель "отслеживания" (`ArticleObserver`, `RubricObserver`, `TagObserver`): обновление счётчиков, рассылка
- `app/Console/Commands/` — консольные команды (автопубликация статей по расписанию)
- `app/Mail/` — Mailable-классы (`NewArticleMail` — рассылка подписчикам)
- `app/Support/` и `app/Rules/` — капча и правила валидации (`MathCaptcha`, `MathCaptchaRule`)
- `app/Providers/` — сервис-провайдеры

### Настройки и данные
- `config/` — конфигурация, в т.ч. `my_config.php` (соцсети, контакты, слоган)
- `database/migrations/`, `database/seeders/`, `database/factories/` — миграции, сиды и фабрики
- `lang/` — языковые файлы (ru, en)

### HTTP и маршруты
- `routes/` — маршруты приложения (`web.php`, `platform.php`, `auth.php`, `console.php`)
- `bootstrap/app.php` — регистрация маршрутов и middleware (Laravel 13-стиль)

### Фронтенд
- `resources/views/` — Blade-шаблоны (публичная часть, layout `techlog`, auth, компоненты, emails)
- `resources/sass/` — стили (новый дизайн `techlog/` + legacy `style.scss`)
- `resources/js/` — скрипты (`techlog.js` — новый фронтенд, `app.js` — legacy/Alpine)
- `resources/css/` — CSS-ассеты
- `public/` — публично доступные файлы (собранные ассеты, изображения)
- `vite.config.js` — сборка фронтенда

### Инфраструктура и остальное
- `docker/` — конфигурация Docker (nginx, PHP-FPM, Node, MySQL, phpMyAdmin, MailHog)
- `tests/` — PHPUnit-тесты
- `docs/` — документация проекта (архитектура, планы миграции)
- `Makefile` — основной dev-workflow