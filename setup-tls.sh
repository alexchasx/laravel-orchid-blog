#!/usr/bin/env bash
# =============================================================================
# setup-tls.sh — настройка TLS для продакшена (Шаг 3 docs/deployment.md)
# =============================================================================
# Вариант A (рекомендуемый): хостовый nginx reverse proxy + Let's Encrypt.
# Требование: репозиторий уже склонирован на сервер (docs/deployment.md,
# Шаг 3) — скрипт вносит изменения в docker/.env.prod проекта.
# Скрипт запускается НА СЕРВЕРЕ из корня репозитория (например, /opt/blog):
#
#   sudo bash setup-tls.sh --domain example.com --email admin@example.com --www
#
# Что делает:
#   1. ставит nginx, certbot, python3-certbot-nginx;
#   2. вносит изменения в проект: добавляет NGINX_PORTS=127.0.0.1:8080:80
#      в docker/.env.prod — порт 80 контейнера привязывается к loopback,
#      освобождая порт 80 для хостового nginx. Файл docker/.env.prod в
#      .gitignore, поэтому git pull --ff-only (deploy.sh) не конфликтует;
#   3. создаёт /etc/nginx/sites-available/blog (server_name + proxy_pass
#      на 127.0.0.1:8080) и включает сайт;
#   4. выпускает сертификат Let's Encrypt (certbot --nginx, автообновление
#      через systemd-timer).
#
# Флаги:
#   --domain DOMAIN      основной домен (обязательно)
#   --www                добавить www.DOMAIN в server_name и сертификат
#   --email EMAIL        e-mail для Let's Encrypt (неинтерактивный режим)
#   --project-dir DIR    каталог проекта (по умолчанию: текущий)
#   --skip-cert          не выпускать сертификат (только nginx + проект)
#   -h|--help            показать справку
#
# Повторный запуск безопасен (идемпотентность): уже выполненные шаги
# пропускаются. Варианты B (TLS в контейнере) и C (Cloudflare) — вручную,
# см. docs/deployment.md, Шаг 3.
# =============================================================================

set -euo pipefail

# ---------- Цвета и логирование ----------------------------------------------

C_RESET='\033[0m'
C_GREEN='\033[32m'
C_YELLOW='\033[33m'
C_RED='\033[31m'
C_CYAN='\033[36m'

log()  { echo -e "${C_CYAN}→${C_RESET} $*"; }
ok()   { echo -e "${C_GREEN}✓${C_RESET} $*"; }
warn() { echo -e "${C_YELLOW}⚠${C_RESET} $*" >&2; }
die()  { echo -e "${C_RED}✗${C_RESET} $*" >&2; exit 1; }

show_help() {
  sed -n '2,33p' "$0" | sed 's/^# \{0,1\}//'
  exit 0
}

# ---------- Параметры по умолчанию -------------------------------------------

DOMAIN=""
EMAIL=""
WITH_WWW=0
SKIP_CERT=0
PROJECT_DIR="$(pwd)"
NGINX_SITE="blog"
PROXY_PASS="http://127.0.0.1:8080"
NGINX_PORTS_VALUE="127.0.0.1:8080:80"

while [ $# -gt 0 ]; do
  case "$1" in
    --domain)      DOMAIN="${2:?--domain требует значение}"; shift 2 ;;
    --email)       EMAIL="${2:?--email требует значение}"; shift 2 ;;
    --www)         WITH_WWW=1; shift ;;
    --skip-cert)   SKIP_CERT=1; shift ;;
    --project-dir) PROJECT_DIR="${2:?--project-dir требует значение}"; shift 2 ;;
    -h|--help)     show_help ;;
    *) die "Неизвестный аргумент: $1 (см. --help)" ;;
  esac
done

[ -n "$DOMAIN" ] || die "--domain обязателен (например: --domain example.com)."
case "$DOMAIN" in
  ''|*[!a-zA-Z0-9.-]*|.*|*.) die "Некорректный домен: '$DOMAIN'." ;;
esac
case "$DOMAIN" in
  *.*) : ;;
  *) die "Некорректный домен (нужен FQDN с точкой): '$DOMAIN'." ;;
esac

# ---------- Предпроверки ------------------------------------------------------

if [ "$(id -u)" -ne 0 ]; then
  die "Запустите с правами root: sudo bash $0 ..."
fi

[ -f "$PROJECT_DIR/docker/docker-compose.prod.yml" ] \
  || die "Не найден $PROJECT_DIR/docker/docker-compose.prod.yml — укажите --project-dir (корень репозитория)."

export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_MODE=a   # не задавать интерактивных вопросов при перезапуске служб

log "Настройка TLS для ${DOMAIN} (проект: ${PROJECT_DIR})"

# ---------- 1. Установка nginx + certbot -------------------------------------

step_packages() {
  if command -v nginx >/dev/null 2>&1 && command -v certbot >/dev/null 2>&1; then
    ok "nginx и certbot уже установлены"
    return
  fi

  # Если порт 80 занят чем-то, кроме nginx (например, контейнером 80:80) —
  # предупредим до установки хостового nginx.
  if ! command -v nginx >/dev/null 2>&1 \
     && ss -ltn 2>/dev/null | awk '{print $4}' | grep -qE ':(80)$'; then
    warn "Порт 80 уже занят. Если это контейнер nginx (80:80) — после изменений"
    warn "перезапустите стек: make prod-up (порт уйдёт на 127.0.0.1:8080)."
  fi

  log "Установка nginx, certbot, python3-certbot-nginx"
  apt-get update -qq
  apt-get install -y -qq nginx certbot python3-certbot-nginx
  ok "nginx и certbot установлены"
}

# ---------- 2. Изменения в проекте (docker/.env.prod) -------------------------

step_project() {
  local env_file="$PROJECT_DIR/docker/.env.prod"

  if [ ! -f "$env_file" ]; then
    log "docker/.env.prod не найден — создаю из docker/env.prod.example"
    cp "$PROJECT_DIR/docker/env.prod.example" "$env_file"
    chmod 600 "$env_file"
  fi

  if grep -q '^NGINX_PORTS=' "$env_file"; then
    sed -i "s|^NGINX_PORTS=.*|NGINX_PORTS=${NGINX_PORTS_VALUE}|" "$env_file"
  else
    echo "NGINX_PORTS=${NGINX_PORTS_VALUE}" >> "$env_file"
  fi
  ok "NGINX_PORTS=${NGINX_PORTS_VALUE} → $env_file (порт 80 контейнера привязан к loopback)"

  if grep -q '^TRUSTED_PROXIES=' "$env_file" && ! grep -q '^TRUSTED_PROXIES=\*' "$env_file"; then
    warn "TRUSTED_PROXIES в $env_file не равен '*' — Laravel может не определить"
    warn "схему https (canonical, OG, sitemap). Оставьте TRUSTED_PROXIES=* (см. docs/deployment.md)."
  fi
}

# ---------- 3. Хостовый nginx: сайт blog -------------------------------------

step_host_nginx() {
  local site_file="/etc/nginx/sites-available/${NGINX_SITE}"
  local server_names="$DOMAIN"
  [ "$WITH_WWW" -eq 1 ] && server_names="$DOMAIN www.$DOMAIN"

  if [ -f "$site_file" ]; then
    warn "$site_file уже существует — пропускаю (удалите файл, чтобы пересоздать)"
  else
    log "Создание $site_file (server_name: ${server_names})"
    cat > "$site_file" <<EOF
# Сгенерировано setup-tls.sh
server {
    listen 80;
    server_name ${server_names};

    location / {
        proxy_pass ${PROXY_PASS};
        proxy_set_header Host              \$host;
        proxy_set_header X-Real-IP         \$remote_addr;
        proxy_set_header X-Forwarded-For   \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF
  fi

  # Дефолтный сайт nginx слушает 80 и перехватывает запросы — отключаем.
  if [ -e /etc/nginx/sites-enabled/default ]; then
    rm -f /etc/nginx/sites-enabled/default
    ok "Дефолтный сайт nginx отключён (/etc/nginx/sites-enabled/default)"
  fi

  ln -sf "$site_file" "/etc/nginx/sites-enabled/${NGINX_SITE}"
  nginx -t || die "Конфигурация nginx некорректна — правки не применены"
  systemctl reload nginx
  ok "nginx перезагружен, сайт ${NGINX_SITE} включён"
}

# ---------- 4. Сертификат Let's Encrypt --------------------------------------

step_certbot() {
  if [ "$SKIP_CERT" -eq 1 ]; then
    warn "Выпуск сертификата пропущен (--skip-cert)"
    return
  fi

  local domains=(-d "$DOMAIN")
  [ "$WITH_WWW" -eq 1 ] && domains+=(-d "www.$DOMAIN")

  # Предупреждение о DNS, если домен резолвится не на этот сервер.
  local server_ip domain_ip
  server_ip="$(curl -4 -fsS --max-time 5 https://ifconfig.me 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}')"
  domain_ip="$(getent ahostsv4 "$DOMAIN" 2>/dev/null | awk '{print $1; exit}')"
  if [ -n "$server_ip" ] && [ -n "$domain_ip" ] && [ "$server_ip" != "$domain_ip" ]; then
    warn "DNS: ${DOMAIN} → ${domain_ip}, IP сервера: ${server_ip}."
    warn "Проверьте A-запись (пропагация может занять время)."
  fi

  log "Выпуск сертификата Let's Encrypt: ${domains[*]}"
  if [ -n "$EMAIL" ]; then
    if ! certbot --nginx --non-interactive --agree-tos -m "$EMAIL" "${domains[@]}"; then
      warn "certbot не смог выпустить сертификат. Проверьте DNS и доступность порта 80,"
      warn "затем повторите: sudo bash $0 --domain ${DOMAIN} [--www] [--email ${EMAIL}]"
    fi
  else
    if ! certbot --nginx "${domains[@]}"; then
      warn "certbot не смог выпустить сертификат. Проверьте DNS и доступность порта 80,"
      warn "затем повторите: sudo bash $0 --domain ${DOMAIN} [--www] [--email ...]"
    fi
  fi
  ok "Сертификат выпущен: /etc/letsencrypt/live/${DOMAIN}"
}

# ---------- Главный ход -------------------------------------------------------

step_packages
step_project
step_host_nginx
step_certbot

echo ""
ok "TLS настроен для ${DOMAIN}!"
echo ""
echo "  Хостовый nginx:   /etc/nginx/sites-available/${NGINX_SITE}"
echo "  Сертификат:       /etc/letsencrypt/live/${DOMAIN} (автообновление через systemd-timer)"
echo "  Проект:           NGINX_PORTS=${NGINX_PORTS_VALUE} в ${PROJECT_DIR}/docker/.env.prod"
echo ""
echo "Дальнейшие шаги (docs/deployment.md, Шаг 4):"
echo "  1. Заполните ${PROJECT_DIR}/docker/.env.prod: APP_URL=https://${DOMAIN}, DB_*, MAIL_*, OPERATOR_*..."
echo "  2. make prod-up && make prod-migrate"
echo "  3. Проверка: curl -I https://${DOMAIN}/up"
