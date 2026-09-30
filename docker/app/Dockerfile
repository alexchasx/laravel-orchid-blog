FROM php:8.5.2-fpm-bookworm

LABEL maintainer="alexchasx"

RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      procps \
      zip unzip \
      git && \
      docker-php-ext-install pdo_mysql && \
      docker-php-ext-install bcmath && \
      docker-php-ext-install gd && \
      docker-php-ext-install zip && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
      mkdir -p /var/www && chown -R www-data:www-data /var/www && \
      chmod -R 755 /var/www

# Предпочитать IPv4 при резолве имён (иначе getaddrinfo возвращает IPv6 первым,
# а маршрута IPv6 в Docker-сети нет — curl/composer упираются в таймаут).
RUN sed -i 's|^#\s*precedence ::ffff:0:0/96  100|precedence ::ffff:0:0/96  100|' /etc/gai.conf

COPY ./app/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./app/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

WORKDIR /var/www
