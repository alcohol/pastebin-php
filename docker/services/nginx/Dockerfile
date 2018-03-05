FROM scratch

COPY --from=nginx:mainline-alpine / /

RUN apk add --no-cache curl
HEALTHCHECK --interval=1m --timeout=3s \
 CMD curl --fail --head http://localhost:8000/health || exit 1

LABEL traefik.enable=true
LABEL traefik.backend=pastebin
LABEL traefik.frontend.rule=Host:paste.robbast.nl,p.robbast.nl
LABEL traefik.port=8000

WORKDIR /srv

COPY ["docker/services/nginx/nginx.conf", "/etc/nginx/"]
COPY ["public/", "/srv/public/"]

RUN touch /var/run/nginx.pid \
 && mkdir -p /var/cache/nginx/client_temp \
 && chown -R nginx /srv /var/cache/nginx /var/run/nginx.pid \
 && find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 600 {} \+

STOPSIGNAL SIGTERM
EXPOSE 8000
USER nginx

CMD ["nginx", "-g", "daemon off;"]
