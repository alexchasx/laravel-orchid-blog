# 🚀 Деплой на продакшн (Ubuntu 24.04 VPS)

> Прод-стек — [`../docker/docker-compose.prod.yml`](../docker/docker-compose.prod.yml) (nginx + PHP-FPM 8.5 + MySQL 8.0 + воркеры `schedule`/`queue`);
> фронтенд собирается внутрь образа (multi-stage, [`../docker/app/Dockerfile.prod`](../docker/app/Dockerfile.prod)), поэтому на сервере нужны только Docker и код репозитория.
> Детальный план с обоснованием каждого шага — [`../plans/deployment-plan-vps-docker.md`](../plans/deployment-plan-vps-docker.md).

## Шаг 1. Подготовка сервера

Скрипт [`../provision-server.sh`](../provision-server.sh) ставит на чистой Ubuntu 24.04 LTS всё необходимое перед первым деплоем:
базовые пакеты, часовой пояс `Europe/Moscow`, swap, пользователя для деплоя с SSH-ключом, ужесточённый sshd,
Docker Engine + Compose plugin, firewall ufw, опционально fail2ban и автообновления безопасности, каталог бэкапов.

Минимальные требования к VPS: **2 vCPU / 2–4 ГБ RAM / 30 ГБ SSD**.

```bash
# скопировать скрипт на сервер (scp/rsync/cat) и запустить от root:
sudo bash provision-server.sh \
  --user deploy \
  --ssh-key "ssh-ed25519 AAAA... your@host" \
  --with-fail2ban --with-unattended
```

Что делает скрипт (идемпотентно — повторный запуск безопасен, выполненные шаги пропускаются):

| № | Действие |
|---|---|
| 1 | `apt-get update && upgrade`, базовые пакеты (git, curl, rsync, jq, openssh-server…) |
| 2 | `timedatectl set-timezone Europe/Moscow` (совпадает с `config/app.php`) |
| 3 | swap-файл `/swapfile` (по умолчанию 2 ГБ, `vm.swappiness=10`) |
| 4 | пользователь `deploy` (группы `sudo` и `docker`), пароль заблокирован, вход только по ключу |
| 5 | sshd: `PermitRootLogin no`, `PasswordAuthentication no`, `AllowUsers deploy` (шаг пропускается, если не передан `--ssh-key` — иначе есть риск заблокировать самому себе вход) |
| 6 | Docker Engine + Compose plugin из официального репозитория Docker |
| 7 | ufw: разрешены только `SSH/22`, `80`, `443` (порт SSH определяется автоматически) |
| 8 | fail2ban — при `--with-fail2ban` |
| 9 | unattended-upgrades — при `--with-unattended` |
| 10 | каталог бэкапов `/var/backups/blog` (владелец — пользователь деплоя) |

Флаги: `--user NAME`, `--ssh-key "KEY"`, `--swap SIZE_GB`, `--with-fail2ban`, `--with-unattended`, `--skip-firewall`, `--help`.

## Шаг 2. Домен и DNS

- `A`-запись домена (и `www`, если используется) на IP сервера — дождаться пропагации.
- Для рассылки и формы обратной связи — почтовые записи `MX`, `SPF` (`TXT`), `DKIM`, `DMARC`, иначе письма попадают в спам.

## Шаг 3. Размещение проекта на сервере и TLS

Скрипту [`../setup-tls.sh`](../setup-tls.sh) нужен проект на сервере (он вносит изменения в `docker/.env.prod`), поэтому сначала размещаем код в `/opt/blog` — любым из двух способов.

**Способ 1 — клонирование из git** (сервер получает SSH-доступ к репозиторию):

```bash
# войти на сервер под пользователем деплоя
sudo mkdir -p /opt/blog
sudo chown "$USER":"$USER" /opt/blog
git clone git@github.com:alexchasx/laravel-orchid-blog.git /opt/blog
cd /opt/blog
```

**Способ 2 — копирование с локальной машины** (серверу не нужен доступ к вашему git). Прод-стек не требует git на сервере: [`../deploy.sh`](../deploy.sh) при отсутствии `.git` сам пропускает `git pull` (см. Шаг 5). Код доставляется скриптом [`../deploy-rsync.sh`](../deploy-rsync.sh), который исключает всё лишнее (зеркало `.dockerignore` + секреты и runtime-данные):

```bash
# подготовить каталог на сервере (один раз):
ssh deploy@SERVER
sudo mkdir -p /opt/blog
sudo chown "$USER":"$USER" /opt/blog
exit

# на ЛОКАЛЬНОЙ машине (в корне проекта):
chmod +x deploy-rsync.sh   # один раз
./deploy-rsync.sh deploy@SERVER /opt/blog

# затем войти на сервер:
ssh deploy@SERVER
cd /opt/blog
```

> Что исключается и почему: `.git` (иначе `deploy.sh` попытается сделать `git pull`), `vendor/`, `node_modules/`, `public/build` (собираются внутри образа — multi-stage [`../docker/app/Dockerfile.prod`](../docker/app/Dockerfile.prod)), `storage/` (runtime-данные живут в volume `app_storage`), `bootstrap/cache/` (скомпилированные манифесты пакетов с dev-only провайдерами `laravel-lang` — ломают загрузку приложения; манифест генерирует `Dockerfile.prod` через `package:discover`), `docker/.env.prod` (создаётся на сервере на Шаге 4 и переживает обновления — `--delete` исключённые файлы не трогает), `.env*`, dev-артефакты (`tests`, `docs`, `plans`, `prompts`, `screenshots`). Шаблон `docker/env.prod.example` копируется — из него на сервере создаётся `docker/.env.prod`.

**Вариант A — хостовый reverse proxy (рекомендуется).** nginx на хосте терминирует TLS и проксирует на контейнер.

Всё делает скрипт [`../setup-tls.sh`](../setup-tls.sh):

```bash
sudo bash setup-tls.sh --domain example.com --email admin@example.com --www
```

Скрипт (идемпотентен, повторный запуск безопасен):

1. ставит `nginx`, `certbot`, `python3-certbot-nginx`;
2. вносит изменения в проект: добавляет `NGINX_PORTS=127.0.0.1:8080:80` в `docker/.env.prod` — порт 80 контейнера привязывается к loopback, освобождая порт 80 для хостового nginx (файл в `.gitignore`, поэтому `git pull --ff-only` в [`../deploy.sh`](../deploy.sh) не конфликтует);
3. создаёт `/etc/nginx/sites-available/blog` (`server_name` + `proxy_pass http://127.0.0.1:8080`) и включает сайт;
4. выпускает сертификат Let's Encrypt (`certbot --nginx`, автообновление через systemd-timer).

Вручную (если скрипт не подходит):

1. Установить: `apt-get install nginx certbot python3-certbot-nginx`.
2. Освободить порт 80 для хостового nginx — задать `NGINX_PORTS=127.0.0.1:8080:80` в `docker/.env.prod` (по умолчанию в [`../docker/docker-compose.prod.yml`](../docker/docker-compose.prod.yml:36) — `80:80`).
3. Конфиг `/etc/nginx/sites-available/blog` (затем `ln -s` в `sites-enabled` и `nginx -t && systemctl reload nginx`):

```nginx
server {
    listen 80;
    server_name example.com www.example.com;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host              $host;
        proxy_set_header X-Real-IP         $remote_addr;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

4. Выпустить сертификат: `certbot --nginx -d example.com -d www.example.com` (автообновление через systemd-timer).
5. В [`../docker/env.prod.example`](../docker/env.prod.example) оставить `TRUSTED_PROXIES=*` — Laravel через `TrustProxies` корректно определит схему `https` для canonical, OG/Twitter, sitemap и редиректов.

**Вариант B — TLS в контейнере nginx:** смонтировать сертификаты certbot в контейнер и добавить `listen 443 ssl` в [`../docker/nginx/conf.d/nginx.conf`](../docker/nginx/conf.d/nginx.conf:1).

**Вариант C — Cloudflare** (Flexible/Full): TLS на границе CDN; для режима Full укажите в `TRUSTED_PROXIES` диапазоны IP Cloudflare.

## Шаг 4. Первый деплой

```bash
cd /opt/blog

make prod-env            # docker/env.prod.example → docker/.env.prod (права 600; если файл уже создан setup-tls.sh — пропустится)
nano docker/.env.prod    # заполнить: APP_URL, DB_*, MAIL_*, OPERATOR_*, SEO_*, CONTACT_EMAIL...

# APP_KEY генерируется НА ХОСТЕ и вписывается в docker/.env.prod:
# (php artisan key:generate в контейнере писал бы в .env образа — он не персистентен)
KEY=$(openssl rand -base64 32)
sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" docker/.env.prod

make prod-up             # собрать multi-stage образы и поднять стек (nginx, app, schedule, queue, db)
make prod-migrate        # php artisan migrate --force  (НЕ migrate:fresh!)
```

После первого `make prod-up` — разовые шаги:

```bash
make prod-shell   # войти в контейнер app и создать администратора Orchid:
#   php artisan orchid:admin admin admin@example.com СильныйПароль123!
#   exit
make prod-optimize    # config:cache + view:cache (route:cache запрещён — замыкания в routes/web.php)
curl -I http://localhost/up   # 200 OK (или https://домен/up)
```

> `storage:link` уже выполняется на этапе сборки образа ([`../docker/app/Dockerfile.prod`](../docker/app/Dockerfile.prod:85)); `storage/` монтируется в named volume `app_storage` — загруженные изображения переживают пересборку.

## Шаг 5. Обновления (последующие деплои)

**Если проект клонирован из git (Способ 1):**

```bash
cd /opt/blog
./deploy.sh          # или make prod-deploy
```

**Если проект скопирован с локальной машины (Способ 2):**

```bash
# на ЛОКАЛЬНОЙ машине — обновить код на сервере:
./deploy-rsync.sh deploy@SERVER /opt/blog

# на сервере:
cd /opt/blog
./deploy.sh --no-pull
```

[`../deploy.sh`](../deploy.sh:1) выполняет: бэкап БД (`mysqldump`) и `storage` (tar) с ротацией 14 дней → обновление кода (`git pull --ff-only`; при отсутствии `.git` шаг пропускается с предупреждением) →
`docker compose up -d --build` → `migrate --force` → `config:cache` + `view:cache` → рестарт контейнеров → healthcheck `/up`.
Флаги: `--skip-backup`, `--skip-migrate`, `--no-pull`.

### Смена настроек (`docker/.env.prod`)

Файл с секретами исключён из [`../deploy-rsync.sh`](../deploy-rsync.sh) и git — его копируют отдельно:

```bash
# на ЛОКАЛЬНОЙ машине:
scp docker/.env.prod deploy@SERVER:/opt/blog/docker/.env.prod

# на сервере:
cd /opt/blog
chmod 600 docker/.env.prod
docker compose -f docker/docker-compose.prod.yml --env-file docker/.env.prod up -d   # пересоздать контейнеры с новыми env
make prod-optimize    # config:cache + view:cache — иначе Laravel отдаёт старый кэш
```

> `docker compose up -d` (без `--build`) пересоздаёт только контейнеры, чей конфиг изменился; `restart` новые значения env не подхватывает. Если контейнеры не пересоздались — добавьте `--force-recreate`. Смена `DB_*` после первого запуска не меняет реальные креды MySQL (они задаются только при инициализации пустого volume) — либо не меняйте их, либо правите через `ALTER USER` в контейнере `db`.

## Шаг 6. Очередь, планировщик и бэкапы

- Рассылка подписчикам (`queue`) и автопубликация статей (`schedule`) уже входят в прод-compose с `restart: unless-stopped` и `TZ=Europe/Moscow` — отдельной настройки не требуется.
- Ежедневный дамп БД через cron на хосте (ротация 14 дней) — готовый пример в [`../plans/deployment-plan-vps-docker.md`](../plans/deployment-plan-vps-docker.md), раздел 9.
- `storage/app/public` (загруженные изображения) и `docker/.env.prod` — в отдельное хранилище (rsync/borg), не на тот же диск.
- Мониторинг: health-эндпоинт `https://домен/up` (UptimeRobot и т. п.), логи — `docker compose -f docker/docker-compose.prod.yml --env-file docker/.env.prod logs -f` (`make prod-logs`).

## Шаг 7. Финальный smoke-тест

Чек-лист из 15 пунктов — [`../plans/deployment-plan-vps-docker.md`](../plans/deployment-plan-vps-docker.md), раздел 12: главная и лента, статья с оглавлением/изображением, рубрика/тег по slug и legacy-редирект 301, поиск и пагинация, `/sitemap.xml`/`/rss`/`/robots.txt`, гостевой комментарий (капча + оба согласия + `consent_logs`), форма обратной связи, подписка/отписка, автопубликация запланированной статьи, `/nexus`, HTTPS-редирект и абсолютные URL со схемой `https`.
