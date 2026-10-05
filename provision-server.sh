#!/usr/bin/env bash
# =============================================================================
# provision-server.sh — подготовка Ubuntu 24.04 LTS (VDS/VPS) к первому деплою
# =============================================================================
# Ставит на чистом сервере ВСЁ, что нужно ПЕРЕД первым деплоем продакшн-стека
# (docker/docker-compose.prod.yml):
#
#   1. базовые пакеты и обновления ОС;
#   2. часовой пояс Europe/Moscow (совпадает с config/app.php);
#   3. swap-файл (по умолчанию 2 ГБ, swappiness=10);
#   4. пользователя для деплоя (sudo + группа docker) и его SSH-ключ;
#   5. ужесточение sshd: root запрещён, парольный вход запрещён;
#   6. Docker Engine + Compose plugin из официального репозитория Docker;
#   7. firewall ufw: разрешены только SSH/80/443;
#   8. (опционально) fail2ban и unattended-upgrades;
#   9. каталог бэкапов /var/backups/blog.
#
# Использование (на сервере, от root или через sudo):
#   sudo bash provision-server.sh --user deploy \
#     --ssh-key "ssh-ed25519 AAAA... user@host" \
#     --with-fail2ban --with-unattended
#
# Флаги:
#   --user NAME            имя пользователя для деплоя (по умолчанию: deploy)
#   --ssh-key "KEY"        публичный SSH-ключ пользователя. Рекомендуется
#                          указывать всегда: без ключа шаг «ужесточение sshd»
#                          пропускается (риск заблокировать самому себе вход)
#   --swap SIZE_GB         размер swap-файла (по умолчанию: 2)
#   --with-fail2ban        установить и включить fail2ban
#   --with-unattended      включить автоматические обновления безопасности
#   --skip-firewall        не трогать ufw
#   -h|--help              показать справку
#
# Повторный запуск безопасен (идемпотентность): уже выполненные шаги
# пропускаются. Если в конце скрипт сообщит о необходимости reboot —
# перезагрузите сервер (обновилось ядро).
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

DEPLOY_USER="deploy"
SSH_KEY=""
SWAP_GB=2
WITH_FAIL2BAN=0
WITH_UNATTENDED=0
SKIP_FIREWALL=0
TZ="Europe/Moscow"

while [ $# -gt 0 ]; do
  case "$1" in
    --user)            DEPLOY_USER="${2:?--user требует значение}"; shift 2 ;;
    --ssh-key)         SSH_KEY="${2:?--ssh-key требует значение}"; shift 2 ;;
    --swap)            SWAP_GB="${2:?--swap требует значение}"; shift 2 ;;
    --with-fail2ban)   WITH_FAIL2BAN=1; shift ;;
    --with-unattended) WITH_UNATTENDED=1; shift ;;
    --skip-firewall)   SKIP_FIREWALL=1; shift ;;
    -h|--help)         show_help ;;
    *) die "Неизвестный аргумент: $1 (см. --help)" ;;
  esac
done

[ "$DEPLOY_USER" != "root" ] || die "--user не может быть root."
case "$SWAP_GB" in
  ''|*[!0-9]*) die "--swap должен быть целым числом (гигабайты): '$SWAP_GB'." ;;
esac
[ "$SWAP_GB" -gt 0 ] || die "--swap должен быть больше 0."

# ---------- Предпроверки ------------------------------------------------------

if [ "$(id -u)" -ne 0 ]; then
  die "Запустите с правами root: sudo bash $0 ..."
fi

[ -r /etc/os-release ] || die "/etc/os-release не найден — это не Ubuntu."
# shellcheck disable=SC1091
. /etc/os-release
if [ "${ID:-}" != "ubuntu" ] || [ "${VERSION_ID:-}" != "24.04" ]; then
  die "Скрипт рассчитан на Ubuntu 24.04 LTS (сейчас: ${ID:-?} ${VERSION_ID:-?})."
fi

export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_MODE=a   # не задавать интерактивных вопросов при перезапуске служб

# Порт SSH, используемый сейчас (нужен для firewall). Определяем до изменений.
SSH_PORT="22"
if command -v sshd >/dev/null 2>&1; then
  SSH_PORT="$(sshd -T 2>/dev/null | awk '/^port /{print $2; exit}')"
fi
SSH_PORT="${SSH_PORT:-22}"

log "Предпроверки пройдены: Ubuntu ${VERSION_ID}, root, SSH порт ${SSH_PORT}."

# ---------- 1. Обновление ОС и базовые пакеты ---------------------------------

step_system() {
  log "Обновление списка пакетов и установка базовых пакетов"
  apt-get update -qq
  apt-get install -y -qq \
    ca-certificates curl gnupg lsb-release software-properties-common \
    git rsync jq htop unzip openssh-server
  apt-get upgrade -y -qq
  ok "Система обновлена, базовые пакеты установлены"
}

# ---------- 2. Часовой пояс ---------------------------------------------------

step_timezone() {
  log "Часовой пояс → ${TZ}"
  if command -v timedatectl >/dev/null 2>&1; then
    timedatectl set-timezone "$TZ"
  else
    ln -sf "/usr/share/zoneinfo/$TZ" /etc/localtime
  fi
  ok "Часовой пояс: $(cat /etc/timezone 2>/dev/null)"
}

# ---------- 3. Swap ------------------------------------------------------------

step_swap() {
  if swapon --show=NAME --noheadings | grep -q .; then
    warn "swap уже настроен — пропускаю"
    swapon --show
    return
  fi
  log "Создание swap-файла ${SWAP_GB}G (/swapfile)"
  if ! fallocate -l "${SWAP_GB}G" /swapfile 2>/dev/null; then
    # fallocate не поддержан файловой системой — создаём через dd
    rm -f /swapfile
    dd if=/dev/zero of=/swapfile bs=1M count=$((SWAP_GB * 1024)) conv=fsync status=progress
  fi
  chmod 600 /swapfile
  mkswap /swapfile >/dev/null
  swapon /swapfile
  grep -q '^/swapfile ' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
  echo 'vm.swappiness=10' > /etc/sysctl.d/99-blog-swappiness.conf
  sysctl -w vm.swappiness=10 >/dev/null
  ok "Swap включён (${SWAP_GB}G, swappiness=10)"
}

# ---------- 4. Пользователь для деплоя -----------------------------------------

step_deploy_user() {
  if id "$DEPLOY_USER" &>/dev/null; then
    warn "Пользователь ${DEPLOY_USER} уже существует — пропускаю создание"
  else
    log "Создание пользователя ${DEPLOY_USER} (sudo)"
    useradd -m -s /bin/bash -G sudo "$DEPLOY_USER"
    passwd -l "$DEPLOY_USER" >/dev/null   # пароль заблокирован — вход только по ключу
    echo "$DEPLOY_USER ALL=(ALL) NOPASSWD:ALL" > "/etc/sudoers.d/$DEPLOY_USER"
    chmod 440 "/etc/sudoers.d/$DEPLOY_USER"
    ok "Пользователь ${DEPLOY_USER} создан (passwordless sudo, пароль заблокирован)"
  fi

  if [ -n "$SSH_KEY" ]; then
    log "Добавление SSH-ключа для ${DEPLOY_USER}"
    install -d -m 700 -o "$DEPLOY_USER" -g "$DEPLOY_USER" "/home/$DEPLOY_USER/.ssh"
    local auth="/home/$DEPLOY_USER/.ssh/authorized_keys"
    touch "$auth"
    chown "$DEPLOY_USER:$DEPLOY_USER" "$auth"
    chmod 600 "$auth"
    grep -qxF "$SSH_KEY" "$auth" || echo "$SSH_KEY" >> "$auth"
    ok "SSH-ключ добавлен в $auth"
  fi
}

# ---------- 5. Ужесточение sshd -------------------------------------------------

step_ssh_hardening() {
  local auth="/home/$DEPLOY_USER/.ssh/authorized_keys"
  if [ ! -s "$auth" ]; then
    warn "У ${DEPLOY_USER} нет SSH-ключей — ужесточение sshd ПРОПУЩЕНО (риск блокировки)."
    warn "Добавьте ключ повторным запуском: sudo bash $0 --user ${DEPLOY_USER} --ssh-key \"...\""
    return
  fi

  log "Ужесточение sshd (root запрещён, парольный вход запрещён)"
  cat > /etc/ssh/sshd_config.d/99-blog-hardening.conf <<EOF
# Сгенерировано provision-server.sh
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
PubkeyAuthentication yes
AllowUsers ${DEPLOY_USER}
EOF
  chmod 644 /etc/ssh/sshd_config.d/99-blog-hardening.conf
  sshd -t || die "Конфигурация sshd некорректна — правки не применены"
  systemctl reload ssh
  ok "sshd: PermitRootLogin no, PasswordAuthentication no, AllowUsers ${DEPLOY_USER}"
}

# ---------- 6. Docker Engine + Compose plugin ------------------------------------

step_docker() {
  if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    warn "Docker Engine + Compose plugin уже установлены — пропускаю"
  else
    log "Установка Docker Engine + Compose plugin (официальный репозиторий Docker)"
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] \
https://download.docker.com/linux/ubuntu ${VERSION_CODENAME} stable" \
      > /etc/apt/sources.list.d/docker.list
    apt-get update -qq
    apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    systemctl enable --now docker
    ok "Docker установлен"
  fi

  log "Добавление ${DEPLOY_USER} в группу docker"
  usermod -aG docker "$DEPLOY_USER"
  ok "docker --version:        $(docker --version)"
  ok "docker compose version:  $(docker compose version)"
}

# ---------- 7. Firewall (ufw) -----------------------------------------------------

step_firewall() {
  if [ "$SKIP_FIREWALL" -eq 1 ]; then
    warn "Firewall пропущен (--skip-firewall)"
    return
  fi
  if ! command -v ufw >/dev/null 2>&1; then
    apt-get install -y -qq ufw
  fi
  log "Настройка ufw (разрешены: SSH/${SSH_PORT}, 80, 443)"
  ufw default deny incoming
  ufw default allow outgoing
  ufw allow "$SSH_PORT"/tcp comment 'SSH'
  ufw allow 80/tcp comment 'HTTP'
  ufw allow 443/tcp comment 'HTTPS'
  ufw --force enable
  ok "Firewall включён: SSH/${SSH_PORT}, 80, 443"
  ufw status verbose | head -n 6
}

# ---------- 8. fail2ban ------------------------------------------------------------

step_fail2ban() {
  log "Установка fail2ban"
  apt-get install -y -qq fail2ban
  cat > /etc/fail2ban/jail.local <<EOF
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5

[sshd]
enabled  = true
backend  = systemd
EOF
  systemctl enable --now fail2ban
  ok "fail2ban включён (jail sshd)"
}

# ---------- 9. Автоматические обновления --------------------------------------------

step_unattended() {
  log "Включение автоматических обновлений безопасности"
  apt-get install -y -qq unattended-upgrades
  cat > /etc/apt/apt.conf.d/20auto-upgrades <<EOF
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
EOF
  systemctl enable --now unattended-upgrades
  ok "unattended-upgrades включён"
}

# ---------- 10. Каталог бэкапов -------------------------------------------------------

step_backup_dir() {
  log "Создание каталога бэкапов /var/backups/blog"
  install -d -m 775 -o "$DEPLOY_USER" -g "$DEPLOY_USER" /var/backups/blog
  ok "/var/backups/blog готов (владелец: ${DEPLOY_USER})"
}

# ---------- Главный ход -----------------------------------------------------------

log "Подготовка сервера (Ubuntu ${VERSION_ID}) к деплою: пользователь ${DEPLOY_USER}, swap ${SWAP_GB}G"

step_system
step_timezone
step_swap
step_deploy_user
step_ssh_hardening
step_docker
step_firewall

if [ "$WITH_FAIL2BAN" -eq 1 ]; then
  step_fail2ban
fi
if [ "$WITH_UNATTENDED" -eq 1 ]; then
  step_unattended
fi

step_backup_dir

echo ""
ok "Подготовка сервера завершена!"
echo ""
echo "  Пользователь деплоя:  ${DEPLOY_USER}"
echo "  Часовой пояс:          $(cat /etc/timezone 2>/dev/null)"
echo "  Docker:                $(docker --version 2>/dev/null || echo 'перезайдите в сессию, чтобы попасть в группу docker')"
echo "  Firewall (ufw):        $(ufw status | head -n 1)"
echo "  Каталог бэкапов:       /var/backups/blog"
echo ""
echo "Дальнейшие шаги — см. README.md, раздел «Деплой на продакшн (Ubuntu 24.04 VPS)»:"
echo "  1. Настройте DNS (A-запись домена на IP сервера)."
echo "  2. Настройте TLS (рекомендуется хостовый nginx + certbot)."
echo "  3. Склонируйте репозиторий, заполните docker/.env.prod и выполните первый деплой."

if [ -f /var/run/reboot-required ]; then
  warn "Требуется перезагрузка (обновление ядра): sudo reboot"
fi
