FROM php:8.3.2-fpm-bullseye

RUN apt-get update -y && apt-get install -y --no-install-recommends \
  apt-utils \
  libzip-dev \
  libsqlite3-dev \
  unzip 

RUN docker-php-ext-install pdo pdo_sqlite

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/app
RUN cd /var/www/app
RUN chown www-data:www-data /var/www/app

COPY --chown=www-data:www-data ./app .
RUN rm -rf vendor
RUN composer install

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/app/public"]