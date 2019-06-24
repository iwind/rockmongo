FROM php:5-apache

RUN \
  apt-get update && \
  DEBIAN_FRONTEND=noninteractive \
    apt-get -y install \
      build-essential \
      php5-dev \
  && \
  pecl channel-update pecl.php.net && \
  pecl install mongo && \
  rm -vrf /build /tmp/pear && \
  echo "extension=mongo.so" > /usr/local/etc/php/conf.d/mongo.ini && \
  DEBIAN_FRONTEND=noninteractive \
    apt-get -y autoremove --purge \
      automake \
      autotools-dev \
      bsdmainutils \
      build-essential \
      php5-dev \
      psmisc \
  && \
  apt-get autoclean && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* /var/log/apt/*

ADD . /var/www/html
