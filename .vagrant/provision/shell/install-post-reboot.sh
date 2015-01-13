#!/usr/bin/env bash

pacman --sync --noprogressbar --quiet --noconfirm --needed \
    nginx redis varnish php php-fpm
pacman --sync --noprogressbar --quiet --noconfirm --needed \
    php-mcrypt php-intl php-apcu xdebug
