# Аудит окружения `docker/docker-compose.yml`

> Отчёт по результатам проверки конфигурации Docker-окружения.
> Дата: 2026-09-29. Изменения в файлы не вносились — только аудит и план.

Проверенные файлы: `docker/docker-compose.yml`, `docker/app/Dockerfile`,
`docker/app/entrypoint.sh`, `docker/nginx/conf.d/nginx.conf`, `Makefile`,
`.env.example`, `config/database.php`, `config/queue.php`, `routes/console.php`.

---

## 🔴 Критический баг (блокирует `make up` на свежем клоне)

**Сервисы `schedule` и `queue` ссылаются на несуществующий образ `blog_app`.**

В `docker/docker-compose.yml` (строки 37 и 48) указано `image: blog_app`, но у
сервиса `app` (строка 13) **нет ключа `image:`**. Docker Compose в этом случае
сам именует собранный образ как `{имя_проекта}-{имя_сервиса}`. Поскольку
compose-файл лежит в каталоге `docker/`, проект называется `docker`, и образ
собирается как `docker-app:latest`. `container_name: blog_app` задаёт только имя
контейнера и на тег образа не влияет.

Итог: `docker compose up -d --build` пытается вытянуть `blog_app:latest` из
Docker Hub → ошибка `pull access denied / manifest not found`, и вся сборка падает.

**Исправление**: добавить `image: blog_app` в определение сервиса `app` — тогда
`schedule`/`queue` корректно переиспользуют локально собранный образ.

---

## 🟠 Проблемы надёжности

1. **Нет `restart` у воркеров.** У `schedule` (стр. 36) и `queue` (стр. 47)
   отсутствует политика перезапуска (у `app` и `db` она есть). При падении
   воркеры останавливаются навсегда: автопубликация статей и рассылка писем
   молча встанут. Рекомендация: `restart: unless-stopped`. Аналогично у `nginx`.

2. **Воркеры работают от root.** Образ `php:8.5.2-fpm-bookworm` запускается от
   root. `schedule`/`queue` наследуют это: `php artisan schedule:work` и
   `queue:work` будут создавать файлы (логи, кэш) в `/var/www/storage` от root,
   что конфликтует с PHP-FPM (`www-data`), обслуживающим веб-запросы. Возможны
   ошибки прав при ротации `storage/logs/laravel.log`. Рекомендация:
   `user: "www-data"` для этих сервисов — но тогда нужно сделать entrypoint
   устойчивым (см. п. 3), т.к. `chown` без `CAP_CHOWN` упадёт.

3. **Entrypoint хрупкий.** `docker/app/entrypoint.sh` (стр. 6) выполняет
   `chown -R ... /var/www/storage /var/www/bootstrap/cache` без `mkdir -p` и с
   `set -e`. Сейчас каталоги существуют, но на свежем клоне или при
   нестандартной структуре репозитория отсутствие каталога уронит старт
   контейнера. Рекомендация: `mkdir -p` + допускать ошибку chown (`|| true`).

4. **Нет ожидания готовности БД.** `depends_on` без `condition: service_healthy`.
   Контейнеры могут стартовать до готовности MySQL; особенно критично для
   `queue:work`, который при неудачном подключении сразу завершится (без restart
   из п. 1 это фатально). Рекомендация: healthcheck на `db`
   (`mysqladmin ping`) + `condition: service_healthy` у `app`, `schedule`,
   `queue`, `phpmyadmin`.

5. **Непинованные версии образов.** `nginx` (стр. 3), `mailhog/mailhog`
   (стр. 57), `phpmyadmin` (стр. 76) — плавающий `latest`: поведение меняется
   при пересборке. Для публичного шаблона желательно пинить (например,
   `nginx:1.27-alpine`, `mailhog/mailhog:v1.0.1`, `phpmyadmin:5.2`).

6. **MySQL на utf8 вместо utf8mb4.** Стр. 72: `--character-set-server=utf8
   --collation-server=utf8_unicode_ci` (utf8 = utf8mb3, без эмодзи и 4-байтных
   символов), а Laravel настроен на `utf8mb4`/`utf8mb4_unicode_ci`
   (`config/database.php`). Таблицы миграциями создадутся в utf8mb4 (данные в
   безопасности), но дефолты БД лучше привести к `utf8mb4_unicode_ci`.

7. **Volume БД внутри репозитория.** Данные MySQL лежат в `./tmp/db`
   (стр. 66) — внутри `docker/`. Нужно убедиться, что `docker/tmp/` в
   `.gitignore`, иначе пароль root и данные БД могут попасть в коммит.

---

## 🟡 Мелкие замечания

- **Пустые `DB_*` в `.env.example`**: compose создаёт `laraorchid`/`root`/`root`,
  но в шаблоне значения БД пустые. `make install` → `make setup` не отработают
  «из коробки» — разработчик обязан заполнить `.env` (`DB_DATABASE=laraorchid`,
  `DB_USERNAME=root`, `DB_PASSWORD=root`). Это сознательная политика шаблона
  (AGENTS.md), но о рассинхроне стоит помнить.
- **`MAIL_HOST=mailhog` / `MAIL_PORT=1025`** — корректно: mailhog слушает SMTP
  на 1025 внутри сети.
- **`node`**: `user: "${UID:-1000}:${GID:-1000}"` — рабочий подход; GID обычно
  не экспортирован в shell, поэтому упадёт в 1000 даже при другой основной
  группе пользователя (мелочь).
- **`phpmyadmin`**: `PMA_ARBITRARY=1` работает, но удобнее явно задать
  `PMA_HOST=db`, `PMA_USER=root`, `PMA_PASSWORD=root`.
- **nginx** (стр. 2): без `restart`, но не критично; `fastcgi_pass app:9000`
  соответствует внутреннему порту PHP-FPM.
- **`redis`/`memcached`** в compose отсутствуют, а в `.env.example` упомянуты —
  не используются, т.к. `CACHE_DRIVER=file`, `QUEUE_CONNECTION=database`,
  `SESSION_DRIVER=file`. Противоречия нет, но переменные-«призраки» можно удалить.

---

## ✅ Что настроено корректно

- Bind-mount всего проекта в `/var/www` во всех сервисах — единая точка истины
  для кода.
- `app` не публикует порты наружу (PHP-FPM только внутренний) — правильно.
- Порты хоста не конфликтуют: сайт `8080`, phpMyAdmin `8899`, MailHog `8026`,
  MySQL `8101`, Vite `5173`.
- `QUEUE_CONNECTION=database` + воркер `queue:work` согласованы с
  `config/queue.php` и миграцией `jobs`.
- Расписание через `php artisan schedule:work` корректно подхватывает задания из
  `routes/console.php`.
- Entrypoint-подход с фиксом прав `storage`/`bootstrap/cache` — правильная идея
  (bind-mount сбрасывает владельца с build-стадии).
- `APP_URL=http://localhost:8080` совпадает с проброшенным портом nginx.

---

## Резюме

| Уровень | Кол-во | Суть |
|---|---|---|
| 🔴 Критично | 1 | `image: blog_app` у `schedule`/`queue` без `image:` у `app` — сборка падает |
| 🟠 Надёжность | 5 | restart у воркеров, root-выполнение, хрупкий entrypoint, healthcheck БД, pin образов |
| 🟡 Мелочи | 5 | utf8mb4, `docker/tmp/` в .gitignore, пустые DB_*, phantom-переменные redis/memcached, PMA_* |

---

## План исправлений (по приоритету)

- [ ] Добавить `image: blog_app` в сервис `app` (критично — без этого `make up` падает)
- [ ] Добавить `restart: unless-stopped` для `schedule`, `queue`, `nginx`
- [ ] Запускать `schedule` и `queue` от `user: "www-data"` (не root)
- [ ] Сделать `entrypoint.sh` устойчивым: `mkdir -p storage bootstrap/cache` + `|| true`
- [ ] Healthcheck для `db` (`mysqladmin ping`) + `condition: service_healthy` в `depends_on`
- [ ] Перевести MySQL на `--character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci`
- [ ] Попинить версии образов `nginx`, `mailhog`, `phpmyadmin`
- [ ] Добавить `docker/tmp/` в `.gitignore` (проверить актуальный `.gitignore`)
- [ ] Верификация: `docker compose config`, `make up`, `make setup`, `make test`
