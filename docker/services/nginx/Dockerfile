FROM nginx:mainline-alpine

LABEL traefik.enable=true
LABEL traefik.backend=pastebin
LABEL traefik.frontend.rule=Host:paste.robbast.nl,p.robbast.nl
LABEL traefik.port=80
LABEL traefik.docker.network=traefik

WORKDIR /srv

COPY ["docker/services/nginx/nginx.conf", "/etc/nginx/"]
COPY ["web/", "/srv/web/"]

RUN chown -R nginx /srv/web \
 && find /srv/web -type d -exec chmod 700 {} \+ \
 && find /srv/web -type f -exec chmod 600 {} \+
