#!/bin/sh
set -e

# Fail-fast: в проде код лежит внутри образа. Если vendor/ или artisan нет —
# образ собран неверно (например, запущен dev-образ на общем теге). Падаем
# сразу с внятным сообщением вместо каскада ошибок Laravel/Composer.
# Проверка активна только при APP_ENV=production (dev-контейнер его не задаёт).
if [ "$APP_ENV" = "production" ] && { [ ! -f /var/www/artisan ] || [ ! -f /var/www/vendor/autoload.php ]; }; then
  echo "ERROR: production image is incomplete: /var/www/artisan or /var/www/vendor/autoload.php is missing." >&2
  echo "Rebuild the production image: docker compose -f docker/docker-compose.prod.yml build" >&2
  exit 1
fi

# В проде /var/www лежит внутри образа, а storage/ — отдельный volume,
# который при первом запуске пуст и принадлежит root. Создаём обязательные
# каталоги и приводим права на запись под www-data (PHP-FPM/artisan).
# В dev (bind-mount с хоста) каталоги уже есть — mkdir/chown безопасны,
# chown выполняется только когда процесс от root, иначе молча пропускается.
mkdir -p \
  /var/www/storage/app/public \
  /var/www/storage/framework/cache/data \
  /var/www/storage/framework/sessions \
  /var/www/storage/framework/views \
  /var/www/storage/logs \
  /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true
chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache || true

exec "$@"
