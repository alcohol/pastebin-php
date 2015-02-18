# Alcohol\sf-minimal-demo

A demo that attempts to setup a minimalistic Symfony application.

Inspired by [this blogpost](http://www.whitewashing.de/2014/10/26/symfony_all_the_things_web.html) and also [sprunge.us](http://sprunge.us).

[![Build Status](https://img.shields.io/travis/alcohol/sf-minimal-demo/master.svg?style=flat-square)](https://travis-ci.org/alcohol/sf-minimal-demo)

## Installing

``` Shell
git clone https://github.com/alcohol/sf-minimal-demo.git
cd sf-minimal-demo
composer install
vim .env
```

Worth mentioning:
* It uses a directory structure similar to the one described
  [here](http://stackoverflow.com/questions/23993295/what-is-the-new-symfony-3-directory-structure/23994473#23994473),
  so make sure the **httpd** or **fcgi** user can write to `var/`.

## Configuring

Modify the `.env` file.

> See [external
> parameters](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html)
> regarding **SYMFONY__** prefixed environment variables.

## Nginx config

``` Nginx
upstream php-fpm {
    server unix:/run/php-fpm/php-fpm.sock;
}
server {
    server_name pastebin.tld;
    access_log /var/log/nginx/pastebin.tld.log;
    error_log /var/log/nginx/pastebin.tld.err;
    root /srv/http/pastebin.tld/web;
    location / {
        index index.php;
        try_files $uri $uri/ /index.php$is_args$args;
    }
    location = /robots.txt  { access_log off; log_not_found off; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location ~* \.php$ {
        try_files $uri =404;
        include fastcgi.conf;
        fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_pass php-fpm;
    }
    location ~* \.(?:jpe?g|gif|png|css|js|ico|xml)$ {
        expires 1h;
        add_header Cache-Control "public";
    }
}
```

## cURL examples

``` Shell
$ curl -X POST --data-binary 'Lorem ipsum' http://pastebin.tld
# 201 Created
# Location: /54ae
# X-Paste-Token: 99d6a7cb2f
http://pastebin.tld/54ae

$ curl http://pastebin.tld/54ae
# 200 OK
Lorem ipsum

$ curl -H 'X-Paste-Token: 99d6a7cb2f' -X PUT --data-binary 'Lipsum lorem' \
    http://pastebin.tld/54ae
# 204 No Content

$ curl http://pastebin.tld/54ae
# 200 OK
Lipsum lorem

$ curl -H 'X-Paste-Token: 99d6a7cb2f' -X DELETE \
    http://pastebin.tld/54ae
# 204 No Content
```

## Contributing

Feel free to submit a pull request or create an issue.

## License

Alcohol\sf-minimal-demo is licensed under the MIT license.
