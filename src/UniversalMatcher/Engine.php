<?php
/*
 * This file is part of PhpRulez.
 *
 * (c) 2013 Nicolò Martini
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UniversalMatcher;

/**
 * Class FirstClass
 */
class Engine
{
    /**
     * @var array[callable]
     */
    private $matchers;

    /**
     * @var array[array]
     */
    private $rules;

    /**
     * @var mixed
     */
    private $noMatchValue = null;

    /**
     * @param null $noMatchValue
     */
    public function __construct($noMatchValue = null)
    {
        $this->noMatchValue = $noMatchValue;
    }

    /**
     * @param string $name
     * @param callable $matcher
     * @throws InvalidMatcherException
     * @return $this
     */
    public function matcher($name, $matcher)
    {
        if (!is_callable($matcher))
            throw new InvalidMatcherException('Matcher must be a callable');

        $this->matchers[$name] = $matcher;

        if (!isset($this->rules[$name]))
            $this->rules[$name] = array();

        return $this;
    }

    /**
     * @param string|callable $matcher  The name of a registered matcher or a callable
     * @param mixed $expected   The expected matching value
     * @param mixed $value      The value that will be returned on match
     * @return $this            The current instance
     * @throws InvalidRuleException
     */
    public function rule($matcher, $expected, $value)
    {
        if (is_callable($matcher)) {
            if (is_string($matcher)) {
                if (!isset($this->matchers[$matcher]))
                    return $this->callbackRule($matcher, $expected, $value);
            } else {
                return $this->callbackRule($matcher, $expected, $value);
            }
        }

        $this->rules[$matcher][$expected] = $value;

        return $this;
    }

    /**
     * Create a rule providing directly a callable
     *
     * @param callable $callback
     * @param mixed $expected
     * @param mixed $value
     * @return $this
     */
    public function callbackRule($callback, $expected, $value)
    {
        $key = is_string($callback) ? $callback : $this->getFreeKey();

        $this
            ->matcher($key, $callback)
            ->rule($key, $expected, $value)
        ;

        return $this;
    }

    /**
     * Find a
     * @param mixed $value
     * @return mixed
     */
    public function match($value)
    {
        foreach ($this->matchers as $name => $matcher) {
            $matchingValue = call_user_func($matcher, $value);

            if (isset($this->rules[$name][$matchingValue]))
                return $this->rules[$name][$matchingValue];
        }

        return $this->noMatchValue;
    }

    /**
     * Render an Engine a callable => Can be nested in other engines!
     * @param mixed $value
     * @return mixed
     */
    public function __invoke($value)
    {
        return $this->match($value);
    }

    /**
     * Generate a matcher key not already taken
     * @return int
     */
    private function getFreeKey()
    {
        $index = count($this->matchers);

        while (isset($this->matchers[$index]))
            $index++;

        return $index;
    }
}