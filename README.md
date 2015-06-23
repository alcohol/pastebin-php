# Alcohol\sf-minimal-demo

A demo that attempts to setup a minimalistic Symfony application.

Inspired by [this blogpost](http://www.whitewashing.de/2014/10/26/symfony_all_the_things_web.html) and also [sprunge.us](http://sprunge.us).

[![Build Status](https://img.shields.io/travis/alcohol/sf-minimal-demo/master.svg?style=flat-square)](https://travis-ci.org/alcohol/sf-minimal-demo)


## Dependencies (External)

* redis-server


## Installing

```
git clone https://github.com/alcohol/sf-minimal-demo.git
cd sf-minimal-demo
composer install
vim .env
```

Worth mentioning:
* It uses a modified directory structure. Make sure the **httpd** or **fcgi**
  user can write to `var/{cache,log}`. I recommend `chmod 2775 var/{cache,log}`.


## Configuring

Modify the `.env` file.

> See [external parameters](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html)
> regarding **SYMFONY__** prefixed environment variables.


## Nginx config

Adjust where applicable. If unsure, consult [Nginx documentation](http://nginx.org/en/docs/).

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

## Apache config

Install Nginx.


## Testing

```
vendor/bin/phpunit
```

The `integration` group is excluded by default, as it will boot the Application
Kernel and depends on a running Redis instance for most tests. To test the
functional group as well, run:

```
vendor/bin/phpunit -c phpunit.all.xml
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
