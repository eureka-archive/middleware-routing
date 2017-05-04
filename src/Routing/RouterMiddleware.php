<?php

/**
 * Copyright (c) 2010-2017 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Middleware\Routing;

use Eureka\Component\Config\Config;
use Eureka\Component\Container\Container;
use Eureka\Component\Http\Message\Response;
use Eureka\Component\Psr\Http\Middleware\DelegateInterface;
use Eureka\Component\Psr\Http\Middleware\ServerMiddlewareInterface;
use Eureka\Middleware\Routing\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Eureka\Component\Routing\Route;

class RouterMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var Routing\RouteCollection $collection
     */
    private $collection = null;

    /**
     * Class constructor.
     */
    public function __construct(Config $config)
    {
        //~ Pre-load routing fro config
        $this->collection = Container::getInstance()->get('routing');
        $this->collection->addFromConfig($config->get('global.routing'));
    }

    /**
     * @param ServerRequestInterface  $request
     * @param DelegateInterface $frame
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $frame)
    {
        $route = $this->collection->match((string) $request->getUri(), false);

        if (!($route instanceof Route)) {
            throw new Exception\RouteNotFoundException('Route not found', 10001);
        }

        $request = $request->withAttribute('route', $route);

        return $frame->next($request);
    }
}