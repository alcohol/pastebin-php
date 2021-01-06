FROM php:8.0-fpm-alpine AS base

# install non php packages
RUN set -eux; \
  apk add --no-cache --upgrade \
    bash \
    curl

# install non-default extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN set -eux; \
  chmod +x /usr/local/bin/install-php-extensions; \
  install-php-extensions \
    intl \
    pcntl \
    opcache \
    zip

# remove default configuration files
RUN set -eux; \
  rm -f \
    /usr/local/etc/php-fpm.conf.default \
    /usr/local/etc/php-fpm.d/docker.conf \
    /usr/local/etc/php-fpm.d/www.conf \
    /usr/local/etc/php-fpm.d/www.conf.default \
    /usr/local/etc/php-fpm.d/zz-docker.conf

# copy configuration files from project
COPY /docker/fpm/php.ini /usr/local/etc/php/
COPY /docker/fpm/php-cli.ini /usr/local/etc/php/
COPY /docker/fpm/php-fpm.conf /usr/local/etc/
COPY /docker/fpm/php-fpm-pool.conf /usr/local/etc/php-fpm.d/

# set permissions on files/directories
RUN set -eux; \
  find /srv -type d -exec chmod 700 {} \+; \
  find /srv -type f -exec chmod 400 {} \+; \
  chown -R www-data:www-data /srv; \
  find /usr/local/etc -type d -exec chmod 755 {} \+; \
  find /usr/local/etc -type f -exec chmod 644 {} \+

WORKDIR /srv

EXPOSE 9000/tcp

STOPSIGNAL SIGQUIT

FROM base AS composer

RUN set -eux; \
  apk add --no-cache --upgrade \
    openssh-client

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

FROM base AS production

ENV APP_SECRET 'not-very-secret-nor-secure-random-string-that-should-be-replaced'
ENV APP_ENV prod
ENV APP_DEBUG 0

COPY /docker/fpm/docker.ini /usr/local/etc/php/conf.d/

COPY /bin/ /srv/bin/
COPY /config/ /srv/config/
COPY /public/ /srv/public/
COPY /src/ /srv/src/
COPY /templates/ /srv/templates/
COPY /var/ /srv/var/
COPY /vendor/ /srv/vendor/
COPY /composer.json /srv/
COPY /composer.lock /srv/
