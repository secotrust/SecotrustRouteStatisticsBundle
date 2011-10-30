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
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

abstract class PathInfoReader extends Reader
{
    protected $pathInfoStats = array();
    protected $methodMap = array();
    protected $router;
    protected $prefix;
    protected $prefixLength;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param string $prefix
     */
    public function __construct(Router $router, $prefix)
    {
        $this->router = $router;
        $this->prefix = $prefix;
        $this->prefixLength = strlen($prefix);
    }

    /**
     * @return array
     */
    public function getStatistics()
    {
        foreach ($this->pathInfoStats as $pathInfo => $count) {
            try {
                $requestContext = new RequestContext('', $this->methodMap[$pathInfo]); // TODO scheme?
                $this->router->setContext($requestContext);
                $routeInfo = $this->router->match($pathInfo);
                @$this->stats[$routeInfo['_route']] += $count;
            } catch (ResourceNotFoundException $e) {
                // do nothing if no route matches...
            } catch (MethodNotAllowedException $e) {
                // do nothing if method is not allowed...
            }
        }

        return parent::getStatistics();
    }

    /**
     * @param string $method
     * @param string $pathInfo
     */
    protected function addPathInfoRecord($method, $pathInfo)
    {
        if (empty($pathInfo)) {
            $pathInfo = '/';
        }
        @$this->pathInfoStats[$pathInfo]++;
        @$this->methodMap[$pathInfo] = $method;
    }
}
