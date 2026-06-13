FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip zip curl \
    libpq-dev libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath zip

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install

RUN npm run build

RUN php artisan config:cache
RUN php artisan route:cache || true
RUN php artisan view:cache

RUN php artisan event:cache || true
RUN composer dump-autoload --optimize

RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
