#!/bin/sh

cd /var/www

php artisan migrate:fresh --force
php artisan db:seed

# php artisan cache:clear
# php artisan route:cache


/usr/bin/supervisord -c /etc/supervisord.conf
