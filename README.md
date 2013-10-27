# Universal Matcher
[![Build Status](https://travis-ci.org/nicmart/UniversalMatcher.png?branch=master)](https://travis-ci.org/nicmart/UniversalMatcher)
[![Coverage Status](https://coveralls.io/repos/nicmart/UniversalMatcher/badge.png?branch=master)](https://coveralls.io/r/nicmart/UniversalMatcher?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/nicmart/UniversalMatcher/badges/quality-score.png?s=48823d51d6b85ca07a7321415a2101b9cc071bb7)](https://scrutinizer-ci.com/g/nicmart/UniversalMatcher/)

A rule based matcher engine for php.

## Install

The best way to install PhpRulez is [through composer](http://getcomposer.org).

Just create a composer.json file for your project:

```JSON
{
    "require": {
        "nicmart/universal-matcher": "dev-master"
    }
}
```

Then you can run these two commands to install it:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

or simply run `composer install` if you have have already [installed the composer globally](http://getcomposer.org/doc/00-intro.md#globally).

Then you can include the autoloader, and you will have access to the library classes:

```php
<?php
require 'vendor/autoload.php';
```
