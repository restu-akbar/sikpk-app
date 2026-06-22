#!/bin/sh

php artisan config:cache
php artisan route:cache || true
php artisan view:cache
php artisan event:cache || true

php artisan migrate --force

exec "$@"
