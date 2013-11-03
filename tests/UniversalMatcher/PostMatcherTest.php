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


use UniversalMatcher\Matcher;
use UniversalMatcher\PostMatcher;

class PostMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var PostMatcher
     */
    protected $postMatcher;

    public function setUp()
    {
        $this->matcher = $this->getMockBuilder('UniversalMatcher\Matcher')->getMock();

        $this->matcher
            ->expects($this->any())
            ->method('getDefault')
            ->will($this->returnValue('none'))
        ;

        $this->matcher
            ->expects($this->any())
            ->method('match')
            ->will($this->returnCallback(function ($v) {
                if ($v == 'not here') return 'none';
                return substr($v, 0, 2);
            }))
        ;

        $this->postMatcher = $this->getMockBuilder('UniversalMatcher\PostMatcher')
            ->setConstructorArgs(array($this->matcher))
            ->setMethods(array('transform'))
            ->getMock()
        ;

        $this->postMatcher->expects($this->any())
            ->method('transform')
            ->will($this->returnCallback(function ($v, $original, Matcher $m) {
                return $original . ':' . strtoupper($v);
            }));
    }

    public function testNoMatchValue()
    {
        $this->assertEquals($this->matcher->getDefault(), $this->postMatcher->getDefault());
    }

    public function testMatch()
    {
        $this->assertEquals('foo bar:FO', $this->postMatcher->match('foo bar'));
        $this->assertEquals('baz bar bag:BA', $this->postMatcher->match('baz bar bag'));
    }

    public function testMatchWhenInnerMatcherDoesNotMatch()
    {
        $this->assertEquals('none', $this->postMatcher->match('not here'));
    }
}
 