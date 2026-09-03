FROM php:7.3-fpm

ARG user=lojacorr
ARG uid=1000

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip libssl-dev pkg-config

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN pecl channel-update pecl.php.net \
    && pecl install redis-5.3.7 && docker-php-ext-enable redis \
    && pecl install mongodb-1.9.2 && docker-php-ext-enable mongodb

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && chown -R $user:$user /home/$user

WORKDIR /var/www

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY --chown=$user:$user . /var/www
RUN chown -R $user:$user /var/www

USER $user

RUN composer config --global allow-plugins true
RUN composer install --no-interaction --optimize-autoloader --no-scripts

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
