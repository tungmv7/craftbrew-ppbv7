<?php

/**
 *
 * Cube Framework $Id$ VwJ5d27iruJp9OnmF8aOKgM/o4TtBla6P54HMC91KOA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * router class for when mod rewrite or equivalent is not available
 */


namespace Cube\Controller\Router;

use Cube\Controller\Front,
    Cube\Controller\Request\AbstractRequest;

class Standard extends AbstractRouter
{

    const DEFAULT_PATH = 'index.php';

    const DEFAULT_MATCH = '#:([a-zA-Z0-9\._-]+)#';
    const DEFAULT_REGEX = '[a-zA-Z0-9\._-]+';

    /**
     *
     * the route class that corresponds to this router
     *
     * @var string
     */
    protected $_routeClass = '\Cube\Controller\Router\Route\Standard';

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
    public function route(AbstractRequest $request)
    {
        $request->setModule($request->getParam('module'))
            ->setController($request->getParam('controller'))
            ->setAction($request->getParam('action'));

        $request->clearParam('module')
            ->clearParam('controller')
            ->clearParam('action');

        return $request;
    }

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
    public function assemble($params, $name = null, $addBaseUrl = true, $addGetParams = false, array $skipParams = null)
    {
        $url = null;

        // check if we have a named route or if params is a string
        if ($name !== null) {
            $route = $this->getRoute($name);

            $url = $route->assemble($params, true);
        }
        else if (is_string($params)) {
            $uri = $params;

            /** @var \Cube\Controller\Router\Route\Standard $route */
            foreach ($this->_routes as $route) {
                $routePattern = preg_replace(self::DEFAULT_MATCH, '(' . self::DEFAULT_REGEX . ')', $route->getRoute());
                if (preg_match('#^' . $routePattern . '$#', $params, $values)) {
                    preg_match_all(self::DEFAULT_MATCH, $route->getRoute(), $uri, PREG_PATTERN_ORDER);

                    array_shift($values);
                    foreach ((array)$uri[1] as $key) {
                        $route->setParam($key, array_shift($values));
                    }

                    $url = $route->assemble(
                        $route->getAllParams());
                }
            }
        }


        if (!isset($url)) {
            if (is_string($params)) {
                $parts = (array)explode(self::URI_DELIMITER, $params);


                $params = array();
                $params['module'] = array_shift($parts);
                $params['controller'] = array_shift($parts);
                $params['action'] = array_shift($parts);

                while ($key = array_shift($parts)) {
                    $value = array_shift($parts);
                    $params[$key] = $value;
                }
            }


            $params = $this->_getDefaultParams((array)$params, $addGetParams, $skipParams);
            $url = $this->_defaultRouteAssemble($params);
        }

        if (!preg_match('#^[a-z]+://#', $url)) {
            $url = (($addBaseUrl) ? Front::getInstance()->getRequest()->getBaseUrl() : '')
                . self::URI_DELIMITER
                . ltrim($url, self::URI_DELIMITER);
        }

        return $url;
    }

    /**
     *
     * assemble a request when no routes match
     * for array params, they wont be added in the url, but after the ? character
     *
     * if no module, controller and action is specified in the params, return the current uri + params after ? character
     *
     * @param array $params
     *
     * @return string   the routed uri
     */
    protected function _defaultRouteAssemble(array $params = null)
    {
        $uri = self::DEFAULT_PATH;
        $get = array();

        foreach ((array)$params as $key => $value) {
            if (preg_match('#^[a-zA-Z0-9_-]+$#', $key)) {
                if (!is_array($value)) {
                    if (in_array($key, array('module', 'controller', 'action'))) {
                        $value = $this->normalize($value, true);
                    }

                    $get[] = $key . '=' . $value;
                }
                else {
                    foreach ((array)$value as $val) {
                        if (!empty($val)) {
                            $get[] = $key . '[]=' . $val;
                        }
                    }
                }
            }
        }

        if (count($get) > 0) {
            $uri .= '?' . implode('&', $get);
        }

        return $uri;
    }

    /**
     *
     * normalize url parts for modules/controllers/actions in order for them to be parsable by the router
     *
     * - will first convert the input to lowercase
     * - will remove any non alpha-numeric and '-' characters from the value
     * - will convert '-' to camel cased and will capitalize the first letter of the string
     *
     * if reverse is set to true, we convert a router router parsable string into a url string
     *
     * @param string $input
     * @param bool   $reverse
     *
     * @return string
     */
    public function normalize($input, $reverse = false)
    {
        if ($reverse) {
            return strtolower(
                preg_replace("/([a-z])([A-Z])/", '$1-$2', $input));
        }
        else {
            $input = str_replace('-', ' ', strtolower(
                preg_replace("/[^a-zA-Z0-9\_\-]/", '', $input)));

            return str_replace(' ', '', ucwords($input));
        }
    }
}

