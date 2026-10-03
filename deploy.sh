#!/usr/bin/env bash
# =============================================================================
# deploy.sh — деплой продакшн-стека (docker/docker-compose.prod.yml)
# =============================================================================
# Запускать на сервере из корня репозитория:
#   ./deploy.sh
#
# Флаги:
#   --skip-backup   не делать бэкап БД и storage
#   --skip-migrate  не запускать миграции
#   --no-pull       не выполнять git pull --ff-only
#
# Требования:
#   - Docker Engine + Compose plugin;
#   - docker/.env.prod заполнен (шаблон: docker/env.prod.example, права 600);
#   - стек запускается с --env-file docker/.env.prod (интерполяция ${DB_*}).
#
# Последовательность (см. plans/deployment-plan-vps-docker.md, раздел 13):
#   1. бэкап БД (mysqldump) и storage (tar) с ротацией 14 дней;
#   2. git pull --ff-only;
#   3. docker compose up -d --build;
#   4. php artisan migrate --force (не migrate:fresh!);
#   5. config:cache + view:cache (route:cache — опционально, см. п. 2.6 плана);
#   6. перезапуск долгоживущих контейнеров;
#   7. healthcheck http://localhost/up через контейнер nginx.
# =============================================================================

set -euo pipefail

# ---------- Параметры по умолчанию ------------------------------------------

DO_BACKUP=1
DO_MIGRATE=1
DO_PULL=1

for arg in "$@"; do
  case "$arg" in
    --skip-backup)  DO_BACKUP=0 ;;
    --skip-migrate) DO_MIGRATE=0 ;;
    --no-pull)      DO_PULL=0 ;;
    -h|--help)
      sed -n '2,22p' "$0" | sed 's/^# \{0,1\}//'
      exit 0
      ;;
    *)
      echo "Неизвестный аргумент: $arg (см. --help)" >&2
      exit 1
      ;;
  esac
done

# ---------- Пути и константы ------------------------------------------------

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

COMPOSE_FILE="docker/docker-compose.prod.yml"
ENV_FILE="docker/.env.prod"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/blog}"
STAMP="$(date +%F_%H-%M-%S)"
RETENTION_DAYS=14

COMPOSE=(docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE")

log()  { echo "→ $*"; }
warn() { echo "⚠ $*" >&2; }
die()  { echo "✗ $*" >&2; exit 1; }

# ---------- Проверки ---------------------------------------------------------

[ -f "$ENV_FILE" ] || die "$ENV_FILE не найден. Скопируйте docker/env.prod.example → docker/.env.prod и заполните."

if [ "$DO_PULL" -eq 1 ] && [ ! -d .git ]; then
  warn "каталог .git не найден — пропускаю git pull (обновляйте код вручную)."
  DO_PULL=0
fi

# ---------- 1. Бэкапы --------------------------------------------------------

if [ "$DO_BACKUP" -eq 1 ]; then
  mkdir -p "$BACKUP_DIR"

  # Дамп БД из контейнера db. MYSQL_USER/MYSQL_PASSWORD/MYSQL_DATABASE
  # заданы в docker-compose.prod.yml (environment) и доступны внутри контейнера.
  if [ -n "$("${COMPOSE[@]}" ps --status running -q db 2>/dev/null || true)" ]; then
    log "Бэкап БД → $BACKUP_DIR/db_$STAMP.sql.gz"
    "${COMPOSE[@]}" exec -T db sh -c \
      'exec mysqldump --single-transaction --quick -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
      | gzip > "$BACKUP_DIR/db_$STAMP.sql.gz"
  else
    warn "контейнер db не запущен — бэкап БД пропущен."
  fi

  # Архив storage/ (загруженные изображения, сессии, логи) из контейнера app.
  if [ -n "$("${COMPOSE[@]}" ps --status running -q app 2>/dev/null || true)" ]; then
    log "Бэкап storage → $BACKUP_DIR/storage_$STAMP.tar.gz"
    "${COMPOSE[@]}" exec -T app tar cf - -C /var/www storage \
      | gzip > "$BACKUP_DIR/storage_$STAMP.tar.gz"
  else
    warn "контейнер app не запущен — бэкап storage пропущен."
  fi

  # Ротация старых бэкапов.
  find "$BACKUP_DIR" -name 'db_*.sql.gz'        -mtime +"$RETENTION_DAYS" -delete 2>/dev/null || true
  find "$BACKUP_DIR" -name 'storage_*.tar.gz'   -mtime +"$RETENTION_DAYS" -delete 2>/dev/null || true
fi

# ---------- 2. Обновление кода ----------------------------------------------

if [ "$DO_PULL" -eq 1 ]; then
  log "git pull --ff-only"
  git pull --ff-only
fi

# ---------- 3. Сборка и запуск -----------------------------------------------

log "docker compose up -d --build"
"${COMPOSE[@]}" up -d --build

# ---------- 4. Миграции ------------------------------------------------------

if [ "$DO_MIGRATE" -eq 1 ]; then
  log "php artisan migrate --force"
  "${COMPOSE[@]}" exec -T app php artisan migrate --force
fi

# ---------- 5. Оптимизация ---------------------------------------------------
# route:cache можно добавить после выноса замыканий в контроллеры (см. план,
# п. 2.6); по умолчанию — безопасная пара config:cache + view:cache.

log "php artisan config:cache && php artisan view:cache"
"${COMPOSE[@]}" exec -T app php artisan config:cache
"${COMPOSE[@]}" exec -T app php artisan view:cache

# ---------- 6. Перезапуск долгоживущих процессов -----------------------------

log "restart app schedule queue nginx"
"${COMPOSE[@]}" restart app schedule queue nginx

# ---------- 7. Healthcheck ---------------------------------------------------

log "Healthcheck http://localhost/up"
if "${COMPOSE[@]}" exec -T nginx wget -q -O /dev/null http://localhost/up; then
  echo "✓ Деплой завершён: сайт отвечает на /up."
else
  warn "сайт не ответил на /up — проверьте логи: ${COMPOSE[*]} logs"
  exit 1
fi
