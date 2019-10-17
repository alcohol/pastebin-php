FROM php:7.3-fpm-alpine

RUN set -eux; \
  apk add --no-cache --upgrade \
    tini \
    bash \
    curl; \
  apk add --no-cache --virtual .build-deps \
    icu-dev \
    zlib-dev \
    libzip-dev; \
  docker-php-ext-configure zip --with-libzip; \
  docker-php-ext-configure intl; \
  docker-php-ext-install -j$(nproc) \
    intl \
    pcntl \
    mbstring \
    opcache \
    zip; \
  runDeps="$( \
    scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
      | tr ',' '\n' \
      | sort -u \
      | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
   apk add --virtual .phpext-rundeps $runDeps; \
   apk del .build-deps

WORKDIR /srv

RUN set -eux; \
  rm -f \
    /usr/local/etc/php-fpm.conf.default \
    /usr/local/etc/php-fpm.d/docker.conf \
    /usr/local/etc/php-fpm.d/www.conf \
    /usr/local/etc/php-fpm.d/www.conf.default \
    /usr/local/etc/php-fpm.d/zz-docker.conf

COPY /docker/fpm/php.ini /usr/local/etc/php/
COPY /docker/fpm/php-cli.ini /usr/local/etc/php/
COPY /docker/fpm/php-fpm.conf /usr/local/etc/
COPY /docker/fpm/php-fpm-pool.conf /usr/local/etc/php-fpm.d/

RUN set -eux; \
  find /srv -type d -exec chmod 700 {} \+; \
  find /srv -type f -exec chmod 400 {} \+; \
  chown -R www-data:www-data /srv; \
  find /usr/local/etc -type d -exec chmod 755 {} \+; \
  find /usr/local/etc -type f -exec chmod 644 {} \+

EXPOSE 9000/tcp

STOPSIGNAL SIGQUIT
