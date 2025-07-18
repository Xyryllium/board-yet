FROM php:8.3-fpm AS php

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libxslt-dev \
    zip unzip curl git \
    ca-certificates \
    && docker-php-ext-install pdo_pgsql pgsql xsl


WORKDIR /var/www

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer global require \
    squizlabs/php_codesniffer \
    phpmd/phpmd \
    phpstan/phpstan \
    && ln -s /root/.composer/vendor/bin/phpstan /usr/local/bin/phpstan \
    && ln -s /root/.composer/vendor/bin/phpcs /usr/local/bin/phpcs \
    && ln -s /root/.composer/vendor/bin/phpmd /usr/local/bin/phpmd \
    && ln -s /root/.composer/vendor/bin/phpcbf /usr/local/bin/phpcbf

COPY ./Docker/entrypoint.sh /usr/local/bin/laravel-setup.sh
RUN chmod +x /usr/local/bin/laravel-setup.sh

ENTRYPOINT ["/usr/local/bin/laravel-setup.sh"]

CMD ["php-fpm"]