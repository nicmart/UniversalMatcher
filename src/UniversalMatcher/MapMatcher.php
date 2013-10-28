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
 * Class MapMatcher
 */
class MapMatcher implements Matcher
{
    /**
     * @var array[callable]
     */
    private $maps;

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
     * {@inheritdoc}
     */
    public function noMatchValue()
    {
        return $this->noMatchValue;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setNoMatchValue($value)
    {
        $this->noMatchValue = $value;

        return $this;
    }

    /**
     * Register a map
     *
     * @param string $name
     * @param callable $map
     * @throws InvalidMatcherException
     * @return $this
     */
    public function defineMap($name, $map)
    {
        if (!is_callable($map))
            throw new InvalidMatcherException('Hasher must be a callable');

        $this->maps[$name] = $map;

        if (!isset($this->rules[$name]))
            $this->rules[$name] = array();

        return $this;
    }

    /**
     * @param string|callable $map  The name of a registered map or a callable
     * @param mixed $expected   The expected matching value
     * @param mixed $value      The value that will be returned on match
     * @return $this            The current instance
     */
    public function rule($map, $expected, $value)
    {
        if (is_callable($map)) {
            if (is_string($map)) {
                if (!isset($this->maps[$map]))
                    return $this->callbackRule($map, $expected, $value);
            } else {
                return $this->callbackRule($map, $expected, $value);
            }
        }

        $this->rules[$map][$this->serializeExpected($expected)] = $value;

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
            ->defineMap($key, $callback)
            ->rule($key, $expected, $value)
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function match($value)
    {
        foreach ($this->maps as $name => $hasher) {
            $matchingValue = $this->serializeExpected(call_user_func($hasher, $value));

            if (isset($this->rules[$name][$matchingValue]))
                return $this->rules[$name][$matchingValue];
        }

        return $this->noMatchValue;
    }

    /**
     * Render an MapMatcher a callable => Can be nested in other engines!
     * @param mixed $value
     * @return mixed
     */
    public function __invoke($value)
    {
        return $this->match($value);
    }

    /**
     * Generate a defineMap key not already taken
     * @return int
     */
    private function getFreeKey()
    {
        $index = count($this->maps);

        while (isset($this->maps[$index]))
            $index++;

        return $index;
    }

    /**
     * @param $value
     * @return string
     */
    private function serializeExpected($value)
    {
        if (is_scalar($value))
            return $value;

        return serialize($value);
    }
}