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

use UniversalMatcher\Engine;
use UniversalMatcher\None;

/**
 * Unit tests for class FirstClass
 */
class EngineTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $engine = new Engine();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->matcher('first', $firstLetter)
            ->matcher('last', $lastLetter)
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

    public function testMatchWithNotFoundValue()
    {
        $engine = new Engine(new None);

        $engine
            ->rule('strtoupper', 'A', 'a')
        ;

        $this->assertInstanceOf('\UniversalMatcher\None', $engine->match('x'));
    }

    public function testCallbackRules()
    {
        $engine = new Engine();

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
        $engine = new Engine();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->matcher('first', $firstLetter)
            ->matcher('last', $lastLetter)
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
}