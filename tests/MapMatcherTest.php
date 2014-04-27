<?php
/*
 * This file is part of UniversalMatcher.
 *
 * (c) 2014 Nicolò Martini
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

    public function testHierarchicalMatch()
    {
        $engine = new MapMatcher();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };
        $secondLetter = function ($string) { return $string[2]; };

        $linked = $engine->linkedMatcher();
        $engine
            ->defineMap('first', $firstLetter)
            ->defineMap('last', $lastLetter)
            ->defineMap('second', $secondLetter)
            ->rule('first', 'a', $linked
                ->rule('last', 'a', $linked->linkedMatcher()
                    ->setDefault('starts and finishes with a')
                    ->rule('second', 'a', 'starts with aa and finishes with a')
                    ->rule('second', 'b', 'starts with ab and finishes with a')
                )
                ->rule('last', 'b', 'starts with a and finishes with b')
                ->setDefault('starts with a')
            )
            ->rule('first', 'b', 'starts with b')
        ;

        $this->assertEquals('starts with a and finishes with b', $engine->match('aaaaaab'));
        $this->assertEquals('starts with aa and finishes with a', $engine->match('aaaaaabaaa'));
        $this->assertEquals('starts with aa and finishes with a', $engine->match('abaaaabaaa'));
        $this->assertEquals('starts with a', $engine->match('abaaaabaaax'));
        $this->assertEquals('starts with b', $engine->match('bbaaaabaaax'));
    }

    public function testMatchWithCallableReturnValue()
    {
        $engine = new MapMatcher();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->defineMap('first', $firstLetter)
            ->defineMap('last', $lastLetter)
            ->rule('first', 'a', function($v) { return "a:$v"; })
            ->rule('first', 'x', function($v) { return "x:$v"; })
        ;

        $this->assertEquals('a:aaaaaab', $engine->match('aaaaaab'));
        $this->assertEquals('x:xaaaaay', $engine->match('xaaaaay'));
    }

    public function testMatchAll()
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

        $this->assertEquals(['starts with a', 'finishes with b'], $engine->matchAll('aaaaaab'));
        $this->assertEquals(['starts with x', 'finishes with y'], $engine->matchAll('xaaaaay'));
        $this->assertEquals(['finishes with b'], $engine->matchAll('caaaaab'));
        $this->assertEquals(['finishes with y'], $engine->matchAll('caaaaay'));

        $this->assertEquals([], $engine->matchAll('zzz'));
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
        $this->assertEquals(call_user_func($engine->getDefault()), $engine->matchByMapValue('last', 'xxx'));
        $this->assertEquals(call_user_func($engine->getDefault()), $engine->matchByMapValue('xxx', 'xxx'));
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
            ->defineMap('test', function() { return ['a', 'b']; })
            ->rule('test', ['a', 'b'], 'Bingo!')
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

    public function testLinkedMatcher()
    {
        $matcher = new MapMatcher($n = new None);

        $matcher
            ->defineMap('a', function(){ return '1'; })
            ->defineMap('b', function(){ return '2'; })
            ->rule('a', '1', 'v1')
            ->rule('b', '2', 'v2')
        ;

        $linked = $matcher->linkedMatcher()
            ->defineMap('c', function(){ return '3'; })
            ->rule('a', '3', 'v3');

        $this->assertSame($matcher->getMap('a'), $matcher->getMap('a'));
        $this->assertSame($matcher->getMap('b'), $matcher->getMap('b'));
        $this->assertSame($matcher->getMap('c'), $matcher->getMap('c'));

        $this->assertSame($n, $matcher->matchByMapValue('a', '3'));
        $this->assertSame($n, $linked->matchByMapValue('a', '1'));

        $linked->setDefault("Different default");

        $this->assertSame($n, call_user_func($matcher->getDefault()));
    }
}