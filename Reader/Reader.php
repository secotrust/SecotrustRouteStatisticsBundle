<?php

/*
 * This file is part of the SecotrustRouteStatisticsBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\RouteStatisticsBundle\Reader;

abstract class Reader
{
    protected $stats = array();

    /**
     * @return array
     */
    public function getStatistics()
    {
        return $this->stats;
    }
}
