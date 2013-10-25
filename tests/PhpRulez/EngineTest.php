<?php
/*
 * This file is part of PhpRulez.
 *
 * (c) 2013 Nicolò Martini
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpRulez\Test;

use PhpRulez\Engine;

/**
 * Unit tests for class FirstClass
 */
class EngineTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testMatch()
    {
        $engine = new Engine();

        $firstLetter = function ($string) { return $string[0]; };
        $lastLetter = function ($string) { return substr($string, -1); };

        $engine
            ->addRule($firstLetter, 'a', 'starts with a')
            ->addRule($firstLetter, 'x', 'starts with x')
            ->addRule($lastLetter, 'b', 'finishes with b')
            ->addRule($lastLetter, 'y', 'finishes with y')
        ;

        $this->assertEquals('starts with a', $engine->match('aaaaaab'));
        $this->assertEquals('starts with x', $engine->match('xaaaaay'));
        $this->assertEquals('finishes with b', $engine->match('caaaaab'));
        $this->assertEquals('finishes with y', $engine->match('caaaaay'));
    }
}