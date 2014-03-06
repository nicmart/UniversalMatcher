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


class PrioritizedMap
{
    /**
     * @var callable
     */
    public $map;

    /**
     * @var int
     */
    public $priority;

    /**
     * Used to mantain maps order on equal priorities
     * @var int
     */
    public $priority2;

    /**
     * @param $map
     * @param $priority
     * @param $priority2
     */
    public function __construct($map, $priority, $priority2)
    {
        $this->map = $map;
        $this->priority = $priority;
        $this->priority2 = $priority2;
    }
} 