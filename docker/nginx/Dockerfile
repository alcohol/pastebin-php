FROM nginx:mainline-alpine

# nginx.conf.template
ENV CLIENT_BODY_TIMEOUT 2s
ENV CLIENT_HEADER_TIMEOUT 2s
ENV KEEPALIVE_TIMEOUT 60s
ENV SEND_TIMEOUT 2s
ENV RESOLVER_TIMEOUT 1s

# server.conf.template
ENV FASTCGI_CONNECT_TIMEOUT 1s
ENV FASTCGI_READ_TIMEOUT 4s
ENV FASTCGI_SEND_TIMEOUT 2s

RUN set -eux ; \
  apk add --no-cache \
    bash \
    coreutils ; \
  rm -v /etc/nginx/nginx.conf /etc/nginx/conf.d/*

COPY /docker/nginx/nginx.conf.template /etc/nginx/
COPY /docker/nginx/server.conf.template /etc/nginx/conf.d/

WORKDIR /srv

COPY /public/ /srv/public/

RUN set -eux ; \
  chown -vR root:nginx /srv ; \
  find /srv -type d -exec chmod 750 {} \+ ; \
  find /srv -type f -exec chmod 660 {} \+

STOPSIGNAL SIGTERM

EXPOSE 80/tcp

CMD /bin/sh -c " \
  envsubst '\$FASTCGI_CONNECT_TIMEOUT \$FASTCGI_READ_TIMEOUT \$FASTCGI_SEND_TIMEOUT' \
    < /etc/nginx/conf.d/server.conf.template \
    > /etc/nginx/conf.d/server.conf && \
  envsubst '\$CLIENT_BODY_TIMEOUT \$CLIENT_HEADER_TIMEOUT \$KEEPALIVE_TIMEOUT \$SEND_TIMEOUT \$RESOLVER_TIMEOUT' \
    < /etc/nginx/nginx.conf.template \
    > /etc/nginx/nginx.conf && \
  exec nginx -g 'daemon off;'"
