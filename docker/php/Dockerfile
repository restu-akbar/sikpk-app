FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install \
        pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

COPY --from=node:20 /usr/local/bin /usr/local/bin
COPY --from=node:20 /usr/local/lib /usr/local/lib

RUN npm install -g pnpm

RUN useradd -u 1000 -m -s /bin/bash kota301

COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist

RUN chown -R kota301:kota301 /var/www/html

USER kota301 
ENV HOME=/home/kota301

EXPOSE 9000

CMD ["php-fpm"]
