<?php

/**
 *
 * Cube Framework $Id$ KZvHPNY7IyBDOJftxrvL5lWu8QJzOpKlnn8PCpQTCF8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Controller\Dispatcher;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\ResponseInterface;

/**
 * dispatcher interface
 *
 * Interface DispatcherInterface
 *
 * @package Cube\Controller\Dispatcher
 */
interface DispatcherInterface
{

    /**
     *
     * dispatch method
     *
     * @param \Cube\Controller\Request\AbstractRequest    $request
     * @param \Cube\Controller\Response\ResponseInterface $response
     */
    public function dispatch(AbstractRequest $request, ResponseInterface $response);
}

