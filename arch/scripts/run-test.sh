#!/usr/bin/env bash
echo ">>Installing dependencies..."
composer install
if [[ ! -f "${WORKING_DIR}/${APP_WORKSPACE}/storage/oauth-private.key" ]]; then
    echo ">>Generating passport keys..."
    php artisan passport:keys
fi
echo ">>Fresh migration..."
php artisan migrate:fresh
echo ">>Running seeding..."
php artisan db:seed --force
echo ">>Running test..."
composer test
