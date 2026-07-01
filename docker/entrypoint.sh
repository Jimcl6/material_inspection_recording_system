#!/bin/sh
set -e

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

ln -sfn ../storage/app/public public/storage
chown -R www-data:www-data storage bootstrap/cache public/storage

exec "$@"
