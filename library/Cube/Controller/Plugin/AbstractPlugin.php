<?php

/**
 *
 * Cube Framework $Id$ LsBg7njUKQvd+3q4/qmXsAs/dvuIKTms9eGqw4DtpZY=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * controller plugins abstract class
 */

namespace Cube\Controller\Plugin;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\AbstractResponse;

class AbstractPlugin
{

    /**
     *
     * request object
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

    /**
     *
     * @var \Cube\Controller\Response\AbstractResponse
     */
    protected $_response;

    /**
     *
     * get request object
     *
     * @return \Cube\Controller\Request\AbstractRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     *
     * set request object
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return \Cube\Controller\Plugin\AbstractPlugin
     */
    public function setRequest(AbstractRequest $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     *
     * get response object
     *
     * @return \Cube\Controller\Response\AbstractResponse
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     *
     * set response object
     *
     * @param \Cube\Controller\Response\AbstractResponse $response
     *
     * @return \Cube\Controller\Plugin\AbstractPlugin
     */
    public function setResponse(AbstractResponse $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     *
     * this method will be run prior to routing the request
     */
    public function preRoute()
    {

    }

    /**
     *
     * this method will be run after the request has been routed
     */
    public function postRoute()
    {

    }

    /**
     *
     * this method will be run before starting the dispatch loop
     */
    public function preDispatcher()
    {

    }

    /**
     *
     * this method will be run after the dispatch loop has ended
     */
    public function postDispatcher()
    {

    }

    /**
     *
     * this method will be run each time an action is dispatched
     */
    public function preDispatch()
    {

    }

    /**
     *
     * this method will be run after an action has been dispatched
     */
    public function postDispatch()
    {

    }

}

