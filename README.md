# Laravel Orchid Blog

Блог на базе **Laravel** с административной панелью **[Orchid](https://orchid.software/)**.

![Скрин главной](screenshots/home.png)

---

## ✨ Возможности «из коробки»

Всё перечисленное уже работает — ничего не нужно дописывать, чтобы получить рабочий блог.

- **Лента статей** — главная страница с пагинацией, полнотекстовым поиском, выводом рубрик и меток.
- **Страница статьи** — Markdown-контент, автоматически собранное оглавление, теги, рубрика, блок «Похожие статьи».
- **Рубрики и метки** — страницы `/rubric/{slug}` и `/tag/{slug}` (legacy `/rubric/{id}` → 301).
- **Гостевые комментарии** — математическая капча, модерация, защита от спама (`throttle`), согласия на обработку ПДн (152-ФЗ).
- **Подписка на рассылку** — письма через очередь, отписка по одноразовому токену, автопубликация запланированных статей.
- **Форма обратной связи** — `/contact` с валидацией, заявки сохраняются и видны в админке.
- **Админ-панель Orchid** (`/nexus`) — CRUD статей, рубрик и меток, модерация комментариев, подписчики, роли и права.
- **SEO** — title/description, Open Graph, Twitter Card, JSON-LD, canonical, noindex, sitemap.xml, robots.txt, RSS 2.0-лента.
- **Тёмная/светлая тема**, двуязычность (ru/en), хлебные крошки, gzip, кнопка «Копировать» у блоков кода.
- **Импорт статей из Markdown** — консольная команда `articles:import` с защитой от дублей.

> Полный список — в [docs/features.md](docs/features.md).

---

## 📋 Используемые технологии

### Backend
- **PHP** ^8.5, **Laravel Framework** ^13.0, **Orchid Platform** ^14.53, **Laravel Sanctum** ^4.0

### Frontend (Blade + Vite)
- **Vite** ^6.0, **Tailwind CSS** ^3.4, **Alpine.js** ^3.14, **Sass** + PostCSS + Autoprefixer

### База данных и инфраструктура
- **MySQL** 8.0, **Docker / Docker Compose** (nginx, PHP-FPM, Node.js, phpMyAdmin, MailHog, воркеры `schedule`/`queue`)

---

## 🚀 Быстрый старт (только Docker)

> Приложение запускается **только через Docker Compose** — локальные PHP/Composer/npm/MySQL на хосте не используются.

### Требования

- **Docker Engine** 24+ и **Docker Compose** (плагин `docker compose`)
- **Make** (для быстрой установки; без него — см. «Ручной запуск» в [docs/installation.md](docs/installation.md))

### Установка

```bash
git clone git@github.com:alexchasx/laravel-orchid-blog.git
cd laravel-orchid-blog
make install
```

`make install` собирает образы, копирует `.env.example` → `.env`, запускает контейнеры и вызывает `make setup`:
composer/npm install, `key:generate`, **`migrate:fresh --seed`**, создание администратора Orchid, `storage:link`, сборка фронтенда.

> ⚠️ `make setup` (и `make install`) включает шаг **`migrate:fresh --seed`** — команда
> **разрушает** базу данных при повторном запуске.

### Доступы после установки

| Что | Адрес |
|---|---|
| Сайт | [http://localhost:8080](http://localhost:8080) |
| Админ-панель Orchid | [http://localhost:8080/nexus](http://localhost:8080/nexus) — `admin@localhost.ru` / `123456` |
| phpMyAdmin | [http://localhost:8899](http://localhost:8899) |
| MailHog | [http://localhost:8026](http://localhost:8026) |

### Повседневные команды

| Команда | Что делает |
|---|---|
| `make up` / `make down` | собрать образы и поднять / остановить контейнеры |
| `make logs` | следить за логами |
| `make shell` | войти в контейнер `app` (bash) |
| `make test` | PHPUnit-тесты (`php artisan test` внутри `blog_app`) |
| `make frontend-build` | сборка фронтенда (Vite) |
| `make migrate` | **`migrate:fresh --seed`** (⚠️ разрушает данные) |
| `make help` | полный список команд Makefile |

> Подробная инструкция, ручной запуск без `make` и таблица сервисов — в [docs/installation.md](docs/installation.md).

---

## 📂 Структура проекта

- `app/` — модели, контроллеры, сервисы, обсерверы, консольные команды, админ-панель Orchid (`app/Orchid/`)
- `config/` — конфигурация, в т.ч. `my_config.php` (соцсети, контакты, слоган)
- `database/` — миграции, сиды, фабрики
- `resources/` — Blade-шаблоны, стили (Sass), скрипты (JS)
- `routes/` — маршруты (`web.php`, `platform.php`, `auth.php`, `console.php`)
- `docker/` — Docker Compose (dev и prod), nginx, Dockerfile
- `tests/` — PHPUnit-тесты
- `docs/` — документация проекта

---

## 📚 Документация

| Раздел | Файл |
|---|---|
| Возможности «из коробки» | [docs/features.md](docs/features.md) |
| Используемые технологии | [docs/technologies.md](docs/technologies.md) |
| Установка и запуск | [docs/installation.md](docs/installation.md) |
| Что поменять после создания | [docs/configuration.md](docs/configuration.md) |
| Деплой на продакшн | [docs/deployment.md](docs/deployment.md) |
| Структура проекта | [docs/project-structure.md](docs/project-structure.md) |
| Архитектура | [docs/architecture.md](docs/architecture.md) |

## 🤝 Вклад

См. [CONTRIBUTING.md](CONTRIBUTING.md).
