#!/usr/bin/env bash

ln -sf /etc/php/php.ini /home/vagrant/php.ini
ln -sf /home/vagrant/nginx.conf /etc/nginx/nginx.conf

sed -i -r --follow-symlinks -e 's,^open_basedir =.*,open_basedir =,' \
    -e 's,^memory_limit = 128M,memory_limit = 512M,' \
    -e 's,(display_errors|display_startup_errors) = Off,\1 = On,' \
    -e 's,^;date.timezone =,date.timezone = Europe/Amsterdam,' \
    -e 's,^;zend_extension=opcache.so,zend_extension=opcache.so,' \
    -e 's,^;extension=(intl.so|mcrypt.so|phar.so|openssl.so|posix.so),extension=\1,' /home/vagrant/php.ini

sed -i 's,^;extension=apcu.so,extension=apcu.so,' /etc/php/conf.d/apcu.ini
sed -i 's,^;zend_extension=/usr/lib/php/modules/xdebug.so,zend_extension=/usr/lib/php/modules/xdebug.so,' /etc/php/conf.d/xdebug.ini
sed -i -r -e 's,^(user|group) = http,\1 = vagrant,' -e 's,^;error_log = log/php-fpm.log,error_log = syslog,' /etc/php/php-fpm.conf

systemctl enable nginx redis php-fpm redis
systemctl start nginx redis php-fpm redis
