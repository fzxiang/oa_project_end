#-------------------------------------------------------------------------------------------------------------
# Copyright (c) Microsoft Corporation. All rights reserved.
# Licensed under the MIT License. See https://go.microsoft.com/fwlink/?linkid=2090316 for license information.
#-------------------------------------------------------------------------------------------------------------

FROM php:7.2-fpm-buster

ARG USERNAME=php
ARG USER_UID=1000
ARG USER_GID=$USER_UID

ENV TZ=Asia/Shanghai

USER root

RUN groupadd --gid $USER_GID $USERNAME \
    && useradd -r --uid $USER_UID --gid $USER_GID -m $USERNAME \
    #
    # Use ustc source insteads of debian official source
    && sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
    && sed -i 's|security.debian.org/debian-security|mirrors.ustc.edu.cn/debian-security|g' /etc/apt/sources.list \
    #
    # Configure apt and install packages
    && apt-get update \
    && apt-get -y install --no-install-recommends apt-utils dialog 2>&1 \
    #
    # install git iproute2, procps, lsb-release (useful for CLI installs)
    && apt-get -y install git openssh-client less iproute2 procps iproute2 lsb-release zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev libzip4 libzip-dev libssh2-1-dev \
    #
    # [Optional] Add sudo support. Omit if you don't need to install software after connecting.
    && apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME \
    #
    # Update pecl channel
    && pecl channel-update pecl.php.net \
    #
    # Install xdebug
    # && pecl install xdebug \
    # && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    # && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    # && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    #
    # Install redis extension
    && pecl install redis && docker-php-ext-enable redis \
    #
    # Install ssh2 extension
    && pecl install ssh2-1.3.1 && docker-php-ext-enable ssh2 \
    #
    # Install other extension
    && docker-php-ext-install bcmath pdo_mysql shmop sysvmsg sysvsem sysvshm sockets pcntl zip \
    && docker-php-source extract \
    && cd /usr/src/php/ext/gd \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable gd \
    #
    # Install composer
    && curl -o /usr/local/bin/composer https://mirrors.aliyun.com/composer/composer.phar \
    && chmod +x /usr/local/bin/composer \
    && runuser -l $USERNAME -c 'composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/' \
    #
    # Clean up
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

USER $USERNAME
