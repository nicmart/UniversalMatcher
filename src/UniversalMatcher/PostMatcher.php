<?php
/**
 * This file is part of UniversalMatcher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace UniversalMatcher;

/**
 * Class PostMatcher
 * A post-filter proxy matcher. Wraps another mathcer and take an action
 * on the matched result
 *
 * @package UniversalMatcher
 */
abstract class PostMatcher implements Matcher
{
    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @param Matcher $matcher
     */
    public function __construct(Matcher $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        return $this->matcher->getDefault();
    }

    /**
     * {@inheritdoc}
     */
    public function match($value)
    {
        $match = $this->matcher->match($value);
        if ($match === $this->getDefault())
            return $match;

        return $this->transform($this->matcher->match($value), $value, $this->matcher);
    }

    /**
     * @param mixed $matchResult    The value returned my match method of the underlyining Matcher
     * @param mixed $matchingValue  The input value of the match method
     * @param Matcher $matcher      The underlinying matcher
     * @return mixed
     */
    abstract public function transform($matchResult, $matchingValue, Matcher $matcher);
} 