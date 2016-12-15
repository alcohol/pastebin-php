# alcohol/paste.robbast.nl

A demo that attempts to setup a minimalistic Symfony application.

Inspired by [sprunge.us](http://sprunge.us).

A live deployment can be found at: [paste.robbast.nl](https://paste.robbast.nl)

[![Build Status](https://img.shields.io/travis/alcohol/paste.robbast.nl.svg)](https://travis-ci.org/alcohol/paste.robbast.nl)


## Dependencies (External)

* redis-server (production environment)


## Setup

``` shell
composer install
```


## Running

``` shell
bin/console server:run
```

> Note: do not forget to modify the `.env` file.


## Testing

``` shell
vendor/bin/phpunit
```


## Nginx config

> Make sure the **httpd** and/or **fcgi** user has read and write access on `var/`.

Adjust where applicable. If unsure, consult [NGINX documentation].

``` nginx
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


## Apache config

Install Nginx.


## cURL examples

``` shell
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

alcohol/sf-minimal-demo is licensed under the MIT license.


[external parameters]: http://symfony.com/doc/current/cookbook/configuration/external_parameters.html
[NGINX documentation]: http://nginx.org/en/docs/
