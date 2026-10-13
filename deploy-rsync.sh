#!/usr/bin/env bash
# =============================================================================
# deploy-rsync.sh — доставка кода на сервер через rsync (без git на сервере)
# =============================================================================
# Альтернатива `git clone`/`git pull` на сервере: серверу не нужен SSH-доступ
# к вашему git-репозиторию — код копируется с локальной машины.
#
# Запускать на ЛОКАЛЬНОЙ машине из корня репозитория:
#   ./deploy-rsync.sh deploy@example.com            # → /opt/blog
#   ./deploy-rsync.sh deploy@example.com /srv/blog  # → /srv/blog
#
# После копирования на сервере:
#   cd /opt/blog && ./deploy.sh --no-pull
# (без каталога .git deploy.sh и сам пропустит git pull, но --no-pull явнее)
#
# Исключения зеркалируют .dockerignore: всё, что не нужно для сборки
# продакшн-образа (vendor/, node_modules/, public/build собираются внутри
# образа — multi-stage Dockerfile.prod), плюс секреты и runtime-данные,
# которые живут только на сервере (docker/.env.prod, storage/ в volume
# app_storage). Шаблон docker/env.prod.example НЕ исключается — из него
# на сервере создаётся docker/.env.prod (make prod-env).
# =============================================================================

set -euo pipefail

# ---------- Аргументы --------------------------------------------------------

DEST_HOST="${1:-}"
DEST_DIR="${2:-/opt/blog}"

if [ -z "$DEST_HOST" ]; then
  echo "Использование: $0 <user@host> [каталог на сервере]" >&2
  echo "Пример:        $0 deploy@example.com /opt/blog" >&2
  exit 1
fi

# ---------- Исключения (зеркало .dockerignore + серверные файлы) -------------

EXCLUDES=(
  --exclude '.git'
  --exclude 'vendor'
  --exclude 'node_modules'
  --exclude 'storage'
  # Скомпилированные кэши Laravel (bootstrap/cache/packages.php и services.php)
  # содержат dev-only провайдеры (laravel-lang) — на сервере они не нужны и
  # ломают загрузку приложения («Class "LaravelLang\Config\ServiceProvider" not
  # found»). Зеркало .dockerignore; манифест генерирует Dockerfile.prod.
  --exclude 'bootstrap/cache'
  --exclude 'docker/.env.prod'
  --exclude 'docker/tmp'
  --exclude '.env'
  --exclude '.env.*'
  --exclude 'public/build'
  --exclude 'public/storage'
  --exclude 'public/hot'
  --exclude 'tests'
  --exclude 'docs'
  --exclude 'plans'
  --exclude 'prompts'
  --exclude 'screenshots'
  --exclude '.idea'
  --exclude '.vscode'
  --exclude '.codeassistant'
  --exclude '.gigacode*'
  --exclude 'opencode.json'
  --exclude 'context_handoff*.md'
  --exclude '.phpunit.result.cache'
  --exclude 'npm-debug.log'
  --exclude 'yarn-error.log'
  --exclude 'composer.phar'
  --exclude 'Homestead.json'
  --exclude 'Homestead.yaml'
  --exclude 'database_dump*.sql'
)

# ---------- Копирование ------------------------------------------------------

echo "→ rsync ./ → $DEST_HOST:$DEST_DIR/"
rsync -avz --delete "${EXCLUDES[@]}" ./ "$DEST_HOST:$DEST_DIR/"

echo ""
echo "✓ Код скопирован на $DEST_HOST:$DEST_DIR"
echo "  Дальше на сервере:"
echo "    cd $DEST_DIR && ./deploy.sh --no-pull"
