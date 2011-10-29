<?php

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
