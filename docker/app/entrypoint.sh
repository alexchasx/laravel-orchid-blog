#!/bin/sh
set -e

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
