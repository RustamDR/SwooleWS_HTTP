FROM php:7.2.5-cli-stretch

ARG WORKDIR=/control
ARG GIT_TOKEN=''
ENV GIT_TOKEN ${GIT_TOKEN}

ADD php.ini /usr/local/etc/php
#ADD swoole2-1-3.tgz /tmp

RUN apt-get update \
    && apt-get install -y --no-install-recommends nano zlib1g-dev libmemcached-dev git libssl-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql

#####################################
# Memcached ext:
#####################################
RUN curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/php7.tar.gz" \
  && mkdir -p memcached \
  && tar -C memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
  && (cd memcached && phpize \
    && ./configure \
    && make -j$(nproc) && make install) \
  && rm /tmp/memcached.tar.gz \
  && rm -r memcached \
  && docker-php-ext-enable memcached

#####################################
# Swoole with SSL ext:
#####################################
RUN curl -L -o /tmp/swoole.tgz "https://pecl.php.net/get/swoole-2.1.3.tgz" \
  && mkdir -p swoole \
  && tar -C swoole -zxvf /tmp/swoole.tgz --strip 1 \
  && (cd swoole && phpize \
    && ./configure --enable-openssl --with-openssl-dir=/opt/openssl/ \
    && make -j$(nproc) && make install) \
  && rm /tmp/swoole.tgz \
  && rm -r swoole \
  && docker-php-ext-enable swoole

#####################################
# Composer:
#####################################
ADD ./composer.json /root/.composer/composer.json

RUN cd /usr/local/bin/ \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && echo "##########################################" \
    && echo "#                WARNING                 #" \
    && echo "##########################################" \
    && echo "#        ADD insecure_id_rsa.pub         #" \
    && echo "#       to YOUR github.com Account       #" \
    && echo "#        for autoloading composer        #" \
    && echo "#              dependancies              #" \
    && echo "##########################################"

RUN composer config -g github-oauth.github.com ${GIT_TOKEN} \
    && composer global install

WORKDIR ${WORKDIR}

CMD ["/bin/bash", "start.sh"]