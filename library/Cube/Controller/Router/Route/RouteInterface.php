<?php

/**
 *
 * Cube Framework $Id$ hMpTUkRDs30HT4YQ3qopJXxxdOzSqd7ipUgzNfReUn8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * route object interface
 */

namespace Cube\Controller\Router\Route;

/**
 * Interface RouteInterface
 *
 * @package Cube\Controller\Router\Route
 */
interface RouteInterface
{

    /**
     *
     * match the route to a request uri
     *
     * @param string $requestUri
     *
     * @return mixed
     */
    public function match($requestUri);
}

