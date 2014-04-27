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


interface Matcher
{
    /**
     * The value returned when there is no match
     *
     * @return callable
     */
    public function getDefault();

    /**
     * Match a value and return the associated value
     *
     * @param mixed $value
     * @return mixed
     */
    public function match($value);

    /**
     * Find all matches for a given value (not only the first one)
     *
     * @param mixed $value
     * @return array
     */
    public function matchAll($value);
} 