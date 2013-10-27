<?php
/**
 * This file is part of UniversalMatcher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace UniversalMatcher\Test;

use UniversalMatcher\FluentFunction\FluentFunction;
use UniversalMatcher\MapMatcher;

class MapMatcherAndFluentFunctionTest extends \PHPUnit_Framework_TestCase
{
    public function testMatcherWithFluentFunctionBuilder()
    {
        $matcher = new MapMatcher;
        $f = new FluentFunction;

        $matcher
            ->defineMap('foo', $f->value('foo'))
            ->defineMap('barbaz', $f->value('bar')->value('baz'))
            ->rule('foo', 'foo1', 'aaa')
            ->rule('foo', 'foo2', 'bbb')
            ->rule('barbaz', 'baz1', 'ccc')
        ;

        $this->assertEquals(
            'aaa',
            $matcher(array('foo' => 'foo1', 'bar' => array('baz' => 'baz1')))
        );
        $this->assertEquals(
            'bbb',
            $matcher(array('foo' => 'foo2', 'bar' => array('baz' => 'baz1')))
        );
        $this->assertEquals(
            'ccc',
            $matcher(array('foo' => 'foo3', 'bar' => array('baz' => 'baz1')))
        );
    }
}
 