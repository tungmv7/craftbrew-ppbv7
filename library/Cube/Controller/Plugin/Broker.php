<?php

/**
 *
 * Cube Framework $Id$ MQmwlGghNLsCgokchZvpjU8OqL/UEFISwXP/IVDpCpc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * controller plugin broker class
 */

namespace Cube\Controller\Plugin;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\AbstractResponse;

class Broker extends AbstractPlugin
{

    /**
     *
     * array of objects of type \Cube\Controller\PluginAbstract
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     *
     * add a new plugin to the plugins stack
     *
     * @param \Cube\Controller\Plugin\AbstractPlugin $plugin
     *
     * @return \Cube\Controller\Plugin\Broker
     * @throws \OverflowException
     */
    public function registerPlugin(AbstractPlugin $plugin)
    {
        if ($this->isRegisteredPlugin($plugin) === true) {
            throw new \OverflowException("The controller plugin is already registered");
        }
        else {
            $this->_plugins[get_class($plugin)] = $plugin;
        }

        return $this;
    }

    /**
     *
     * check if the plugin has already been registered
     *
     * @param string|AbstractPlugin $plugin
     *
     * @return bool
     */
    public function isRegisteredPlugin($plugin)
    {
        if ($plugin instanceof AbstractPlugin && array_search($plugin, $this->_plugins, true) !== false) {
            return true;
        }
        else if (is_string($plugin) && array_key_exists($plugin, $this->_plugins)) {
            return true;
        }

        return false;
    }

    /**
     *
     * set the request object to the broker and all registered plugins
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return \Cube\Controller\Plugin\Broker
     */
    public function setRequest(AbstractRequest $request)
    {
        $this->_request = $request;

        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->setRequest($request);
            }
        }

        return $this;
    }

    /**
     *
     * set the response object to the broker and all registered plugins
     *
     * @param \Cube\Controller\Response\AbstractResponse $response
     *
     * @return \Cube\Controller\Plugin\Broker
     */
    public function setResponse(AbstractResponse $response)
    {
        $this->_response = $response;

        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->setResponse($response);
            }
        }

        return $this;
    }

    /**
     *
     * this method will be run prior to routing the request
     */
    public function preRoute()
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->preRoute();
            }
        }
    }

    /**
     *
     * this method will be run after the request has been routed
     */
    public function postRoute()
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->postRoute();
            }
        }
    }

    /**
     *
     * this method will be run before starting the dispatch loop
     */
    public function preDispatcher()
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->preDispatcher();
            }
        }
    }

    /**
     *
     * this method will be run after the dispatch loop has ended
     */
    public function postDispatcher()
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->postDispatcher();
            }
        }
    }

    /**
     *
     * this method will be run each time an action is dispatched
     */
    public function preDispatch()
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->preDispatch();
            }
        }
    }

    /**
     *
     * this method will be run after an action has been dispatched
     */
    public function postDispatch()
    {
        foreach ($this->_plugins as $plugin) {
            if ($plugin instanceof AbstractPlugin) {
                $plugin->postDispatch();
            }
        }
    }

}

