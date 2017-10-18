FROM nginx:mainline-alpine

LABEL traefik.enable=true
LABEL traefik.backend=pastebin
LABEL traefik.frontend.rule=Host:paste.robbast.nl,p.robbast.nl
LABEL traefik.port=8000
LABEL traefik.docker.network=traefik

WORKDIR /srv

COPY ["docker/services/nginx/nginx.conf", "/etc/nginx/"]
COPY ["web/", "/srv/web/"]

RUN touch /var/run/nginx.pid \
 && chown -R nginx /srv /var/cache/nginx /var/run/nginx.pid \
 && find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 600 {} \+

USER nginx
