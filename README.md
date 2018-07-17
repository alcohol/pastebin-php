# alcohol/paste.robbast.nl

My personal pastebin application. Initially created as a minimalistic approach to setting up a Symfony application. Over
time it has evolved and grown, with each new Symfony release.

Inspired by [sprunge.us](http://sprunge.us).

A live deployment can be found at: [paste.robbast.nl](https://paste.robbast.nl)

[![Build Status](https://travis-ci.org/alcohol/paste.robbast.nl.svg?branch=master)](https://travis-ci.org/alcohol/paste.robbast.nl)

## Dependencies (external)

* redis-server (production environment)


## Testing

``` shell
vendor/bin/phpunit
```


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

alcohol/paste.robbast.nl is licensed under the MIT license.
