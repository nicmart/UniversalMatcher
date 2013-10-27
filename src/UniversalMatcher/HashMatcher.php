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
 * Class HashMatcher
 */
class HashMatcher implements Matcher
{
    /**
     * @var array[callable]
     */
    private $hashFucntions;

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
     * @param string $name
     * @param callable $hasher
     * @throws InvalidMatcherException
     * @return $this
     */
    public function hasher($name, $hasher)
    {
        if (!is_callable($hasher))
            throw new InvalidMatcherException('Hasher must be a callable');

        $this->hashFucntions[$name] = $hasher;

        if (!isset($this->rules[$name]))
            $this->rules[$name] = array();

        return $this;
    }

    /**
     * @param string|callable $hasher  The name of a registered hasher or a callable
     * @param mixed $expected   The expected matching value
     * @param mixed $value      The value that will be returned on match
     * @return $this            The current instance
     */
    public function rule($hasher, $expected, $value)
    {
        if (is_callable($hasher)) {
            if (is_string($hasher)) {
                if (!isset($this->hashFucntions[$hasher]))
                    return $this->callbackRule($hasher, $expected, $value);
            } else {
                return $this->callbackRule($hasher, $expected, $value);
            }
        }

        $this->rules[$hasher][$expected] = $value;

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
            ->hasher($key, $callback)
            ->rule($key, $expected, $value)
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function match($value)
    {
        foreach ($this->hashFucntions as $name => $hasher) {
            $matchingValue = call_user_func($hasher, $value);

            if (isset($this->rules[$name][$matchingValue]))
                return $this->rules[$name][$matchingValue];
        }

        return $this->noMatchValue;
    }

    /**
     * Render an HashMatcher a callable => Can be nested in other engines!
     * @param mixed $value
     * @return mixed
     */
    public function __invoke($value)
    {
        return $this->match($value);
    }

    /**
     * Generate a hasher key not already taken
     * @return int
     */
    private function getFreeKey()
    {
        $index = count($this->hashFucntions);

        while (isset($this->hashFucntions[$index]))
            $index++;

        return $index;
    }
}