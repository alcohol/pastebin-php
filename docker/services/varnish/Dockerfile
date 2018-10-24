FROM alpine:3.8

ENV VARNISH_CONFIG=/etc/varnish/default.vcl
ENV VARNISH_CACHE_SIZE=64m
ENV VARNISHD_PARAMS="-p default_ttl=3600 -p default_grace=3600 -p vcc_allow_inline_c=on"
ENV VARNISH_IP=0.0.0.0
ENV VARNISH_PORT=6081
ENV VARNISH_ADMIN_IP=127.0.0.1
ENV VARNISH_ADMIN_PORT=6082

RUN apk add --update --no-cache libintl varnish=6.0.0-r1 \
 && rm -rf /etc/varnish \
 && mkdir /etc/varnish

COPY docker/services/varnish/*.vcl /etc/varnish/
COPY docker/services/varnish/varnish /

RUN chmod +x /varnish

CMD ["/varnish"]
