<?php

/**
 *
 * Cube Framework $Id$ RjMaoctusNu/8Aj1cw+9vGd3FjDDMMzh5AX+v7Wipb0=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * helper that renders a controller action
 */

namespace Cube\View\Helper;

use Cube\Controller\Front;

class Action extends AbstractHelper
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
     * @var \Cube\Http\Response
     */
    protected $_response;

    /**
     *
     * dispatcher object
     *
     * @var \Cube\Controller\Dispatcher
     */
    protected $_dispatcher;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        $front = Front::getInstance();

        $this->_request = clone $front->getRequest();
        $this->_response = clone $front->getResponse();
        $this->_dispatcher = clone $front->getDispatcher();
    }

    /**
     * Retrieve rendered contents of a controller action
     *
     * If the action results in a forward or redirect, returns empty string.
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module Defaults to default module
     * @param  array  $params
     *
     * @return string
     */
    public function action($action, $controller, $module = null, array $params = array())
    {
        $request = clone $this->_request;

        $this->_response->clearBody()
            ->clearHeaders();

        if ($module === null) {
            $module = $request->getModule();
        }

        if ($params) {
            $request->clearParams();
        }

        $request->setParams($params)
            ->setModule($module)
            ->setController($controller)
            ->setAction($action)
            ->setDispatched(true);

        $this->_dispatcher->dispatch($request, $this->_response, true);

        if (!$request->isDispatched()) {
//                || $this->_response->isRedirect()) {
            // forwards and redirects render nothing
            return '';
        }

        $response = $this->_response->getBody();

        return $response;
    }

}

