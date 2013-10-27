<?php
/**
 * This file is part of UniversalMatcher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace UniversalMatcher\Test\FluentFunction;

use UniversalMatcher\FluentFunction\FluentFunction;

class FluentFunctionTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $f = new FluentFunction('strtoupper');

        $this->assertEquals('ABCDEF', $f('aBcDEf'));

        $g = new FluentFunction;
        $this->setExpectedException('DomainException');
        $g('test');
    }

    public function testFunc()
    {
        $f = new FluentFunction;
        $g = $f->func(function ($s) { return substr($s, 0, 2); });

        $this->assertEquals('AB', $g('ABCDE'));

        $h = $g->func('strtolower');
        $this->assertEquals('ab', $h('ABCDE'));
    }

    public function testMethod()
    {
        $f = FluentFunction::f()->method('foo', 'bar', 'baz');
        $f(new Object());

        $this->assertEquals('bar:baz', $f(new Object()));
    }

    public function testValue()
    {
        $f = FluentFunction::f()->value('foo')->value('bar');

        $this->assertEquals('baz', $f(array('foo' => array('bar' => 'baz'))));
    }
}

class Object
{
    public function foo($a, $b)
    {
        return "$a:$b";
    }
}
 