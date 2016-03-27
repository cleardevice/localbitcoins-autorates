FROM alpine:3.3
MAINTAINER cd "cleardevice@gmail.com"

ADD ./ /lbtc
RUN apk add --no-cache php-cli php-curl php-json php-phar php-openssl ca-certificates && \
    apk add --no-cache grc --repository http://dl-4.alpinelinux.org/alpine/edge/testing/ && \
    cd /lbtc && ash bin/composer-install.sh && ./composer.phar update

WORKDIR /root
ENV PATH $PATH:/lbtc/bin:/lbtc/bin/rub:/lbtc/bin/usd:/lbtc/bin/uah

CMD ["/bin/ash", "true"]
