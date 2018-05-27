FROM redis:latest

MAINTAINER Mahmoud Zalt <mahmoud@zalt.me>

## For security settings uncomment, make the dir, copy conf, and also start with the conf, to use it
RUN mkdir -p /usr/local/etc/redis
COPY 6379.conf /usr/local/etc/redis/6379.conf

CMD ["redis-server", "/usr/local/etc/redis/6379.conf"]
#CMD ["redis-server"]
