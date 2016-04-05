<?php

/**
 *
 * Cube Framework $Id$ VwJ5d27iruJp9OnmF8aOKgM/o4TtBla6P54HMC91KOA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.5
 */

namespace Cube\Controller\Request;

/**
 * request object - for when mod rewrite is not available
 *
 * Class Standard
 *
 * @package Cube\Controller\Request
 */
class Standard extends AbstractRequest
{

    const DEFAULT_MODULE = 'App';
    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'Index';

    /**
     *
     * set the request uri and format it so that it can be used by the router
     * (remove the base url of the application from the uri and remove any GET variables as well)
     *
     * @1.5: modified this method so that it will work on servers where preg_replace does not return the intended results
     *
     * @param string $requestUri
     *
     * @return \Cube\Controller\Request\AbstractRequest
     */
    public function setRequestUri($requestUri = null)
    {
        if ($requestUri === null) {
            // we can retrieve more ways if needed
            // we replace the first occurrence of the base url (used if installing the app in a sub-domain)

            if (array_key_exists('REQUEST_URI', $_SERVER)) {
                $requestUri = $_SERVER['REQUEST_URI'];

                if ($baseUrl = $this->getBaseUrl()) {
                    $requestUri = str_ireplace('//', '/', $this->_strReplaceFirst($baseUrl, '', $requestUri));
                }
            }
        }

        $this->_requestUri = $requestUri;

        return $this;
    }

    /**
     *
     * get the module from the routed request
     *
     * @return string
     */
    public function getModule()
    {
        if (!$this->_module) {
            $this->setModule(self::DEFAULT_MODULE);
        }

        return $this->_module;
    }

    /**
     *
     * get the name of the routed action controller
     *
     * @return string
     */
    public function getController()
    {
        if (!$this->_controller) {
            $this->setController(self::DEFAULT_CONTROLLER);
        }

        return $this->_controller;
    }

    /**
     *
     * get the name of the routed controller action
     *
     * @return string
     */
    public function getAction()
    {
        if (!$this->_action) {
            $this->setAction(self::DEFAULT_ACTION);
        }

        return $this->_action;
    }

    protected function _strReplaceFirst($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}

