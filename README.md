# Universal Matcher
[![Build Status](https://travis-ci.org/nicmart/UniversalMatcher.png?branch=master)](https://travis-ci.org/nicmart/UniversalMatcher)
[![Coverage Status](https://coveralls.io/repos/nicmart/UniversalMatcher/badge.png?branch=master)](https://coveralls.io/r/nicmart/UniversalMatcher?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/nicmart/UniversalMatcher/badges/quality-score.png?s=48823d51d6b85ca07a7321415a2101b9cc071bb7)](https://scrutinizer-ci.com/g/nicmart/UniversalMatcher/)

`UniversalMatcher` is a library that simplifies and abstracts the construction of custom matchers.
A Matcher acts like a filter that transforms an arbitrary value to another, following the business rules you specify
in the matcher definition. The "match" is intended to be between the input value and the rule that is applied to that value.

For installing instructions (composer!) please go to the end of this README

## Changelog
 - 0.1.1 Added matchAll method

## Example
Instantiate the matcher, and define some maps:
```php
use UniversalMatcher\FluentFunction\FluentFunction;
use UniversalMatcher\MapMatcher;
$f = new FluentFunction;

$matcher = (new MapMatcher)
    ->defineMap('featured', $f->value('featured'))
    ->defineMap('type', $f->value('type'))
    ->defineMap('type-featured', function($v) { return [$v['type'], $v['value']]; }, 100)
;
```
The `FluentFunction` simplifies the construction of maps, but it is completely optional. The number in the 
third definition specifies a priority. Default is `0`, so the example above the last map has the highest priority. 

Then you define the rules. Each rule is attached to a previously defined map and to an expected map value,
and specifies a value that will be returned when the rule matches:
```php
$matcher
    ->rule('type', 'book', 'book.html')
    ->rule('type', 'dvd', 'dvd.html')
    ->rule('featured', true, 'featured.html')
    ->rule('type-featured', ['book', true], 'featured-book.html')
    ->setDefault('item.html')
;
```
The `setDefault` call defines the value taht will be returned when no rule matches.

Now you can use your matcher:
```php
// Returns 'book.html'
$matcher(['type' => 'book', 'featured' => false]);

// Returns 'featured-book.html'
$matcher(['type' => 'book', 'featured' => true]);

// Returns 'featured.html'
$matcher(['type' => 'dvd', 'featured' => true]);

// Returns 'dvd.html'
$matcher(['type' => 'dvd', 'featured' => false]);

// Returns 'item.html'
$matcher(['type' => 'cd', 'featured' => false]);

// Find all matching values with matchAll method, ordered by priority:
// This returns ['featured-book.html', 'featured.html', 'book.html']
$matcher->matchAll(['type' => 'book', 'featured' => true]);
```

## Documentation
### Summary
A matcher is defined by a set of maps and a set of rules. 

When you invoke the matcher,
the input value is transformed by the registered map with highest priority. If there is
a registered rule for that map that has the expected value (second argument of `rule` method)
equal to the transformed value, then the matcher returns the return value of that rule 
(third argument of the `rule` method).

If no rules match for that map, the matcher will pass to the next (in priority order) map, 
and so on until there is a rule match.

When the matcher has cycled throughout all the registered maps without finding a matching rule,
a default value is returned.

### Maps
You register a map with the `MapMatcher::defineMap()` method. The first argument is
the map name that the rules will use to refer to the map, and the second is the real map, that
can be any valid php callable:
```php
$matcher
    ->defineMap('foo', function($v) { return $v->foo; })
    ->defineMap('lowered', 'strtolower')
    ->defineMap('method', [$object, 'method'])
;
    
```
### Priority
`defineMap` accepts also a third optional argument to specify a priority. Default is `0`, and the rules
that corresponds to higher priority maps will win. If two maps have the same priority, the first defined wins.
```php
$matcher
    ->defineMap('bar', function($v) { return $v->bar; }, -100) //This will be the last checked
    ->defineMap('baz', function($v) { return $v->baz; }, 100)  //This will be the first
;
    
```
You can retrieve the priority of a registered map with the `MapMatcher::priority` method. So
 if you want, for example, to be sure to define a map with prioriy higher that the `baz` map,
 you can do
```php
$matcher
    ->defineMap('blah', 'strtoupper', $matcher->priority('baz') + 1)
;
    
```

### FluentFunction
With a `FluentFunction` you can define and compose more easily some very common callables:

```php
use UniversalMatcher\FluentFunction\FluentFunction;
$f = new FluentFunction;

// Returns a property of the input object
$h = $f->prop('foo');
$h($object); //Returns $object->foo;

// Returns the return value of a method of the input object
$h = $f->method('method');
$h($object); //Returns $object->method();
// ... with arguments too:
$h = $f->method('method', $arg1, $arg2, ...);
$h($object); //Returns $object->method($arg1, $arg2);

//Returns the value of an array or of an `ArrayAccess` instance:
$h = $f->value('key');
$h(['key' => 'value']); //Returns 'value'

//Regexpes
$h = $f->regexp('/^[0-9]+$/');
$h('abc0123'); // False
$h('123456')   // True

```
Concatenation is easy too:
```php
$h = $f->prop('foo')->method('method')->value('bar');
$h($object); //Returns $object->foo->method()['bar']
```

## Rules
A rule is composed of three arguments: the name of the map that will transform
the input value, the expected returned value, and the value to be returned on match.

The order of the rules, unlike the maps definitions, has no effect on matching.
```php
$matcher
    ->rule('foo', 'bar', '$object->foo is bar')
    ->rule('foo', 'baz', '$object->foo is baz')
    ->rule('lowered', 'string', 'strtolower($value) is "string"'
;
```
### Skip the map definition
If a map is intended to be used with only one rule, you can skip the definition of the map
and directly define the rule with the `callbackRule` method:
```php
$matcher->callbackRule(function($v) { /* Do something */ }, 'expected', 'returned value');
```
### Default return value
You can set the return value of the matcher when no rules match with the `setDefault` method.
Default is `null`.
```php
$matcher->setDefault('not-found!');
```


## Performance considerations

`MapMatcher` has been designed to minimize cycles between rules. Indeed, the cost of a match
is independent on the number of rules, but only on the number of registered maps (and of course
on the cost of each map).

So there should not be issues if the number of rules is high but the number of maps remains low.

Measuring the cost on php array accesses, we have, given the number of maps `M`, that
```
T(match) = O(M)
```
as you can see, the cost is linear on the number of maps.

## Where is it used
I use `UniversalMatcher` in the [compiler definitions](https://github.com/comperio/DomainSpecificQuery/blob/master/src/DSQ/Compiler/MatcherCompiler.php#L35) 
of the [DomainSpecificQuery](http://github.com/comperio/DomainSpecificQuery)
component. The Universal matcher allowed us to minimize rules checks while mantaining maximum
flexibility on the compiler definition.


## Install

The best way to install UniversalMatcher is [through composer](http://getcomposer.org).

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
