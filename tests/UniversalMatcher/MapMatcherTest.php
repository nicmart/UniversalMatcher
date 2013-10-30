<?php
/*
 * This file is part of PhpRulez.
 *
 * (c) 2013 Nicolò Martini
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace UniversalMatcher\Test;

use UniversalMatcher\MapMatcher;
use UniversalMatcher\None;

/**
 * Unit tests for class MapMatcher
 */
class MapMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $engine = new MapMatcher();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->defineMap('first', $firstLetter)
            ->defineMap('last', $lastLetter)
            ->rule('first', 'a', 'starts with a')
            ->rule('first', 'x', 'starts with x')
            ->rule('last', 'b', 'finishes with b')
            ->rule('last', 'y', 'finishes with y')
        ;

        $this->assertEquals('starts with a', $engine->match('aaaaaab'));
        $this->assertEquals('starts with x', $engine->match('xaaaaay'));
        $this->assertEquals('finishes with b', $engine->match('caaaaab'));
        $this->assertEquals('finishes with y', $engine->match('caaaaay'));
    }

    public function testMatchWithPriorities()
    {
        $engine = new MapMatcher();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->defineMap('first', $firstLetter, 10)
            ->defineMap('last', $lastLetter, 100)
            ->rule('first', 'a', 'starts with a')
            ->rule('first', 'x', 'starts with x')
            ->rule('last', 'b', 'finishes with b')
            ->rule('last', 'y', 'finishes with y')
        ;

        $this->assertEquals('finishes with b', $engine->match('aaaaaab'));
        $this->assertEquals('finishes with y', $engine->match('xaaaaay'));
        $this->assertEquals('starts with a', $engine->match('aaaaaabc'));
        $this->assertEquals('starts with x', $engine->match('xasdasd'));
    }

    public function testMatchWithNotFoundValue()
    {
        $engine = new MapMatcher(new None);

        $engine
            ->rule('strtoupper', 'A', 'a')
        ;

        $this->assertInstanceOf('\UniversalMatcher\None', $engine->match('x'));
    }

    public function testMatchByMapValue()
    {
        $engine = new MapMatcher();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->defineMap('first', $firstLetter)
            ->defineMap('last', $lastLetter)
            ->rule('first', 'a', 'starts with a')
            ->rule('first', 'x', 'starts with x')
            ->rule('last', 'b', 'finishes with b')
            ->rule('last', 'y', 'finishes with y')
        ;

        $this->assertEquals('starts with a', $engine->matchByMapValue('first', 'a'));
        $this->assertEquals('starts with x', $engine->matchByMapValue('first', 'x'));
        $this->assertEquals('finishes with b', $engine->matchByMapValue('last', 'b'));
        $this->assertEquals('finishes with y', $engine->matchByMapValue('last', 'y'));
        $this->assertEquals($engine->noMatchValue(), $engine->matchByMapValue('last', 'xxx'));
        $this->assertEquals($engine->noMatchValue(), $engine->matchByMapValue('xxx', 'xxx'));
    }

    public function testCallbackRules()
    {
        $engine = new MapMatcher();

        $engine
            ->callbackRule('strtolower', 'aaa', 'first')
            ->callbackRule('strtolower', 'bbb', 'second')
            ->callbackRule('strtoupper', 'AA', 'third')
            ->callbackRule('strtoupper', 'BB', 'fourth')
        ;

        $this->assertEquals('first', $engine->match('AaA'));
        $this->assertEquals('second', $engine->match('bBb'));
        $this->assertEquals('third', $engine->match('Aa'));
        $this->assertEquals('fourth', $engine->match('bB'));
    }

    public function testMatchWithCallableRules()
    {
        $engine = new MapMatcher();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->defineMap('first', $firstLetter)
            ->defineMap('last', $lastLetter)
            ->rule('first', 'a', 'starts with a')
            ->rule('first', 'x', 'starts with x')
            ->rule('last', 'b', 'finishes with b')
            ->rule('last', 'y', 'finishes with y')
            ->rule(function($s) { return $s[1]; }, 'w', 'second is w')
        ;

        $this->assertEquals('starts with a', $engine->match('awaaaab'));
        $this->assertEquals('starts with x', $engine->match('xwaaaay'));
        $this->assertEquals('finishes with b', $engine->match('cwaaaab'));
        $this->assertEquals('finishes with y', $engine->match('caaaaay'));
        $this->assertEquals('second is w', $engine->match('jwgsdjhagsd'));
    }

    public function testMatcherWithNotScalarExpectation()
    {
        $matcher = new MapMatcher;

        $matcher
            ->defineMap('test', function() { return array('a', 'b'); })
            ->rule('test', array('a', 'b'), 'Bingo!')
        ;

        $this->assertEquals('Bingo!', $matcher('any value'));
    }

    public function testPriority()
    {
        $matcher = new MapMatcher;

        $matcher
            ->defineMap('a', function(){})
            ->defineMap('b', function(){}, 100)
            ->defineMap('c', function(){}, -100)
        ;

        $this->assertEquals(0, $matcher->priority('a'));
        $this->assertEquals(100, $matcher->priority('b'));
        $this->assertEquals(-100, $matcher->priority('c'));
    }
}