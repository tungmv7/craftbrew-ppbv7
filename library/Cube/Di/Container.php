<?php

/**
 * 
 * Cube Framework $Id$ +YE7VyINsh7jjma6W2JtRPVrVjW1Z7VrmXw3MIsFvJM= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * dependency injection container
 */

namespace Cube\Di;

class Container implements ContainerInterface
{

    /**
     *
     * objects container
     * 
     * @var array
     */
    protected $_services = array();

    /**
     * 
     * add a new service to the container
     * 
     * @param string $name      the name of the service to be saved in the container
     * @param mixed $service    the service that will be saved
     * @return \Cube\Di\Container
     * @throws \InvalidArgumentException
     */
    public function set($name, $service)
    {
        if (!is_object($service) && !is_string($service)) {
            throw new \InvalidArgumentException("Only objects or strings can be registered with the container.");
        }
        if (!in_array($service, $this->_services, true)) {
            $this->_services[$name] = $service;
        }

        return $this;
    }

    /**
     * 
     * get a service from the container
     * 
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws \RuntimeException
     */
    public function get($name, array $params = array())
    {
        if (!isset($this->_services[$name])) {
            throw new \RuntimeException(sprintf("The service '%s' has not been registered with the container.", $name));
        }
        $service = $this->_services[$name];
        return !$service instanceof \Closure ? $service : call_user_func_array($service, $params);
    }

    /**
     * 
     * check if a service has been saved in the container
     * 
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->_services[$name]);
    }

    /**
     * 
     * remove a service from the container
     * 
     * @param string $name
     * @return \Cube\Di\Container
     */
    public function remove($name)
    {
        if (isset($this->_services[$name])) {
            unset($this->_services[$name]);
        }
        return $this;
    }

    /**
     * 
     * clear the container
     */
    public function clear()
    {
        $this->_services = array();
    }

}

