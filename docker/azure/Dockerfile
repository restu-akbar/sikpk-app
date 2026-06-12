FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip zip curl \
    libpq-dev libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath zip

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# 2. PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 3. JS dependencies
RUN npm install

# 4. BUILD VITE (INI YANG KRITIS)
RUN npm run build

# Permissions
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
