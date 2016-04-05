<?php

/**
 *
 * Cube Framework $Id$ R+L4Kz+ojLFtqptLKGS1ySXe8YpTm8bSWNv8RhB7XmQ=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * abstract route object
 */

namespace Cube\Controller\Router\Route;

abstract class AbstractRoute implements RouteInterface
{

    const URI_DELIMITER = '/';
    const DEFAULT_MATCH = '#:([a-zA-Z0-9_-]+)#';
    const DEFAULT_REGEX = '[a-zA-Z0-9_-]+';

    /**
     *
     * true if route has been matched to given request uri or false otherwise
     *
     * @var bool
     */
    protected $_matched;

    /**
     *
     * the route path from the config that will be matched with the provided url
     *
     * @var string
     */
    protected $_route;

    /**
     *
     * the name of the route
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * the name of the module this route belongs to
     *
     * @var string
     */
    protected $_module = null;

    /**
     *
     * default module, controller, action and params for the route created
     *
     * @var array
     */
    protected $_defaults = array();

    /**
     *
     * conditions for the params, regular expressions required
     *
     * @var array
     */
    protected $_conditions = array();

    /**
     *
     * holds the params of a matched route so that it can be assembled
     *
     * @var array
     */
    protected $_params = array();


//    /**
//     *
//     * holds all params of a route including defaults and regex rules for variables
//     * will be used internally only
//     * 
//     * @var array
//     */
//    protected $_parts = array();

    /**
     *
     * class constructor, initialize route object
     *
     * @param string $route
     * @param array  $defaults
     * @param array  $conditions
     */
    public function __construct($route, $defaults = array(), $conditions = array())
    {
        $this->setDefaults($defaults)
            ->setConditions($conditions)
            ->setRoute($route);
    }

    /**
     *
     * get route path
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->_route;
    }

    /**
     *
     * set route path
     *
     * @param string $route
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setRoute($route)
    {
        if (empty($route) || !is_string($route)) {
            throw new \InvalidArgumentException("The route path must be a string.");
        }

        $this->_route = $route;
//
//
//        $params = array();
//        $values = array();
//
//        preg_match_all(self::DEFAULT_MATCH, $this->_route, $params, PREG_PATTERN_ORDER);
//
//        foreach ((array) $params[1] as $param) {
//            $this->_parts[$param] = (isset($this->_conditions[$param])) ? $this->_conditions[$param] : self::DEFAULT_REGEX;
//        }

        return $this;
    }

    /**
     *
     * get the name of the module the route belongs to
     *
     * @throws \OutOfBoundsException
     * @return string
     */
    public function getModule()
    {
        if (empty($this->_module)) {
            throw new \OutOfBoundsException("The module for this route has not been set.");
        }

        return $this->_module;
    }

    /**
     *
     * set the name of the module this route belongs to
     *
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->_module = (string)$module;

        return $this;
    }

    /**
     *
     * get the name of the controller from the route
     *
     * @return string|null
     */
    public function getController()
    {
        if (isset($this->_params['controller'])) {
            return $this->_params['controller'];
        }
        else if (isset($this->_defaults['controller'])) {
            return $this->_defaults['controller'];
        }

        return null;
    }

    /**
     *
     * get the name of the action from the route
     *
     * @return string|null
     */
    public function getAction()
    {
        if (isset($this->_params['action'])) {
            return $this->_params['action'];
        }
        else if (isset($this->_defaults['action'])) {
            return $this->_defaults['action'];
        }

        return null;
    }

    /**
     *
     * return if the route has been matched or not
     *
     * @return bool
     */
    public function isMatched()
    {
        return (bool)$this->_matched;
    }

    /**
     *
     * get route default parameters
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }

    /**
     *
     * set route default parameters
     * if there are keys in the defaults array that are different than controller and action,
     * then add these as params
     *
     * @param array $defaults
     *
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        foreach ($defaults as $key => $value) {
            if (!in_array($key, array('controller', 'action'))) {
                $this->setParam($key, $value);
            }
        }

        $this->_defaults = $defaults;

        return $this;
    }

    /**
     *
     * get route parameter conditions (regex)
     *
     * @return array
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     *
     * set route parameter conditions (regex)
     *
     * @param array $conditions
     *
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $this->_conditions = $conditions;

        return $this;
    }

    /**
     *
     * get route params (generated when matching the route to the request)
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     *
     * set route params
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams(array $params = null)
    {
        if (!empty($this->_params)) {
            foreach ($params as $key => $value) {
                $this->setParam($key, $value);
            }
        }

        return $this;
    }

    /**
     *
     * get a single param from the route object
     *
     * @param string $name
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getParam($name)
    {
        if (!isset($this->_params[$name])) {
            throw new \InvalidArgumentException(
                sprintf("The parameter '%s' does not exist in the route object.", $name));
        }

        return $this->_params[$name];
    }

    /**
     *
     * set a single param to the route object.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setParam($name, $value)
    {
        $this->_params[(string)$name] = (string)$value;

        return $this;
    }

    /**
     *
     * get the name of the route
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * set a name for the route object
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     *
     * match the route to a request uri
     *
     * @param string $requestUri
     *
     * @return bool
     */
    public function match($requestUri)
    {
        $this->_params = array();

        $params = array();
        $values = array();

        preg_match_all(self::DEFAULT_MATCH, $this->_route, $params, PREG_PATTERN_ORDER);

        $urlRegex = trim(
            preg_replace_callback(self::DEFAULT_MATCH, array($this, '_matchCallback'), $this->_route), self::URI_DELIMITER);


        $requestUri = trim($requestUri, self::URI_DELIMITER) . self::URI_DELIMITER;


        if (!empty($urlRegex) &&
            preg_match('#^' . $urlRegex . '(.+)$#', $requestUri, $values)
        ) {
            $this->_matched = true;

            array_shift($values);
        }
        else {
            $this->_matched = false;
        }

        foreach ((array)$params[1] as $key) {
            $this->setParam($key, array_shift($values));

            if (!array_key_exists($key, $this->_conditions)) {
                $this->_conditions[$key] = self::DEFAULT_REGEX;
            }
        }

        // now we get any params that were matched by the route itself, format and include them 
        // in the request
        $parts = (array)explode(self::URI_DELIMITER, trim(array_shift($values), self::URI_DELIMITER));

        while ($param = array_shift($parts)) {
            $this->setParam($param, array_shift($parts));
        }

        return $this->_matched;
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

    /**
     *
     * method used to replace parameters with regular expressions in the route path
     *
     * @param array $matches
     *
     * @return string
     */
    protected function _matchCallback($matches)
    {
        $key = str_replace(':', '', $matches[0]);

        if (array_key_exists($key, $this->_conditions)) {
            return '(' . $this->_conditions[$key] . ')';
        }

        return '([a-zA-Z0-9_\+\-%]+)';
    }

    /**
     *
     * get an array of params and if the route matches, return a routed url
     * the method will also route requests if all params in the route match the params in
     * the request, but there are extra params in the request
     *
     * @param array $params
     * @param bool  $named if the flag is set to true, we need to match by params only
     *
     * @return string|null the assembled url or null if the route doesnt match
     */
    abstract public function assemble($params, $named = false);


}

