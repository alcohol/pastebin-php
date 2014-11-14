#!/usr/bin/env bash

pacman --sync --noprogressbar --clean --clean --noconfirm
pacman --sync --noprogressbar --refresh --refresh --noconfirm
pacman --sync --noprogressbar --noconfirm --needed reflector

reflector --threads 2 \
    --country Netherlands \
    --latest 10 \
    --fastest 5 \
    --sort rate \
    --protocol http \
    --save /etc/pacman.d/mirrorlist

pacman --sync --sysupgrade --quiet --noconfirm

pacman-db-upgrade

pacman --sync --quiet --noconfirm --needed \
    unzip zip pacmatic ntp git vim htop

systemctl enable ntpd

timedatectl set-timezone Europe/Amsterdam
timedatectl set-ntp true
timedatectl set-local-rtc false
