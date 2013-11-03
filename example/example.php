<?php
/**
 * This file is part of UniversalMatcher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

include '../vendor/autoload.php';

use UniversalMatcher\FluentFunction\FluentFunction;
use UniversalMatcher\MapMatcher;
$f = new FluentFunction;

$matcher = (new MapMatcher)
    ->defineMap('featured', $f->value('featured'))
    ->defineMap('type', $f->value('type'))
    ->defineMap('type-featured', function($v) { return [$v['type'], $v['featured']]; }, 100)
;

$matcher
    ->rule('type', 'book', 'book.html')
    ->rule('type', 'dvd', 'dvd.html')
    ->rule('featured', true, 'featured.html')
    ->rule('type-featured', ['book', true], 'featured-book.html')
    ->setDefault('item.html')
;

var_dump($matcher(['type' => 'book', 'featured' => false]));
var_dump($matcher(['type' => 'book', 'featured' => true]));
var_dump($matcher(['type' => 'dvd', 'featured' => true]));
var_dump($matcher(['type' => 'dvd', 'featured' => false]));
var_dump($matcher(['type' => 'cd', 'featured' => false]));