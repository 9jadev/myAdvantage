FROM php:8.0-fpm-alpine

RUN apk add --no-cache nginx wget

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app
COPY . /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev

RUN chown -R www-data: /app

ENV DB_SOCKET /cloudsql/mylabscloud:europe-west1:myadvantage
ENV DB_HOST 34.77.38.136
ENV DB_USERNAME root
ENV DB_PORT 3306
ENV DB_PASSWORD .(]epm?(:LJpp8_j

CMD sh /app/docker/startup.sh