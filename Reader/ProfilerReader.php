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

use Symfony\Component\HttpKernel\Profiler\Profiler;

class ProfilerReader extends Reader
{
    protected $profiler;

    /**
     * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
     */
    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * @return array
     */
    public function getStatistics()
    {
        foreach ($this->profiler->find('', '', 10000) as $profile) {
            $profile = $this->profiler->loadProfile($profile['token']);
            if (null !== $route = $profile->getCollector('request')->getRoute()) {
                @$this->stats[$route]++;
            }
        }

        return parent::getStatistics();
    }
}
