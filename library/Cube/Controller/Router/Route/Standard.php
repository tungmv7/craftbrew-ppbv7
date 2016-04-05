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
 * route object when mod rewrite or equivalent is not available
 */

namespace Cube\Controller\Router\Route;

use Cube\Controller\Router\Standard as StandardRouter;

class Standard extends AbstractRoute
{

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
            $this->setParam($key, $value);
        }

        $this->_defaults = $defaults;

        return $this;
    }

    /**
     *
     * get all params
     *
     * @return array
     */
    public function getAllParams()
    {
        $params['module'] = $this->getModule();
        $params['controller'] = $this->getController();
        $params['action'] = $this->getAction();
        $params += array_filter($this->getParams());

        return $params;
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
    public function assemble($params, $named = false)
    {
        if (!is_array($params)) {
            $params = (array)$params;
        }

        $allParams = $this->getAllParams() + array_filter($params);
        foreach ($params as $key => $value) {
            $allParams[$key] = $value;
        }

        $params = $allParams;

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

        $uri = StandardRouter::DEFAULT_PATH;

        if (count($get) > 0) {
            $uri .= '?' . implode('&', $get);
        }

        return $uri;
    }


}

