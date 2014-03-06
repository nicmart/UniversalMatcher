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
 * Class CallableValueMatcher
 * This PostMatcher resolve match returns values if they are callables,
 * passing to them the matching value and the original matcher.
 *
 * @package UniversalMatcher
 */
class CallableValueMatcher extends PostMatcher
{
    /**
     * {@inheritdoc}
     */
    public function transform($matchResult, $matchingValue, Matcher $matcher)
    {
        if (!is_callable($matchResult))
            return $matchResult;

        return call_user_func($matchResult, $matchingValue, $matcher);
    }
} 