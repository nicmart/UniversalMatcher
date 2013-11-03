<?php
/**
 * This file is part of UniversalMatcher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace UniversalMatcher\FluentFunction;

/**
 * Class FluentFunction
 * This is an immutable data structure that wraps callables and that offers
 * a fluent interface to define function chains
 *
 * @package UniversalMatcher\FluentFunction
 */
class FluentFunction
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * Provide a fluent way to create FluentFlunction
     * @param null $callable
     * @return static
     */
    public static function f($callable = null)
    {
        return new static($callable);
    }

    /**
     * @param callable $callable
     */
    public function __construct($callable = null)
    {
        $this->callable = $callable;
    }

    /**
     * Compose with a custom callable
     *
     * @param callable $callable
     * @return FluentFunction
     */
    public function func($callable)
    {
        if ($this->callable === null)
            return new static($callable);
        $that = $this;

        return new static(function() use ($callable, $that) {
              return call_user_func($callable, call_user_func_array($that, func_get_args()));
        });
    }

    /**
     * Add a property extractor to the function chain
     * @param string $propName
     * @return FluentFunction
     */
    public function prop($propName)
    {
        return $this->func(function($object) use ($propName) {
            return $object->$propName;
        });
    }

    /**
     * Add a property extractor to the function chain
     * @param string $methodName
     * @param mixed $arg1,... A variable length list of arguments
     * @return FluentFunction
     */
    public function method($methodName, $arg1/*, $arg2, $arg3, ...*/)
    {
        $args = array_slice(func_get_args(), 1);

        return $this->func(function($object) use ($methodName, $args) {
            return call_user_func_array(array($object, $methodName), $args);
        });
    }

    /**
     * @param mixed $key
     * @return FluentFunction
     */
    public function value($key)
    {
        return $this->func(function($array) use ($key) {
            return $array[$key];
        });
    }

    /**
     * @param string $regexp
     * @return FluentFunction
     */
    public function regexp($regexp)
    {
        return $this->func(function($string) use ($regexp) {
            return (bool) preg_match($regexp, $string);
        });
    }

    /**
     * @param $value
     * @return FluentFunction
     */
    public function lessThan($value)
    {
        return $this->func(function($previous) use ($value) {
            return $previous < $value;
        });
    }

    /**
     * @param $value
     * @return FluentFunction
     */
    public function lessOrEqualThan($value)
    {
        return $this->func(function($previous) use ($value) {
            return $previous <= $value;
        });
    }

    /**
     * @param $value
     * @return FluentFunction
     */
    public function greaterThan($value)
    {
        return $this->func(function($previous) use ($value) {
            return $previous > $value;
        });
    }

    /**
     * @param $value
     * @return FluentFunction
     */
    public function greaterOrEqualThan($value)
    {
        return $this->func(function($previous) use ($value) {
            return $previous >= $value;
        });
    }

    /**
     * @param $c
     * @return mixed
     */
    public function constant($c)
    {
        return $this->func(function() use ($c) {
            return $c;
        });
    }

    /**
     * @throws \DomainException
     * @return mixed
     */
    public function __invoke()
    {
        if (!is_callable($this->callable))
            throw new \DomainException('Not a valid callable');

        return call_user_func_array($this->callable, func_get_args());
    }
} 