<?php

/**
 * 
 * Cube Framework $Id$ CfYizv9pZnS7XQmtHvQ6R9oLUl6e1ryMbnGMJUyejyY= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * controller action helper abstract class
 */

namespace Cube\Controller\Action\Helper;

use Cube\Controller\Front,
    Cube\Controller\Action\AbstractAction;

abstract class AbstractHelper
{

    /**
     *
     * action object
     * 
     * @var \Cube\Controller\Action\AbstractAction
     */
    protected $_action;

    /**
     * 
     * get action object
     * 
     * @return \Cube\Controller\Action\AbstractAction
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * 
     * set action object
     * 
     * @param \Cube\Controller\Action\AbstractAction $action
     * @return \Cube\Controller\Action\Helper\AbstractHelper
     */
    public function setAction(AbstractAction $action)
    {
        $this->_action = $action;

        return $this;
    }

    /**
     * 
     * get request object from action controller or front controller
     * 
     * @return \Cube\Controller\Request\AbstractRequest
     */
    public function getRequest()
    {
        $action = $this->getAction();

        if ($action === null) {
            $action = Front::getInstance();
        }

        return $action->getRequest();
    }

    /**
     * 
     * get response object from action controller or front controller
     * 
     * @return \Cube\Controller\Response\ResponseInterface
     */
    public function getResponse()
    {
        $action = $this->getAction();

        if ($action === null) {
            $action = Front::getInstance();
        }

        return $action->getResponse();
    }

}

