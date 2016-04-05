<?php

/**
 * 
 * Cube Framework $Id$ WqQ424ZXvHqEJmaObRuHyQ/oLJNPj1fy+rhjXLyloeM= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */

namespace Cube\Navigation\Page;

use Cube\Controller\Front;

class Mvc extends AbstractPage
{

    /**
     *
     * page module 
     * 
     * @var string 
     */
    protected $_module;

    /**
     *
     * page controller
     * 
     * @var string
     */
    protected $_controller;

    /**
     *
     * page action
     * 
     * @var string
     */
    protected $_action;

    /**
     *
     * page params
     * 
     * @var array 
     */
    protected $_params = array();

    /**
     * 
     * get page module
     * 
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * 
     * set page module
     * 
     * @param string $module
     * @return \Cube\Navigation\Page\Mvc
     * @throws \InvalidArgumentException
     */
    public function setModule($module)
    {
        if (!is_string($module) && $module !== null) {
            throw new \InvalidArgumentException(sprintf(
                            "'module' must be a string or null, %s given.", gettype($module)));
        }

        $this->_module = $module;

        return $this;
    }

    /**
     * 
     * get page controller
     * 
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * 
     * set page controller
     * 
     * @param string $controller
     * @return \Cube\Navigation\Page\Mvc
     * @throws \InvalidArgumentException
     */
    public function setController($controller)
    {
        if (!is_string($controller) && $controller !== null) {
            throw new \InvalidArgumentException(sprintf(
                            "'controller' must be a string or null, %s given.", gettype($controller)));
        }

        $this->_controller = $controller;

        return $this;
    }

    /**
     * 
     * get page action
     * 
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 
     * set page action
     * 
     * @param string $action
     * @return \Cube\Navigation\Page\Mvc
     * @throws \InvalidArgumentException
     */
    public function setAction($action)
    {
        if (!is_string($action) && $action !== null) {
            throw new \InvalidArgumentException(sprintf(
                            "'action' must be a string or null, %s given.", gettype($action)));
        }

        $this->_action = $action;

        return $this;
    }

    /**
     * 
     * get page params
     * 
     * @return string
     */
    public function getParams()
    {
        $params = array(
            'module' => $this->_module,
            'controller' => $this->_controller,
            'action' => $this->_action,
        );

        foreach ($this->_params as $key => $value) {
            $params[$key] = $value;
        }

        return $params;
    }

    /**
     * 
     * set page params
     * 
     * @param array $params
     * @return \Cube\Navigation\Page\Mvc
     */
    public function setParams(array $params = null)
    {
        $this->_params = (array) $params;

        return $this;
    }

    /**
     * 
     * check if the page is active, based on the request uri
     * 
     * @param bool $recursive    check in subpages as well, and if a subpage is active, return the current page as active
     * @return bool              returns active status
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            $frontController = Front::getInstance();
            $request = $frontController->getRequest();
            $router = $frontController->getRouter();

            $mvcParams = array(
                'module' => $this->_module,
                'controller' => $this->_controller,
                'action' => $this->_action,
            );

            $uri = $router->assemble(
                    array_merge($mvcParams, $this->_params), null, false);

            if ($request->matchRequestUri($uri)) {
                $this->_active = true;
                return true;
            }
        }

        return parent::isActive($recursive);
    }

}

