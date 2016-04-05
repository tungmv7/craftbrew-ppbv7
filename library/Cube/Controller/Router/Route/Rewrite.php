<?php

/**
 *
 * Cube Framework $Id$ ZbyiP35PiWOASpOLyiBjMTYnumzNcsspbewapC0SCl0=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * route with mod rewrite object
 */

namespace Cube\Controller\Router\Route;

class Rewrite extends AbstractRoute
{

    /**
     *
     * used by assemble method
     *
     * @var array
     */
    private $_vars;

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
    public function assemble($params, $named = false)
    {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $routeParams = $this->getParams();

        if ($named === false) {
            $routeParams['module'] = $this->normalize(
                $this->getModule(), true);

            foreach ($this->_defaults as $key => $value) {
                $routeParams[(string)$key] = (string)$value;
            }
        }

        $this->_vars = $params;

        if (array_key_exists('module', $params)) {
            if (strcmp($routeParams['module'], $params['module']) === 0) {
                unset($routeParams['module']);
                unset($params['module']);
            }
        }


        foreach ($params as $key => $value) {
            if (isset($this->_defaults[$key])) {
                if ($this->_defaults[$key] == $value) {
                    unset($params[$key]);
                    unset($routeParams[$key]);
                }
            }

            if (isset($this->_conditions[$key]) && is_string($value)) {
                if (preg_match('#(' . $this->_conditions[$key] . ')#', $value)) {
                    unset($params[$key]);
                    unset($routeParams[$key]);
                }
            }

            if (array_key_exists($key, $this->_params)) {
                unset($routeParams[$key]);
            }
        }

        if (empty($routeParams)) {
            $url = array();
            $get = array();
            foreach ((array)$params as $key => $value) {
                if (preg_match('#^[a-zA-Z0-9_-]+$#', $key)) {
                    if (!is_array($value)) {
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

            $uri = @implode(self::URI_DELIMITER, $url);
            if (count($get) > 0) {
                $uri .= '?' . implode('&amp;', $get);
            }

            return rtrim(preg_replace_callback(self::DEFAULT_MATCH, array($this, '_defaultMatchCallback'), $this->_route)
                . self::URI_DELIMITER
                . $uri, self::URI_DELIMITER);
        }

        return null;
    }

    /**
     *
     * used to replace parameters for default route
     *
     * @param array $matches
     *
     * @return string
     */
    private function _defaultMatchCallback($matches)
    {
        return $this->_vars[$matches[1]];
    }

}

