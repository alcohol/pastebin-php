# alcohol/pastebin-php

I mostly use this demo application to keep myself up to date with the various changes introduced by new (major) Symfony releases.

[![Build Status](https://travis-ci.com/alcohol/pastebin-php.svg?branch=master)](https://travis-ci.com/alcohol/pastebin-php)
[![codecov](https://codecov.io/gh/alcohol/pastebin-php/branch/master/graph/badge.svg)](https://codecov.io/gh/alcohol/pastebin-php)


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

alcohol/pastebin-php is licensed under the MIT license.
