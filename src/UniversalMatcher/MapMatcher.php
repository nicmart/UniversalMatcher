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
     * @var array[PrioritizedMap]
     */
    private $maps;

    private $areMapsSorted = true;

    /**
     * @var array[array]
     */
    private $rules;

    /**
     * @var mixed
     */
    private $default = null;

    /**
     * @param null $defaultValue
     */
    public function __construct($defaultValue = null)
    {
        $this->default = $defaultValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setDefault($value)
    {
        $this->default = $value;

        return $this;
    }

    /**
     * Register a map
     *
     * @param string $name
     * @param callable $map
     * @param int $priority
     * @throws InvalidMatcherException
     * @return $this
     */
    public function defineMap($name, $map, $priority = 0)
    {
        if (!is_callable($map))
            throw new InvalidMatcherException('Hasher must be a callable');

        $this->areMapsSorted = false;
        $this->maps[$name] = new PrioritizedMap($map, $priority, count($this->maps));

        if (!isset($this->rules[$name]))
            $this->rules[$name] = array();

        return $this;
    }

    /**
     * @param $mapName
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function priority($mapName)
    {
        if (isset($this->maps[$mapName]))
            return $this->maps[$mapName]->priority;

        throw new \OutOfBoundsException("There is no map registered with the name \"$mapName\"");
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
     * @param int $priority
     *
     * @return $this
     */
    public function callbackRule($callback, $expected, $value, $priority = 0)
    {
        $key = is_string($callback) ? $callback : $this->getFreeKey();

        $this
            ->defineMap($key, $callback, $priority)
            ->rule($key, $expected, $value)
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function match($value)
    {
        // Sort maps by descending priority if necessary
        if (!$this->areMapsSorted) {
            $this->sortMaps();
            $this->areMapsSorted = true;
        }

        foreach ($this->maps as $name => $prioritizedMap) {
            $map = $prioritizedMap->map;
            $matchingValue = $this->serializeExpected(call_user_func($map, $value));

            if (isset($this->rules[$name][$matchingValue]))
                return $this->rules[$name][$matchingValue];
        }

        return $this->getDefault();
    }

    /**
     * @param string $mapName
     * @param mixed $matchingValue
     * @return mixed
     */
    public function matchByMapValue($mapName, $matchingValue)
    {
        if (isset($this->rules[$mapName][$matchingValue]))
            return $this->rules[$mapName][$matchingValue];

        return $this->getDefault();
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

    /**
     * Sort maps by priority. On equal prioriy, first inserted wins
     */
    private function sortMaps()
    {
        uasort($this->maps, function(PrioritizedMap $m1, PrioritizedMap $m2) {
            if ($p = $m2->priority - $m1->priority)
                return $p;
            return $m1->priority2 - $m2->priority2;
        });
    }
}