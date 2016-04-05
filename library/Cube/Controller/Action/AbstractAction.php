<?php

/**
 *
 * Cube Framework $Id$ EfVzxPeEAVCMJgcyTqCKeNy/91QknThQEmRDYUsEMIA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * abstract action controller class
 * application controllers will extend from this class
 */

namespace Cube\Controller\Action;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Response\ResponseInterface;

abstract class AbstractAction
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
     * response object
     *
     * @var \Cube\Controller\Response\ResponseInterface
     */
    protected $_response;

    /**
     *
     * action helper broker
     *
     * @var \Cube\Controller\Action\Helper\Broker
     */
    protected $_helper;

    /**
     *
     * class constructor
     *
     * @param \Cube\Controller\Request\AbstractRequest    $request
     * @param \Cube\Controller\Response\ResponseInterface $response
     */
    public function __construct(AbstractRequest $request, ResponseInterface $response)
    {
        $this->setRequest($request)
            ->setResponse($response);

        $this->_helper = new Helper\Broker($this);

        $this->init();
    }

    /**
     *
     * function run when controller is initialized, can be overridden if functionality is needed to apply for
     * all actions
     */
    public function init()
    {

    }

    /**
     *
     * return request object
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
     * @return \Cube\Controller\Action\AbstractAction
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
     * @return \Cube\Controller\Response\ResponseInterface
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     *
     * set response object
     *
     * @param \Cube\Controller\Response\ResponseInterface $response
     *
     * @return \Cube\Controller\Action\AbstractAction
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     *
     * forward to another action/controller/module
     *
     * used by the front controller dispatcher and initiates a dispatch loop
     * the pre-dispatch and post-dispatch plugins will be run on each loop
     *
     * @param string     $action
     * @param string     $controller
     * @param string     $module
     * @param array|null $params
     */
    final protected function _forward($action, $controller = null, $module = null, $params = null)
    {
        $request = $this->getRequest();

        if ($controller !== null) {
            $request->setController($controller);
        }

        if ($module !== null) {
            $request->setModule($module);
        }

        if ($params !== null) {
            $request->setParams($params);
        }

        $request->setAction($action)
            ->setDispatched(false);
    }

}

