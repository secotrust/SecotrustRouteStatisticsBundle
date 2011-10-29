<?php

namespace Secotrust\Bundle\RouteStatisticsBundle\Log;

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

    /**
     * @abstract
     * @param string $line
     */
    abstract public function readLine($line);
}
