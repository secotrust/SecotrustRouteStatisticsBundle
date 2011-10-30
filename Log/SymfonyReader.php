<?php

/*
 * This file is part of the SecotrustRouteStatisticsBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\RouteStatisticsBundle\Log;

class SymfonyReader extends Reader
{
    /**
     * @param string $line
     */
    public function readLine($line)
    {
        if (false !== $offset = strpos($line, 'request.INFO: Matched route')) {
            if (preg_match('/(?<=")[^"]+(?=")/', $line, $matches, null, $offset)) {
                @$this->stats[$matches[0]]++;
            }
        }
    }
}
