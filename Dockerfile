FROM php:7.1-alpine

RUN apk --no-cache add curl git openssh openssl tini

RUN echo "memory_limit=-1" > "$PHP_INI_DIR/conf.d/memory-limit.ini" \
 && echo "date.timezone=${PHP_TIMEZONE:-UTC}" > "$PHP_INI_DIR/conf.d/date_timezone.ini" \
 && echo "always_populate_raw_post_data=-1" > "$PHP_INI_DIR/conf.d/always_populate_raw_post_data.ini"

ENV COMPOSER_HOME /composer
ENV PATH "/composer/vendor/bin:$PATH"
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -s -f -L -o /tmp/composer-setup.php https://getcomposer.org/installer \
 && curl -s -f -L -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
 && php -r " \
    \$hash = hash('SHA384', file_get_contents('/tmp/composer-setup.php')); \
    \$signature = trim(file_get_contents('/tmp/composer-setup.sig')); \
    if (!hash_equals(\$signature, \$hash)) { \
        unlink('/tmp/composer-setup.php'); \
        echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
        exit(1); \
    }" \
 && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/bin --filename=composer \
 && rm /tmp/composer-setup.php \
 && composer --no-interaction --no-ansi --version

WORKDIR /app

COPY ["composer.json", "composer.lock", "LICENSE", "/app/"]
COPY /app /app/app/
COPY /cfg /app/cfg/
COPY /src /app/src/
COPY /web /app/web/

RUN composer install --no-interaction --no-ansi --no-autoloader --no-scripts --no-plugins --no-dev

VOLUME /composer
EXPOSE 8000

CMD ["/sbin/tini", "--", "/usr/local/bin/php", "-S", "0.0.0.0:8000", "-t", "/app/web"]
