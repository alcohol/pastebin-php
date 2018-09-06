FROM php:7.2-fpm-alpine3.7

RUN apk add --update --no-cache tini \
 && apk add --update --no-cache --virtual .build-deps icu-dev zlib-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) intl mbstring opcache zip \
 && runDeps="$( \
    scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
    | tr ',' '\n' \
    | sort -u \
    | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )" \
 && apk add --virtual .phpext-rundeps $runDeps \
 && apk del .build-deps

WORKDIR /srv

RUN for dir in bin config public src templates var vendor; do mkdir -vp /srv/$dir; done

COPY ["bin/", "/srv/bin/"]
COPY ["config/", "/srv/config/"]
COPY ["public/", "/srv/public/"]
COPY ["src/", "/srv/src/"]
COPY ["templates/", "/srv/templates/"]
COPY ["vendor/", "/srv/vendor/"]
COPY ["composer.json", "composer.lock", "/srv/"]

# adjust permissions
RUN find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 400 {} \+ \
 && chown -R www-data:www-data /srv

# clean up some upstream configuration files for php-fpm
RUN rm /usr/local/etc/php-fpm.conf.default \
 && rm /usr/local/etc/php-fpm.d/docker.conf \
 && rm /usr/local/etc/php-fpm.d/www.conf \
 && rm /usr/local/etc/php-fpm.d/www.conf.default \
 && rm /usr/local/etc/php-fpm.d/zz-docker.conf

# add custom php ini files
COPY docker/services/php-fpm/php.ini /usr/local/etc/php/
COPY docker/services/php-fpm/php-cli.ini /usr/local/etc/php/
COPY docker/services/php-fpm/docker.ini /usr/local/etc/php/conf.d/

# add custom php-fpm conf files
COPY docker/services/php-fpm/php-fpm.conf /usr/local/etc/
COPY docker/services/php-fpm/php-fpm-pool.conf /usr/local/etc/php-fpm.d/

# adjust permissions
RUN find /usr/local/etc -type d -exec chmod 755 {} \+ \
 && find /usr/local/etc -type f -exec chmod 644 {} \+

EXPOSE 9000/tcp
