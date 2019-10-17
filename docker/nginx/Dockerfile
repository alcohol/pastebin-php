FROM scratch

COPY --from=nginx:mainline-alpine / /

WORKDIR /srv

COPY /docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY /public/ /srv/public/

RUN set -eux; \
  touch /var/run/nginx.pid; \
  mkdir -p /var/cache/nginx/client_temp; \
  chown -R nginx /srv /var/cache/nginx /var/run/nginx.pid; \
  find /srv -type d -exec chmod 700 {} \+; \
  find /srv -type f -exec chmod 600 {} \+

STOPSIGNAL SIGQUIT

EXPOSE 8000/tcp

USER nginx

CMD ["nginx", "-g", "daemon off;"]
