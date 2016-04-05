<?php

/**
 *
 * Cube Framework $Id$ ElH1ptG9UrqmdqBK3UEHfEtPHThOggtX40YxNFvdrTY=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Controller\Request;

/**
 * request object interface
 *
 * Interface RequestInterface
 *
 * @package Cube\Controller\Request
 */
interface RequestInterface
{

    public function getParams();

    /**
     *
     * set multiple request params
     *
     * @param array $params
     */
    public function setParams(array $params = null);

    /**
     *
     * returns the value of a variable from a request
     *
     * @param string $name
     * @param mixed  $default a default value in case the variable is not set
     */
    public function getParam($name, $default = null);

    /**
     *
     * set the value of a request param
     *
     * @param string $key
     * @param string $value
     */
    public function setParam($key, $value);

    public function getModule();

    /**
     *
     * set the name of the module for the routed request (all module names start with a capital letter)
     *
     * @param string $module
     */
    public function setModule($module);

    public function getController();

    /**
     *
     * set the name of the action controller for the routed request (all module names start with a capital letter)
     *
     * @param string $controller
     */
    public function setController($controller);

    public function getAction();

    /**
     *
     * set the name of the action from the routed request
     *
     * @param string $action
     */
    public function setAction($action);
}

