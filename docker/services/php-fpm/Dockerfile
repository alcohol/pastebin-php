FROM php:7.2-fpm-alpine3.7

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
 && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/amd64/$version \
 && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
 && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
 && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini

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
