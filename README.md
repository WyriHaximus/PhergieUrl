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
return array(

    'plugins' => array(

        // dependencies
        new \WyriHaximus\Phergie\Plugin\Dns\Plugin, // Handles DNS lookups for the HTTP plugin
        new \WyriHaximus\Phergie\Plugin\Http\Plugin, // Handles the HTTP requests for this plugin

        // configuration
        new \WyriHaximus\Phergie\Plugin\Url\Plugin(array(
            // All configuration is optional

            'handler' => new \WyriHaximus\Phergie\Plugin\Url\DefaultUrlHandler(), // URL handler that creates a formatted message based on the URL

            // or

            'shortingTimeout' => 15 // If after this amount of seconds no url shortner has come up with a short URL the normal URL will be used. (Not in effect when there are no shortners listening.)

        )),

    )
);
```

## Events

This plugin emits the following generic, do what ever you want with it, events.

* `url.host.HOSTNAME` For example `url.host.twitter.com` (`www.` is stripped from the hostname).
* `url.host.all` For all hostnames.

This plugins also emits two events for url shortning. Only called when there are listeners registered. Each event emit is passed a `UrlShortningEvent`, if a shortner resolved short url it calls the `resolve` method on the promise.

* `url.shorting.HOSTNAME` For example `url.host.twitter.com` (`www.` is stripped from the hostname).
* `url.shorting.all` For all hostnames.

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the MIT License. See `LICENSE`.
