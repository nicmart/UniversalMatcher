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

        $this->assertEquals('baz', $f(['foo' => ['bar' => 'baz']]));
    }

    public function testRegexp()
    {
        $f = FluentFunction::f()->regexp('/^[0-9]xy[ab]+$/');

        $this->assertTrue($f('7xyaaababab'));
        $this->assertFalse($f('7xyaaabeabab'));
    }

    public function testLessThan()
    {
        $f = FluentFunction::f()->lessThan(12);

        $this->assertTrue($f(11));
        $this->assertFalse($f(12));
    }

    public function testLessOrEqualThan()
    {
        $f = FluentFunction::f()->lessOrEqualThan(12);

        $this->assertTrue($f(11));
        $this->assertTrue($f(12));
        $this->assertFalse($f(13));
    }

    public function testGreaterThan()
    {
        $f = FluentFunction::f()->greaterThan(12);

        $this->assertFalse($f(11));
        $this->assertFalse($f(12));
        $this->assertTrue($f(13));
    }

    public function testGreaterOrEqualThan()
    {
        $f = FluentFunction::f()->greaterOrEqualThan(12);

        $this->assertFalse($f(11));
        $this->assertTrue($f(12));
        $this->assertTrue($f(13));
    }

    public function testConstant()
    {
        $f = FluentFunction::f()->constant('aaa');

        $this->assertEquals('aaa', $f('something'));
    }
}

class Object
{
    public function foo($a, $b)
    {
        return "$a:$b";
    }
}
 