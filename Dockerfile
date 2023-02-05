FROM php:8.0-fpm-alpine

RUN apk add --no-cache nginx wget

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app
COPY . /app

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable pdo_mysql

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev

RUN chown -R www-data: /app

ENV DB_SOCKET /cloudsql/mylabscloud:europe-west1:myadvantage
ENV DB_HOST 127.0.0.1
ENV DB_USERNAME root
ENV DB_PORT 3306
ENV DB_PASSWORD .(]epm?(:LJpp8_j
ENV DB_DATABASE myadvantage

RUN cd /app && php artisan migrate --force
RUN php artisan db:seed --force
RUN php artisan vendor:publish --tag=public --force
RUN php artisan storage:link


# g

CMD sh /app/docker/startup.sh