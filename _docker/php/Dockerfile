FROM php:8.3-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS \
    git \
    curl \
    wget \
    nano \
    libpq-dev \
    shadow \
    bash \
    tar \
    supervisor \
    libzip-dev \
    libzip \
    zip \
    libjpeg-turbo \
    freetype-dev \
    jpeg-dev \
    linux-headers

RUN docker-php-ext-install pdo pdo_pgsql

RUN pecl install redis
RUN docker-php-ext-enable redis.so


ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions xdebug
ENV PHP_IDE_CONFIG 'serverName=TgParserServer'
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.idekey = PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


#COPY ./_docker/supervisor /etc/supervisor
COPY ./_docker/supervisor/supervisord.conf /etc/
RUN mkdir "/var/log/supervisor"

WORKDIR /var/www

CMD ["/usr/bin/supervisord",  "-c",  "/etc/supervisord.conf"]
USER root