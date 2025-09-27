FROM php:8.3-fpm AS php

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libxslt-dev \
    libzip-dev \
    zip unzip curl git \
    ca-certificates \
    postgresql-client \
    && docker-php-ext-install pdo_pgsql pgsql xsl zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts \
    && composer clear-cache

COPY . .

RUN composer run-script post-autoload-dump --no-interaction

RUN composer global require \
    squizlabs/php_codesniffer \
    phpmd/phpmd \
    phpstan/phpstan \
    && ln -s /root/.composer/vendor/bin/phpstan /usr/local/bin/phpstan \
    && ln -s /root/.composer/vendor/bin/phpcs /usr/local/bin/phpcs \
    && ln -s /root/.composer/vendor/bin/phpmd /usr/local/bin/phpmd \
    && ln -s /root/.composer/vendor/bin/phpcbf /usr/local/bin/phpcbf

RUN git config --global --add safe.directory /var/www \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

COPY entrypoint.sh /usr/local/bin/laravel-setup.sh
RUN chmod +x /usr/local/bin/laravel-setup.sh

ENTRYPOINT ["/usr/local/bin/laravel-setup.sh"]

CMD ["php-fpm"]