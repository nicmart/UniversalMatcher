# Universal Matcher
[![Build Status](https://travis-ci.org/nicmart/UniversalMatcher.png?branch=master)](https://travis-ci.org/nicmart/UniversalMatcher)
[![Coverage Status](https://coveralls.io/repos/nicmart/UniversalMatcher/badge.png?branch=master)](https://coveralls.io/r/nicmart/UniversalMatcher?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/nicmart/UniversalMatcher/badges/quality-score.png?s=48823d51d6b85ca07a7321415a2101b9cc071bb7)](https://scrutinizer-ci.com/g/nicmart/UniversalMatcher/)

`UniversalMatcher` is a library that simplifies and abstracts the construction of custom matchers.
A Matcher acts like a filter that transforms an arbitrary value to another, following the business rules you specify
in the matcher definition. The "match" is intended to be between the input value and the rule that is applied to that value.

For installing instructions (composer!) please go to the end of this README

## Example
Suppose you have a `Person` class that contains some basic datas about people:
```php
class Person
{
    public $name, $birthdate, $gender;
    
    public function __construct($name, $birthdate, $gender) { ... }
    
    public function getAge() { ... }
}
```
You want to partition Person objects following these criteria:
 - If age is less or equal than 21 returns "girl" or "boy", depending on the gender.
 - If age greather than 21, returns "man" or "woman"
 - If name is "Gabba" returns "Gabba Ehi!", in each case.
 - If she's not Gabba and has 100 years, returns "Centenary!"

```php
use UniversalMatcher\FluentFunction;
use UniversalMatcher\MapMatcher;

$f = new FluentFunction;
$matcher = new MapMatcher;

$matcher
    ->defineMap('name', $f->prop('name'))
    ->defineMap('age', $f->method('getAge'))
    ->defineMap('age-gender', function(Person $p) { 
        return ($p->getAge() <= 21 ? 'young' : 'grown-up') . ':' . $p->gender]; })
    ->defineMap('gender', $f->prop('gender'))
    
    ->rule('name', 'Gabba', 'Gabba EHI!')
    ->rule('age-gender', 'young:female', 'A girl!')
    ->rule('age-gender', 'young:male', 'A boy!')
    ->rule('age-gender', 'grown-up:female', 'A woman')
    ->rule('age-gender', 'grown-up:male', 'A man')
;
```
Then we can resolve values using `$matcher` as a callable:
```php
//Prints "A man"
echo $matcher(new Person('Nicolò', '1983-03-20', 'male'));

//Prints "Gabba EHI!"
echo $matcher(new Person('Gabba', '1983-09-06', 'female'));

//Prints "A girl!"
echo $matcher(new Person('Emma', '1994-01-01', 'female'));

```


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
