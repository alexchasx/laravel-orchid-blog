# План: разбивка README.md на несколько файлов

> **Статус:** черновик плана. Исполнение — отдельной задачей (по этому плану).
> Все тексты — на русском, как и остальная документация проекта.

## 1. Цель

[`README.md`](../README.md) (463 строки) превращается в компактную «витрину»
репозитория: название, описание, скриншот, оглавление и быстрый старт.
Подробные разделы переезжают в отдельные документы `docs/*.md`, на которые
README ссылается. Это делает README читаемым с первого экрана, а детали —
поддерживаемыми по отдельности.

## 2. Текущее состояние README.md

| № | Раздел | Строки | Объём | Содержание |
|---|--------|--------|-------|------------|
| 1 | Заголовок + интро | 1–7 | 7 | Название, описание, скриншот `screenshots/home.png` |
| 2 | ✨ Возможности «из коробки» | 9–87 | 79 | 7 подразделов: публичная часть, комментарии, рассылка, обратная связь, админка Orchid, инфраструктура, импорт статей |
| 3 | 📋 Используемые технологии | 91–113 | 23 | Backend, Frontend, БД, инструменты |
| 4 | 🚀 Установка и запуск (только Docker) | 117–203 | 87 | Требования, клонирование, `make install`/`make setup`, повседневные команды, ручной запуск |
| 5 | 🔧 Что поменять после создания | 207–291 | 85 | Обязательно, названия, логотип/favicon, Orchid, `my_config`, демо-контент, чек-лист перед деплоем |
| 6 | 🚀 Деплой на продакшн (Ubuntu 24.04 VPS) | 295–422 | 128 | 7 шагов: сервер, DNS, TLS, первый деплой, обновления, очередь/бэкапы, smoke-тест |
| 7 | 📂 Структура проекта | 426–463 | 38 | `app/`, config, routes, фронтенд, инфраструктура |

## 3. Целевая структура

| Файл | Содержимое | Источник (строки README) |
|------|------------|--------------------------|
| [`README.md`](../README.md) | «Витрина»: заголовок, описание, скриншот, краткое введение, быстрый старт, оглавление-таблица ссылок на `docs/*.md` | 1–7 + новый текст |
| [`docs/features.md`](../docs/features.md) | Возможности «из коробки» (все 7 подразделов) | 9–87 |
| [`docs/technologies.md`](../docs/technologies.md) | Используемые технологии | 91–113 |
| [`docs/installation.md`](../docs/installation.md) | Установка и запуск (только Docker) | 117–203 |
| [`docs/configuration.md`](../docs/configuration.md) | Что поменять после создания | 207–291 |
| [`docs/deployment.md`](../docs/deployment.md) | Деплой на продакшн (Ubuntu 24.04 VPS) | 295–422 |
| [`docs/project-structure.md`](../docs/project-structure.md) | Структура проекта | 426–463 |

Итог: README ~60–80 строк, 6 новых документов в `docs/` (рядом с уже
существующими `architecture.md`, `privacy.md`, `provision-server.md`).

## 4. Пошаговый план

### Шаг 1. Создать `docs/features.md`
- Перенести строки 9–87 (раздел «✨ Возможности "из коробки"»).
- Заголовок `##` → `#`, подразделы `###` → `##`.
- Строку 83 «см. раздел "Что нужно поменять"» заменить ссылкой
  `[configuration.md](configuration.md)`.
- Блок «Чего пока нет» (строки 82–87) перенести целиком.

### Шаг 2. Создать `docs/technologies.md`
- Перенести строки 91–113. Заголовок `##` → `#`, подразделы `###` → `##`.

### Шаг 3. Создать `docs/installation.md`
- Перенести строки 117–203. Заголовок `##` → `#`, подразделы `###` → `##`.
- Ссылки на файлы — с префиксом `../` (см. раздел 5.2).
- Добавить примечание: все команды выполняются из корня репозитория.

### Шаг 4. Создать `docs/configuration.md`
- Перенести строки 207–291. Заголовок `##` → `#`, подразделы `###` → `##`.
- Ссылки на файлы — с префиксом `../` (см. раздел 5.2).
- Строку 284 «в разделе "Деплой на продакшн" ниже» заменить ссылкой
  `[deployment.md](deployment.md)`.

### Шаг 5. Создать `docs/deployment.md`
- Перенести строки 295–422. Заголовок `##` → `#`, подразделы `###` → `##`.
- Ссылки на файлы — с префиксом `../` (см. раздел 5.2).

### Шаг 6. Создать `docs/project-structure.md`
- Перенести строки 426–463. Заголовок `##` → `#`, подразделы `###` → `##`.

### Шаг 7. Переписать `README.md`
Оставить только «витрину»:

```markdown
# Laravel Orchid Blog

Блог на базе **Laravel** с административной панелью **[Orchid](https://orchid.software/)**.

![Скрин главной](screenshots/home.png)

## О проекте
<2–3 предложения: что умеет, стек, ключевые фичи>

## Быстрый старт
make install  →  сайт :8080, админ :8080/nexus (admin@localhost.ru / 123456)

## Документация
| Раздел | Файл |
|---|---|
| Возможности «из коробки» | [docs/features.md](docs/features.md) |
| Используемые технологии | [docs/technologies.md](docs/technologies.md) |
| Установка и запуск | [docs/installation.md](docs/installation.md) |
| Что поменять после создания | [docs/configuration.md](docs/configuration.md) |
| Деплой на продакшн | [docs/deployment.md](docs/deployment.md) |
| Структура проекта | [docs/project-structure.md](docs/project-structure.md) |
| Архитектура | [docs/architecture.md](docs/architecture.md) |
```

### Шаг 8. Обновить ссылки в других файлах
См. раздел 6.

### Шаг 9. Проверка
См. чек-лист в разделе 7.

## 5. Правила переноса

### 5.1 Заголовки
- В новых файлах `##` → `#`, `###` → `##` (каждый файл — самостоятельный документ).
- Emoji-префиксы разделов (✨, 📋, 🚀, 🔧, 📂) сохранить в заголовках новых файлов.

### 5.2 Относительные ссылки (префикс `../`)
Файлы переезжают из корня в `docs/`, поэтому все markdown-ссылки на файлы
репозитория получают префикс `../`. Полный список:

**`docs/installation.md`:**
- `docker/docker-compose.yml` (стр. 170) → `../docker/docker-compose.yml`
- `docker/docker-compose.prod.yml` (стр. 182) → `../docker/docker-compose.prod.yml`
- команды `docker compose -f docker/docker-compose.yml ...` (стр. 193–202) — **не трогать** (выполняются из корня репо)

**`docs/configuration.md`:**
- `config/app.php`, `layouts/techlog.blade.php` (стр. 216) → `../config/app.php`, `../resources/views/layouts/techlog.blade.php`
- `config/app.php`, `config/filesystems.php` (стр. 217) → `../config/app.php`, `../config/filesystems.php`
- `database/seeders/DatabaseSeeder.php` (стр. 219, 275) → `../database/seeders/DatabaseSeeder.php`
- `resources/views/layouts/techlog.blade.php:44` (стр. 230) → `../resources/views/layouts/techlog.blade.php:44`
- `public/favicon.ico` (стр. 238) → `../public/favicon.ico`
- `public/apple-touch-icon.png` (стр. 239) → `../public/apple-touch-icon.png`
- `public/site.webmanifest` (стр. 240) → `../public/site.webmanifest`
- `public/vendor/orchid/` (стр. 243) → `../public/vendor/orchid/`
- `config/platform.php` (стр. 247) → `../config/platform.php`
- `app/Orchid/PlatformProvider.php` (стр. 259) → `../app/Orchid/PlatformProvider.php`
- `config/my_config.php` (стр. 262) → `../config/my_config.php`
- `layouts/techlog.blade.php` (стр. 268) → `../resources/views/layouts/techlog.blade.php`
- `contact.blade.php` (стр. 270) → `../resources/views/contact.blade.php`
- `docker/docker-compose.yml` (стр. 286) → `../docker/docker-compose.yml`
- `vite.config.js` (стр. 291) → `../vite.config.js`

**`docs/deployment.md`:**
- `docker/docker-compose.prod.yml` (стр. 297) → `../docker/docker-compose.prod.yml`
- `docker/app/Dockerfile.prod` (стр. 298) → `../docker/app/Dockerfile.prod`
- `plans/deployment-plan-vps-docker.md` (стр. 299, 416, 422) → `../plans/deployment-plan-vps-docker.md`
- `provision-server.sh` (стр. 303) → `../provision-server.sh`
- `docker/docker-compose.prod.yml:36` (стр. 344) → `../docker/docker-compose.prod.yml:36`
- `docker/env.prod.example` (стр. 363) → `../docker/env.prod.example`
- `docker/nginx/conf.d/nginx.conf:1` (стр. 365) → `../docker/nginx/conf.d/nginx.conf:1`
- `docker/app/Dockerfile.prod:85` (стр. 400) → `../docker/app/Dockerfile.prod:85`
- `deploy.sh:1` (стр. 409) → `../deploy.sh:1`

**`docs/features.md` и `docs/project-structure.md`:** markdown-ссылок на файлы нет
(пути в тексте — `resources/js/techlog.js`, `config/operator.php` и т. п. — это
inline-код, не ссылки; можно оставить как есть).

### 5.3 Внутренние якорные ссылки README
- стр. 83 «см. раздел "Что нужно поменять"» → `[configuration.md](configuration.md)`
- стр. 284 «в разделе "Деплой на продакшн" ниже» → `[deployment.md](deployment.md)`

### 5.4 Команды `make` / `docker`
Все команды в перенесённых разделах выполняются из корня репозитория —
в `docs/installation.md` и `docs/deployment.md` добавить примечание об этом,
сами команды не менять.

## 6. Обновление ссылок в других файлах

После разбивки обновить упоминания README в:

| Файл | Строка | Было | Стало |
|------|--------|------|-------|
| [`AGENTS.md`](../AGENTS.md) | 7 | «список работающих "из коробки" возможностей — в README.md (раздел "✨ Возможности "из коробки"")» | `docs/features.md` |
| [`AGENTS.md`](../AGENTS.md) | 39 | «алгоритм деплоя — в README.md, раздел "Деплой на продакшн (Ubuntu 24.04 VPS)"» | `docs/deployment.md` |
| [`CONTRIBUTING.md`](../CONTRIBUTING.md) | 102 | «README.md — установка и общая информация» | README — обзор и быстрый старт; `docs/installation.md` — установка |
| [`plans/related-articles.md`](../plans/related-articles.md) | 133 | «README.md: добавить строку … в раздел "✨ Возможности из коробки"» | `docs/features.md` |
| [`plans/seo-optimization-next.md`](../plans/seo-optimization-next.md) | 5–6, 134 | «раздел "SEO" в README.md» | `docs/features.md` |
| [`plans/import-articles.md`](../plans/import-articles.md) | 135 | «Краткое описание в README.md (раздел про команды/возможности)» | `docs/features.md` |
| [`plans/article-search.md`](../plans/article-search.md) | 188 | «README.md:18 — пункт "Поиск"» | `docs/features.md` |
| [`plans/copy-code-button.md`](../plans/copy-code-button.md) | 21, 150 | «README.md (раздел "✨ Возможности из коробки")» | `docs/features.md` |
| [`docs/plan-consents-pdn.md`](../docs/plan-consents-pdn.md) | 274, 331 | «Обновить README.md: раздел "✨ Возможности "из коробки""» | `docs/features.md` |
| [`docs/provision-server.md`](../docs/provision-server.md) | 21 | «README.md:285 — новый раздел "🚀 Деплой на продакшн"» | `docs/deployment.md` |
| [`docs/public-template-plan.md`](../docs/public-template-plan.md) | 71 | «добавлен раздел "🔧 Что поменять после создания" в README.md» | `docs/configuration.md` |
| [`plans/privacy-consents-audit-fixes.md`](../plans/privacy-consents-audit-fixes.md) | 187 | «Обновить README.md при изменении поведения» | `docs/features.md` (общее правило) |

> `prompts/promt-for-public-template.md:58` (`README.md export-ignore`) — про
> `git archive`, не затрагивается.

## 7. Чек-лист проверки

- [ ] Все 6 новых файлов созданы в `docs/`, заголовки сдвинуты на уровень (`#`/`##`).
- [ ] Все markdown-ссылки в новых файлах ведут на существующие пути (префикс `../`).
- [ ] Внутренние ссылки «Что поменять» / «Деплой» заменены на `configuration.md` / `deployment.md`.
- [ ] `README.md` содержит: заголовок, описание, скриншот, «О проекте», «Быстрый старт», таблицу-оглавление на все `docs/*.md`.
- [ ] `screenshots/home.png` остался в README (не перенесён).
- [ ] Обновлены ссылки в `AGENTS.md`, `CONTRIBUTING.md`, `plans/*.md`, `docs/*.md` (раздел 6).
- [ ] `AGENTS.md` (раздел «Статус репозитория») по-прежнему корректен: README остаётся публичным документом шаблона.
- [ ] Проверен рендеринг на GitHub: относительные ссылки `docs/*.md` и `../*` открываются.
- [ ] `make lint` / `make test` не требуются (изменения только в документации), но при желании — прогнать для контроля.
