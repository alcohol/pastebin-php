FROM scratch

COPY --from=nginx:mainline-alpine / /

RUN apk add --no-cache curl

LABEL traefik.enable=true
LABEL traefik.docker.network=traefik
LABEL traefik.backend=pastebin
LABEL traefik.frontend.rule=Host:paste.robbast.nl,p.robbast.nl
LABEL traefik.port=8000

WORKDIR /srv

COPY /docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY /public/ /srv/public/

RUN touch /var/run/nginx.pid \
 && mkdir -p /var/cache/nginx/client_temp \
 && chown -R nginx /srv /var/cache/nginx /var/run/nginx.pid \
 && find /srv -type d -exec chmod 700 {} \+ \
 && find /srv -type f -exec chmod 600 {} \+

STOPSIGNAL SIGQUIT

EXPOSE 8000/tcp

USER nginx

CMD ["nginx", "-g", "daemon off;"]
