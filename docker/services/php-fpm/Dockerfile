FROM php:7.2-fpm-alpine3.7

WORKDIR /srv

COPY ["bin/", "/srv/bin/"]
COPY ["config/", "/srv/config/"]
COPY ["public/", "/srv/public/"]
COPY ["src/", "/srv/src/"]
COPY ["templates/", "/srv/templates/"]
COPY ["var/", "/srv/var/"]
COPY ["vendor/", "/srv/vendor/"]
COPY ["composer.json", "composer.lock", "/srv/"]

RUN find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 400 {} \+ \
 && find /srv/var -type f -exec chmod 600 {} \+ \
 && chown -R www-data:www-data /srv

EXPOSE 9000
USER www-data:www-data
