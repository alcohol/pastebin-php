FROM php:7.3-fpm-alpine AS builder

ENV APP_SECRET ''
ENV APP_ENV prod
ENV APP_DEBUG 0
ENV SENTRY_DSN ''
ENV SENTRY_RELEASE ''
ENV SENTRY_ENVIRONMENT ''

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

WORKDIR /srv

COPY /bin/ /srv/bin/
COPY /config/ /srv/config/
COPY /public/ /srv/public/
COPY /src/ /srv/src/
COPY /templates/ /srv/templates/
COPY /composer.json /srv/
COPY /composer.lock /srv/

RUN composer install \
    --no-interaction \
    --no-scripts \
    --no-plugins \
    --no-progress \
    --no-suggest \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-dev \
 && bin/console cache:warmup

FROM php:7.3-fpm-alpine

ARG RELEASE
ENV SENTRY_RELEASE ${RELEASE}

RUN apk add --update --no-cache --virtual .build-deps icu-dev zlib-dev libzip-dev \
 && docker-php-ext-configure zip --with-libzip \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) intl mbstring opcache zip \
 && runDeps="$( \
  scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
    | tr ',' '\n' \
    | sort -u \
    | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
  )" \
 && apk add --virtual .phpext-rundeps $runDeps \
 && apk del --no-network --purge .build-deps

COPY --from=builder /srv /srv/

# adjust permissions
RUN find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 400 {} \+ \
 && chown -R www-data:www-data /srv

# remove some upstream configuration files
RUN rm /usr/local/etc/php-fpm.conf.default \
 && rm /usr/local/etc/php-fpm.d/docker.conf \
 && rm /usr/local/etc/php-fpm.d/www.conf \
 && rm /usr/local/etc/php-fpm.d/www.conf.default \
 && rm /usr/local/etc/php-fpm.d/zz-docker.conf

# add custom php ini files
COPY /docker/php-fpm/php.ini /usr/local/etc/php/
COPY /docker/php-fpm/php-cli.ini /usr/local/etc/php/
COPY /docker/php-fpm/docker.ini /usr/local/etc/php/conf.d/

# add custom php-fpm conf files
COPY /docker/php-fpm/php-fpm.conf /usr/local/etc/
COPY /docker/php-fpm/php-fpm-pool.conf /usr/local/etc/php-fpm.d/

# adjust permissions
RUN find /usr/local/etc -type d -exec chmod 755 {} \+ \
 && find /usr/local/etc -type f -exec chmod 644 {} \+

EXPOSE 9000/tcp

STOPSIGNAL SIGQUIT
