#!/bin/sh
set -e

# /var/www — bind-mount с хоста, поэтому chown из Dockerfile на build не помогает.
# Приводим права на запись в storage/ и bootstrap/cache под www-data (PHP-FPM).
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

exec "$@"