# WyriHaximus/PhergieUrl

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for Display URL information about links.

[![Build Status](https://secure.travis-ci.org/WyriHaximus/PhergieUrl.png?branch=master)](http://travis-ci.org/WyriHaximus/PhergieUrl)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "wyrihaximus/phergie-url": "dev-master"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
new \WyriHaximus\Phergie\Plugin\Url\Plugin(array(



))
```

## Events

This plugin emails the following events

[*] `url.host.HOSTNAME` For example `url.host.twitter.com` (`www.` is stripped from the hostname).
[*] `url.host.all` For all hostnames.

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
cd tests
../vendor/bin/phpunit
```

## License

Released under the MIT License. See `LICENSE`.
