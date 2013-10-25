<?php
/*
 * This file is part of PhpRulez.
 *
 * (c) 2013 Nicolò Martini
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpRulez;

/**
 * Class FirstClass
 */
class Engine
{
    /**
     * @var \SplObjectStorage
     */
    private $rules;

    /**
     * @var \SplObjectStorage[callable]
     */
    private $matchingFunctions;

    /**
     * @var null
     */
    private $noMatchValue = null;

    /**
     * @param null $noMatchValue
     */
    public function __construct($noMatchValue = null)
    {
        $this->noMatchValue = $noMatchValue;
        $this->rules = new \SplObjectStorage;
        $this->matchingFunctions = new \SplObjectStorage();
    }

    /**
     * @param mixed $matchingFunction       A closure or an invokable object
     * @param mixed $expected   The expected $rule returned value
     * @param mixed $value      The value associated with this rule
     * @return $this            The current instance
     * @throws InvalidRuleException
     */
    public function addRule($matchingFunction, $expected, $value)
    {
        if (!is_object($matchingFunction) || !method_exists($matchingFunction, '__invoke'))
            throw new InvalidRuleException('Rules must be Closures or invocable objects');

        if (!$this->matchingFunctions->contains($matchingFunction)) {
            $this->matchingFunctions->attach($matchingFunction);
            $this->rules->attach($matchingFunction, array());
        }

        $expectations = $this->rules[$matchingFunction];
        $expectations[$expected] = $value;
        $this->rules->attach($matchingFunction, $expectations);

        return $this;
    }

    /**
     * Find a
     * @param mixed $value
     * @return null
     */
    public function match($value)
    {
        foreach ($this->matchingFunctions as $matchingFunction) {
            $matchingValue = $matchingFunction($value);
            $map = $this->rules[$matchingFunction];
            if (isset($map[$matchingValue]))
                return $map[$matchingValue];
        }

        return $this->noMatchValue;
    }
}