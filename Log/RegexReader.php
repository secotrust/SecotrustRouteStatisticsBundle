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

use Symfony\Component\Routing\Router;

class RegexReader extends PathInfoReader
{
    protected $regex;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param string $prefix
     * @param string $regex
     */
    public function __construct(Router $router, $prefix, $regex)
    {
        parent::__construct($router, $prefix);
        $this->regex = $regex;
    }

    /**
     * @param string $line
     */
    public function readLine($line)
    {
        if (preg_match($this->regex, $line, $matches)) {
            $method = $matches[1];
            $pathInfo = $matches[2];
            if ($this->prefix && 0 === strpos($pathInfo, $this->prefix)) {
                $pathInfo = substr($pathInfo, $this->prefixLength);
            }
            $this->addPathInfoRecord($method, $pathInfo);
        }
    }
}
