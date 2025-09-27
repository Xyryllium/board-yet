FROM php:8.3-fpm AS php

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libxslt-dev \
    zip unzip curl git \
    ca-certificates \
    postgresql-client \
    && docker-php-ext-install pdo_pgsql pgsql xsl


WORKDIR /var/www

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --prefer-dist --no-progress

RUN composer global require \
    squizlabs/php_codesniffer \
    phpmd/phpmd \
    phpstan/phpstan \
    && ln -s /root/.composer/vendor/bin/phpstan /usr/local/bin/phpstan \
    && ln -s /root/.composer/vendor/bin/phpcs /usr/local/bin/phpcs \
    && ln -s /root/.composer/vendor/bin/phpmd /usr/local/bin/phpmd \
    && ln -s /root/.composer/vendor/bin/phpcbf /usr/local/bin/phpcbf

RUN git config --global --add safe.directory /var/www

RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

COPY entrypoint.sh /usr/local/bin/laravel-setup.sh
RUN chmod +x /usr/local/bin/laravel-setup.sh

ENTRYPOINT ["/usr/local/bin/laravel-setup.sh"]

CMD ["php-fpm"]