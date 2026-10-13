# 🚀 Установка и запуск (только Docker)

> **Важно:** приложение запускается **только через Docker Compose**. Локальные
> PHP/Composer/npm/MySQL на хосте не используются — все команды выполняются
> внутри контейнеров через `Makefile`.
>
> Все команды выполняются из корня репозитория.

## Требования

- **Docker Engine** 24+ и **Docker Compose** (плагин `docker compose`)
- **Make** (для быстрой установки; без него — см. «Ручной запуск» ниже)

## 1. Клонирование репозитория

```bash
git clone git@github.com:alexchasx/laravel-orchid-blog.git
cd laravel-orchid-blog
```

## 2. Установка

```bash
make install
```

Команда выполняет полную настройку: собирает образы, копирует `.env.example` → `.env`,
запускает контейнеры и вызывает `make setup`. Сама установка (в уже запущенном Docker):

```bash
make setup
```

`make setup` устанавливает PHP-зависимости (`composer install`), генерирует `APP_KEY`,
запускает `migrate:fresh --seed`, создаёт администратора Orchid, делает `storage:link`,
ставит JS-зависимости (`npm install`) и собирает фронтенд. Команду удобно перезапускать
повторно, если контейнеры уже подняты и нужно полностью настроить проект заново.

Доступ после установки:

- Сайт: [http://localhost:8080](http://localhost:8080)
- Админ-панель Orchid: [http://localhost:8080/nexus](http://localhost:8080/nexus) — пользователь `admin@localhost.ru` / `123456`
- phpMyAdmin: [http://localhost:8899](http://localhost:8899)
- MailHog: [http://localhost:8026](http://localhost:8026)

> ⚠️ `make setup` (и `make install`) включает шаг **`migrate:fresh --seed`** — команда
> **разрушает** базу данных при повторном запуске.

## 3. Повседневные команды

```bash
make up     # собрать образы и поднять контейнеры
make down   # остановить контейнеры
make logs   # следить за логами
make shell  # войти в контейнер app (bash)
```

Приложение и его сервисы описаны в `../docker/docker-compose.yml`:

| Сервис      | Контейнер        | Порт хост → контейнер |
|-------------|------------------|------------------------|
| nginx       | `blog_nginx`     | 8080 → 80              |
| PHP-FPM     | `blog_app`       | —                      |
| Node.js     | `blog_node`      | 5173 → 5173 (Vite dev) |
| MySQL 8.0   | `blog_db`        | 8101 → 3306            |
| phpMyAdmin  | `blog_phpmyadmin`| 8899 → 80              |
| MailHog     | `blog_mailhog`   | 8026 → 8025            |

> **Продакшн-образы — отдельные теги.** Прод-стек описан в
> `../docker/docker-compose.prod.yml` и собирает образы `blog_app_prod`
> (app/schedule/queue) и `blog_nginx_prod` (nginx), чтобы не переиспользовать
> и не перезаписывать dev-образы `blog_app`/`blog_nginx`.

Полный список команд Makefile — `make help`.

## Ручной запуск (без `make`)

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

> **Примечание:** команды `docker compose -f docker/docker-compose.yml ...` выполняются
> из корня репозитория — не меняйте путь к `docker-compose.yml`.
