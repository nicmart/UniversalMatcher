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


use UniversalMatcher\CallableValueMatcher;
use UniversalMatcher\Matcher;
use UniversalMatcher\PostMatcher;

class CallableValueMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var CallableValueMatcher
     */
    protected $callableValueMatcher;

    public function setUp()
    {
        $this->matcher = $this->getMockBuilder('UniversalMatcher\Matcher')->getMock();

        $this->matcher
            ->expects($this->any())
            ->method('noMatchValue')
            ->will($this->returnValue('none'))
        ;

        $matcher = $this->matcher;
        $matcher
            ->expects($this->any())
            ->method('match')
            ->will($this->returnCallback(function ($v) {
                if ($v == 'not callable') return 'scalar';
                return function ($value, Matcher $matcher) { return $value . ':' . substr($value, 0, 2); };
            }))
        ;

        $this->callableValueMatcher = new CallableValueMatcher($this->matcher);
    }

    public function testMatch()
    {
        $this->assertEquals('foo bar:fo', $this->callableValueMatcher->match('foo bar'));
        $this->assertEquals('123456:12', $this->callableValueMatcher->match('123456'));
    }

    public function testMatchWithNonCallableValue()
    {
        $this->assertEquals('scalar', $this->callableValueMatcher->match('not callable'));
    }
}
