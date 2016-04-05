<?php

/**
 *
 * Cube Framework $Id$ qfuW+b/TM3AsUKopvyG2YJzTO8HgNxMyxcjFqpAHTYc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * bootstrap class
 */

namespace Cube\Application;

use Cube\Application,
    Cube\Controller\Front as FrontController;

class Bootstrap
{
    /**
     * default name of resources namespace
     */

    const RESOURCES_NAMESPACE = 'Resource';

    /**
     *
     * application object
     *
     * @var \Cube\Application
     */
    protected $_application;

    /**
     *
     * resources array
     *
     * @var array
     */
    protected $_resources = array();

    /**
     *
     * bootstrap front controller resource
     * final class so it cannot be overridden by module bootstraps
     */
    final public function __construct()
    {
        $this->setResource('FrontController', FrontController::getInstance()
            ->setOptions(
                $this->getApplication()->getOptions()));
    }

    /**
     *
     * get the application object
     *
     * @return \Cube\Application
     */
    public function getApplication()
    {
        if (!($this->_application instanceof Application)) {
            $this->_application = Application::getInstance();
        }

        return $this->_application;
    }

    /**
     *
     * add a resource to the resources array
     *
     * @param string $name
     * @param mixed  $resource
     *
     * @return \Cube\Application\Bootstrap
     */
    public function setResource($name, $resource)
    {
        if ($this->hasResource($name) === false) {
            $this->_resources[$name] = $resource;
        }
//        else {
//            throw new \InvalidArgumentException(sprintf("A resource with the name '%s' already exists", $name));
//        }

        return $this;
    }

    /**
     *
     * get a resource
     *
     * @param string $name
     *
     * @return mixed|bool
     */
    public function getResource($name)
    {
        if ($this->hasResource($name) === true) {
            return $this->_resources[$name];
        }

        return false;
    }

    /**
     *
     * remove a resource
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeResource($name)
    {
        if ($this->hasResource($name) === true) {
            unset($this->_resources[$name]);
        }

        return $this;
    }

    /**
     *
     * check if a resource exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasResource($name)
    {
        if (array_key_exists($name, $this->_resources)) {
            return true;
        }

        return false;
    }

    /**
     *
     * bootstrap a single resource
     * we will search for the resource either in the default resources folder or in the bootstrap of the requested module
     * a resource will only be bootstrapped once
     *
     * @param string $resource
     *
     * @throws \DomainException
     */
    protected function _bootstrap($resource)
    {

        if (!$this->hasResource($resource)) {
            $methodName = '_init' . ucfirst($resource);
            $resourceName = '\\' . __NAMESPACE__ . '\\' . self::RESOURCES_NAMESPACE . '\\' . ucfirst($resource);

            if (class_exists($resourceName)) {
                $class = new $resourceName();

                if ($class instanceof Resource\ResourceInterface) {
                    $options = $this->getApplication()->getOptions();

                    $object = $class->setOptions($options)
                        ->init();

                    $this->setResource($resource, $object);
                }
                else {
                    throw new \DomainException(sprintf("'%s' must be implement the ResourceInterface interface.",
                        $resourceName));
                }
            }
            else if (method_exists($this, $methodName)) {

                $result = $this->$methodName();

                $this->setResource($resource, $result);
            }
        }
    }

    /**
     *
     * bootstrap one, multiple (array) or all resources if no parameters are set
     * final method
     *
     * @param null|string|array $resource
     *
     * @return \Cube\Application\Bootstrap
     */
    final public function bootstrap($resource = null)
    {
        if (is_string($resource)) {
            $this->_bootstrap($resource);
        }
        else if (is_array($resource)) {
            foreach ($resource as $res) {
                $this->_bootstrap($res);
            }
        }
        else {
            $options = $this->getApplication()->getOptions();

            // bootstrap default application resources that are initialized in the application configuration
            foreach ((array)$options as $name => $config) {
                $this->_bootstrap($name);
            }

            // bootstrap resources defined in the active module bootstrap file
            foreach ((array)get_class_methods($this) as $method) {
                if (strpos($method, '_init') === 0) {
                    $this->_bootstrap(lcfirst(
                        str_replace('_init', '', $method)));
                }
            }
        }

        return $this;
    }

    /**
     *
     *
     * run the application
     */
    public function run()
    {
        $front = $this->getResource('FrontController');

        return $front->dispatch();
    }

}

