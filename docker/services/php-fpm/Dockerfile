FROM php:7.1-fpm-alpine

WORKDIR /srv

COPY ["app/", "/srv/app/"]
COPY ["bin/", "/srv/bin/"]
COPY ["cfg/", "/srv/cfg/"]
COPY ["src/", "/srv/src/"]
COPY ["var/", "/srv/var/"]
COPY ["vendor/", "/srv/vendor/"]
COPY ["web/", "/srv/web/"]

RUN find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 400 {} \+ \
 && find /srv/var -type f -exec chmod 600 {} \+ \
 && chown -R www-data:www-data /srv

USER www-data:www-data
