FROM alpine:3.9

LABEL traefik.enable=true
LABEL traefik.docker.network=traefik
LABEL traefik.backend=pastebin
LABEL traefik.frontend.rule=Host:paste.robbast.nl,p.robbast.nl
LABEL traefik.port=6081

ENV VARNISH_CONFIG=/etc/varnish/default.vcl
ENV VARNISH_CACHE_SIZE=64m
ENV VARNISHD_PARAMS="-p default_ttl=3600 -p default_grace=3600 -p vcc_allow_inline_c=on"
ENV VARNISH_IP=0.0.0.0
ENV VARNISH_PORT=6081
ENV VARNISH_ADMIN_IP=127.0.0.1
ENV VARNISH_ADMIN_PORT=6082

RUN apk add --update --no-cache libintl varnish \
 && rm -rf /etc/varnish \
 && mkdir /etc/varnish

COPY docker/services/varnish/*.vcl /etc/varnish/
COPY docker/services/varnish/varnish /

RUN chmod +x /varnish

CMD ["/varnish"]
