#!/bin/sh

sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

php-fpm -D

echo ">>Running migration..."
php artisan migrate --force
echo ">>Running seeding..."
php artisan db:seed --force
echo ">>Publishing vendor assets..."
php artisan vendor:publish --tag=public --force
echo ">>Linking storage..."
php artisan storage:link

while ! nc -w 1 -z 127.0.0.1 9000; do sleep 0.1; done;

nginx