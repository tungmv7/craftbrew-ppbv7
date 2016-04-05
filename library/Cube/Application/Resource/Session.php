<?php

/**
 *
 * Cube Framework $Id$ GoS4pXD//bEV0etlArsuMMQcmZHvF2U5IOIgr5/WeCc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Application\Resource;

use Cube\Session as SessionObject;

/**
 * session resource management class
 *
 * Class Session
 *
 * @package Cube\Application\Resource
 */
class Session extends AbstractResource
{

    /**
     *
     * session object
     *
     * @var \Cube\Session
     */
    protected $_session;

    /**
     *
     * init session resource
     *
     * @return \Cube\Session
     */
    public function init()
    {
        if (!($this->_session instanceof SessionObject)) {
            $this->_session = new SessionObject($this->_options['session']);
        }

        return $this->_session;
    }

}

