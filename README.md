# Laravel Orchid Blog

Блог на базе **Laravel** с административной панелью **[Orchid](https://orchid.software/)**.

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

### Frontend
- **Vite** ^6.0 + **laravel-vite-plugin** — сборка фронтенда
- **Tailwind CSS** ^3.4 + **@tailwindcss/forms**
- **Alpine.js** ^3.14
- **Sass** + **PostCSS** + **Autoprefixer**

### База данных и инфраструктура
- **MySQL** 8.0 (докеризированная)
- **Docker / Docker Compose** (nginx, PHP-FPM 8.5, MySQL, phpMyAdmin)

### Инструменты разработки
- **Laravel Breeze** — каркас аутентификации
- **Laravel IDE Helper**, **Faker**

---

## 🚀 Установка и запуск локально

### Требования
- **PHP** 8.5+
- **Composer** 2+
- **Node.js** 18+ и **npm**
- **MySQL** 8.0 (или **Docker**)

### 1. Клонирование репозитория

```bash
git clone git@github.com:alexchasx/laravel-orchid-blog.git
cd laravel-orchid-blog
```

### 2. Установка зависимостей

```bash
composer install
npm install
```

### 3. Настройка окружения

```bash
cp .env.example .env
php artisan key:generate
```

При необходимости отредактируйте `.env`: настройки базы данных (`DB_*`), почты (`MAIL_*`), URL приложения (`APP_URL`) и т.д.

Для подключения к БД без Docker используется `DB_HOST=127.0.0.1`. Если вы используете БД из Docker-контейнера — закомментируйте эту строку и раскомментируйте `DB_HOST=db`.

### 4. Миграции и сиды

```bash
php artisan migrate --seed
```

### 5. Создание администратора (для панели Orchid)

```bash
php artisan orchid:admin admin email@example.com 123456
```

> Пользователь создаётся с максимальными правами на момент создания.

### 6. Хранилище и сборка фронтенда

```bash
php artisan storage:link
npm run dev   # или npm run build для production
```

### 7. Запуск сервера

```bash
php artisan serve
```

Приложение будет доступно по адресу: [http://localhost:8000](http://localhost:8000)

Админ-панель Orchid: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## 🐳 Запуск через Docker Compose

В проекте есть собственный `docker-compose.yml`, который поднимает четыре сервиса:

| Сервис      | Контейнер       | Порт хост → контейнер |
|-------------|-----------------|------------------------|
| nginx       | `blog_nginx`    | 8080 → 80              |
| PHP-FPM     | `blog_app`      | —                      |
| MySQL 8.0   | `blog_db`       | 8101 → 3306            |
| phpMyAdmin  | `blog_phpmyadmin` | 8899 → 80            |

### Шаги запуска

```bash
# 1. Скопировать и настроить .env (DB_HOST должен быть "db")
cp .env.example .env

# 2. Собрать и запустить контейнеры
docker compose up -d --build

# 3. Установить зависимости PHP внутри контейнера
docker compose exec app composer install

# 4. Сгенерировать ключ приложения
docker compose exec app php artisan key:generate

# 5. Миграции и сиды
docker compose exec app php artisan migrate --seed

# 6. Создать администратора
docker compose exec app php artisan orchid:admin admin email@example.com 123456

# 7. Хранилище
docker compose exec app php artisan storage:link
```

Доступ:
- Сайт: [http://localhost:8080](http://localhost:8080)
- Админ-панель: [http://localhost:8080/admin](http://localhost:8080/admin)
- phpMyAdmin: [http://localhost:8899](http://localhost:8899)

> ⚠️ При работе через Docker не забудьте в `.env` раскомментировать `DB_HOST=db` и выставить параметры подключения к БД (`DB_DATABASE=laraorchid`, `DB_USERNAME=root`, `DB_PASSWORD=root`), либо привести их в соответствие с [docker/docker-compose.yml](docker/docker-compose.yml).

---

## 📂 Структура проекта

- `app/Orchid/` — экраны, layout'ы и фильтры административной панели Orchid
- `app/Http/Controllers/` — контроллеры публичной части и авторизации
- `database/migrations/` и `database/seeders/` — миграции и сиды
- `resources/views/` — Blade-шаблоны (публичная часть, auth, компоненты)
- `routes/` — маршруты приложения
- `docker/` — конфигурация Docker (nginx, PHP-FPM, MySQL, phpMyAdmin)
- `lang/` — языковые файлы (ru, en)
