<?php

/**
 *
 * Cube Framework $Id$ da60z/j2eAE/rP93CE/uXkdBbkPSu8YhJcQDcB3MF38=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * routes management abstract class
 */

namespace Cube\Controller\Router;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Router\Route\RouteInterface,
    Cube\Controller\Front;

abstract class AbstractRouter
{

    const URI_DELIMITER = '/';
    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'Index';

    /**
     *
     * defined routes
     *
     * @var array
     */
    protected $_routes = array();

    /**
     *
     * the request object (containing only request params)
     *
     * @var \Cube\Controller\Request
     */
    protected $_request;

    /**
     *
     * the route class that corresponds to this router
     * defined in each class that extends this one
     *
     * @var string
     */
    protected $_routeClass;

    /**
     *
     * class constructor
     *
     * @param array $routes
     */
    public function __construct(array $routes = array())
    {
        if (!empty($routes)) {
            $this->addRoutes($routes);
        }
    }

    /**
     *
     * add a single route to the routes array
     *
     * @param array|\Cube\Controller\Router\Route\RouteInterface $route can be an array from the config array or an instance of RouteInterface
     *
     * @return \Cube\Controller\Router
     * @throws \BadMethodCallException
     */
    public function addRoute($route)
    {
        if ($route instanceof RouteInterface) {
            $this->_routes[] = $route;
        }
        else if (is_array($route)) {
            $route = $this->_setRouteFromArray($route);

            if ($route !== false) {
                $this->_routes[] = $route;
            }
        }
        else {
            throw new \BadMethodCallException('The route object must be an instance of Cube\Controller\Router\Route\AbstractRoute or an array');
        }

        return $this;
    }

    /**
     *
     * add multiple routes to the routes array
     *
     * @param array $routes
     *
     * @return \Cube\Controller\Router
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }

        return $this;
    }

    /**
     *
     * retrieve the routes array
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     *
     * get a route object by its name
     *
     * @param string $name
     *
     * @return \Cube\Controller\Router\Route\RouteInterface
     * @throws \OutOfBoundsException
     */
    public function getRoute($name)
    {
        foreach ($this->_routes as $route) {
            /** @var \Cube\Controller\Router\Route\AbstractRoute $route */
            if ($route->getName() == $name) {
                return $route;
            }
        }

        throw new \OutOfBoundsException(
            sprintf("The route named '%s' does not exist.", $name));
    }

    /**
     *
     * set a routed request
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return $this
     */
    public function setRequest(AbstractRequest $request)
    {
        $this->_request = $this->route($request);

        return $this;
    }

    /**
     *
     * get routed request
     *
     * @return \Cube\Controller\Request
     */
    public function getRequest()
    {
        if (!$this->_request instanceof AbstractRequest) {
            $this->setRequest(
                new \Cube\Controller\Request());
        }

        return $this->_request;
    }

    /**
     *
     * create a route object from an input array
     *
     * @param array $route the route in array format
     *
     * @return \Cube\Controller\Router\Route\AbstractRoute|false      return a route object or false if invalid data was provided
     */
    protected function _setRouteFromArray(array $route)
    {
        if (!empty($route[0])) {
            return new $this->_routeClass($route[0], $route[1], $route[2]);
        }

        return false;
    }

    /**
     *
     * route the request using a hardcoded default route
     *
     * the uri of the route is [/:module][/:controller][/:action][{/:paramKey/:paramValue}]
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return \Cube\Controller\Request\AbstractRequest
     */
    protected function _getDefaultRoute(AbstractRequest $request)
    {

        $parts = (array)$request->filterInput(array_filter(
            explode(self::URI_DELIMITER, $request->getRequestUri())
        ));

        $modules = (array)Front::getInstance()->getOption('modules');

        $part = ucfirst(array_shift($parts));


        if (in_array($part, $modules)) {
            $request->setModule(strtolower($part));

            $controller = array_shift($parts);
        }
        else {
            $request->setModule($modules[0]);
            $controller = $part;
        }

        $request->setController(
            (!empty($controller)) ? strtolower($controller) : self::DEFAULT_CONTROLLER);

        $action = array_shift($parts);
        $request->setAction(
            (!empty($action)) ? $action : self::DEFAULT_ACTION);

        while ($key = array_shift($parts)) {
            $value = array_shift($parts);

            $request->setParam($key, $value)
                ->setQuery($key, $value);
        }

        return $request;
    }

    /**
     *
     * get all params from the current request uri
     *
     * @param array $params
     * @param bool  $addGetParams whether to attach params resulted from a previous get operation to the url
     * @param array $skipParams   an array of params to be omitted when constructing the url
     *
     * @return array
     */
    protected function _getDefaultParams(array $params = null, $addGetParams = false, array $skipParams = null)
    {
        $data = array();

        $this->setRequest(
            new \Cube\Controller\Request());
        $request = $this->getRequest();

        $modules = (array)Front::getInstance()->getOption('modules');

        $module = $request->normalize(
            (isset($params['module'])) ? $params['module'] : $request->getModule(), true);

        $defaultModule = $modules[0];

        if ($module != $defaultModule) {
            $data['module'] = $module;
        }

        $data['controller'] = $request->normalize(
            (isset($params['controller'])) ? $params['controller'] : $request->getController(), true);
        $data['action'] = $request->normalize(
            (isset($params['action'])) ? $params['action'] : $request->getAction(), true);

        if ($addGetParams === true) {
            $params = array_merge((array)$request->getQuery(), (array)$params);
        }

        foreach ((array)$params as $key => $value) {
            if (!in_array($key, array('module', 'controller', 'action')) && !empty($value)) {
                $data[$key] = $value;
            }
        }

        foreach ((array)$skipParams as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     *
     * routes a request and returns the routed request object
     * can match multiple routes, and will return the one that was matched last
     *
     * from the route's defaults array
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return \Cube\Controller\Request\AbstractRequest    returns the routed request
     */
    abstract public function route(AbstractRequest $request);

    /**
     *
     * return a url string after processing the params and matching them to one of the existing routes
     * if a string is given, return it unmodified
     * if no route is specified
     *
     * @param mixed  $params
     * @param string $name         the name of a specific route to use
     * @param bool   $addBaseUrl   flag to add the base url param to the assembled route
     * @param bool   $addGetParams whether to attach params resulted from a previous get operation to the url
     * @param array  $skipParams   an array of params to be omitted when constructing the url
     *
     * @return string
     */
    abstract public function assemble($params, $name = null, $addBaseUrl = true, $addGetParams = false, array $skipParams = null);

}

