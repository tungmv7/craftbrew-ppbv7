<?php

/**
 *
 * Cube Framework $Id$ MQmwlGghNLsCgokchZvpjU8OqL/UEFISwXP/IVDpCpc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */
/**
 * controller action helper broker
 */

namespace Cube\Controller\Action\Helper;

use Cube\Controller\Action\AbstractAction;

class Broker
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
     * class constructor
     *
     * @param \Cube\Controller\Action\AbstractAction $action
     */
    public function __construct(AbstractAction $action)
    {
        $this->_action = $action;
    }

    /**
     * get action helper by name
     *
     * @param  string $name
     *
     * @throws \InvalidArgumentException
     * @return \Cube\Controller\Action\Helper\AbstractHelper
     */
    public function getHelper($name)
    {
        $className = '\\' . __NAMESPACE__ . '\\' . ucfirst($name);

        if (class_exists($className)) {
            /** @var \Cube\Controller\Action\Helper\AbstractHelper $helper */
            $helper = new $className();
            $helper->setAction($this->_action);
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("The action helper '%s' does not exist.", $className));
        }

        return $helper;
    }

    /**
     * @param string $name
     * @param mixed  $arguments
     *
     * @return \Cube\Controller\Action\Helper\AbstractHelper
     */
    public function __call($name, $arguments)
    {
        return $this->getHelper($name);
    }

}

