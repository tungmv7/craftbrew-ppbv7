<?php

/**
 *
 * Cube Framework $Id$ lICDedPukL+r/aNoCWWKXXP/8Z8mFtN6Cvm8QASBXNc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * dependency injection container interface
 */

namespace Cube\Di;

/**
 * Interface ContainerInterface
 *
 * @package Cube\Di
 */
interface ContainerInterface
{
    /**
     *
     * add a new service to the container
     *
     * @param string $name    the name of the service to be saved in the container
     * @param mixed  $service the service that will be saved
     */
    public function set($name, $service);

    /**
     *
     * get a service from the container
     *
     * @param string $name   the name of the service that is saved in the container
     * @param array  $params run the service with these params
     */
    public function get($name, array $params = array());

    /**
     *
     * check if a service has been saved in the container
     *
     * @param string $name
     */
    public function has($name);

    /**
     *
     * remove a service from the container
     *
     * @param string $name
     */
    public function remove($name);

    /**
     *
     * clear the container
     */
    public function clear();
}

