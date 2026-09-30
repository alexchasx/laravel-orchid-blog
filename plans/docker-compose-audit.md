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

Статус проверки: **2026-09-30** (повторный аудит внедрённых изменений).

- [x] Добавить `image: blog_app` в сервис `app` — выполнено ([`docker-compose.yml`](docker/docker-compose.yml:16));
      `schedule`/`queue` корректно ссылаются на тот же образ (стр. 48, 62)
- [x] Добавить `restart: unless-stopped` для `schedule`, `queue`, `nginx` — выполнено
      (nginx стр. 4, schedule стр. 50, queue стр. 64; заодно у mailhog/phpmyadmin/app,
      db — `always`)
- [x] Запускать `schedule` и `queue` от `user: "www-data"` — выполнено (стр. 49, 63)
- [x] Сделать `entrypoint.sh` устойчивым: `mkdir -p` + `|| true` — выполнено
      ([`docker/app/entrypoint.sh`](docker/app/entrypoint.sh:6))
- [x] Healthcheck для `db` (`mysqladmin ping`) + `condition: service_healthy`
      в `depends_on` — выполнено (db стр. 91-96; depends_on у app/schedule/queue/phpmyadmin,
      nginx→app)
- [x] Перевести MySQL на utf8mb4 — выполнено (стр. 90:
      `--character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci`)
- [x] Попинить версии образов — выполнено: `nginx:1.27-alpine` (стр. 3),
      `mailhog/mailhog:v1.0.1` (стр. 74), `phpmyadmin:5.2` (стр. 100)
- [x] Добавить `docker/tmp/` в `.gitignore` — **не подтверждено**: доступ к `.gitignore`
      закрыт правилами `.codeassistantignore`, поиск по `tmp/db` результатов не дал.
      Проверить вручную (файл `.gitignore` отсутствует в листинге корня репозитория)
- [x] Верификация — **выполнена полностью (2026-09-30)**:
  - `docker compose config` — конфигурация валидна;
  - `make up` — собраны и запущены все 8 контейнеров, `blog_app`/`blog_db` в статусе
    **healthy** (healthcheck на `ps` работает после добавления `procps`);
  - `make setup` шаги (composer install, key:generate, migrate:fresh --seed,
    orchid:admin, storage:link, npm install, vite build) — все прошли;
  - `make test` — **216 passed (547 assertions)** после исправления багов шаблона
    (см. ниже);
  - сайт отвечает: `/`→200, `/rss`→200, `/sitemap.xml`→200, `/robots.txt`→200,
    `/admin`→302 (редирект на логин); планировщик реально выполняет
    `articles:publish-scheduled` каждую минуту.

---

## ✅ Замечание про healthcheck `app` — ИСПРАВЛЕНО

**Healthcheck сервиса `app` зависел от утилиты `ps`, которой могло не быть в образе.**
Исправление: в [`docker/app/Dockerfile`](docker/app/Dockerfile:5) в `apt-get install`
добавлен пакет **`procps`**. После пересборки `blog_app` стабильно в статусе
`healthy` — проверено фактически.

Заодно в [`docker/app/Dockerfile`](docker/app/Dockerfile:20) добавлена настройка
`gai.conf` (предпочтение IPv4 при резолве имён) — в этой сети DNS отдавал первым
IPv6 без маршрута, что роняло `composer install` в таймаут.

В healthcheck БД пароль root передан открытым текстом (`-proot`) — приемлемо для
локальной разработки, но не для продакшена.

---

## 🐛 Баги шаблона, найденные и исправленные в ходе финальной верификации

1. **`format_rss()` не существовал.** [`FeedController.php`](app/Http/Controllers/FeedController.php:25)
   вызывал несуществующий макрос `format_rss()` → `/rss` падал с
   «Method format_rss does not exist», 14 тестов FeedTest были красными.
   Исправлено: заменено на стандартный `toRssString()` (RFC 2822).
   Проверено: `<pubDate>Tue, 29 Sep 2026 09:44:59 +0300</pubDate>`.

2. **Тесты RSS/sitemap с изображениями не создавали файл.** [`FeedTest.php`](tests/Feature/FeedTest.php:217)
   и [`SitemapTest.php`](tests/Feature/SitemapTest.php:104) задавали статье
   `image => 'articles/*.jpg'`, но не создавали физический файл, а контроллеры
   проверяют `Storage::exists()` → блок `<enclosure>`/`<image:url>` не выводился.
   Исправлено: в тестах добавлен `Storage::put(...)` перед созданием статьи.

3. **Инвертированная проверка сортировки в FeedTest.** [`FeedTest.php`](tests/Feature/FeedTest.php:144)
   `assertLessThan($posNewer, $posOlder)` требовал, чтобы старая статья шла раньше
   новой — вопреки сортировке контроллера и смыслу теста. Аргументы поменяны местами.

---

## ℹ️ Особенности окружения, замеченные при верификации

- **Нестабильный DNS/anycast `repo.packagist.org`**: в этой сети резолвер отдаёт
  ротацию адресов, часть из которых недоступна (таймауты `curl error 28`).
  Рабочее зеркало — `packagist.jp`. Для текущего запуска временно прописан рабочий
  IP в `/etc/hosts` контейнера (в шаблон не попадает; на других машинах
  проблемы, скорее всего, не будет).
- **nginx кэширует IP апстрима при старте**: после пересборки `app` (смена IP)
  nginx продолжал ходить на старый адрес → 502. Лечится
  `docker compose restart nginx`. На чистой установке (все контейнеры создаются
  разом) проблема не проявляется.
- Воркеры `schedule`/`queue` от `www-data` до `composer install` уходят в
  restart-loop (нет `vendor/autoload.php`) — это ожидаемо до первого `make setup`.
