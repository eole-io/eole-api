FROM phpdockerio/php71-fpm

RUN apt-get update \
    && apt-get -y --no-install-recommends install php7.1-mysql php-zmq php7.1-sqlite3 \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

WORKDIR "/var/www/html"
