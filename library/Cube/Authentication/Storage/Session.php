<?php

/**
 *
 * Cube Framework $Id$ GoS4pXD//bEV0etlArsuMMQcmZHvF2U5IOIgr5/WeCc=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */

namespace Cube\Authentication\Storage;

use Cube\Controller\Front,
    Cube\Session as SessionObject;

class Session implements StorageInterface
{

    const SESSION_NAMESPACE = 'CubeAuthentication';
    const DEFAULT_KEY = 'AuthStorage';

    /**
     *
     * session namespace
     *
     * @var string
     */
    protected $_namespace;

    /**
     *
     * session array key that will hold the auth storage
     *
     * @var string
     */
    protected $_key;

    /**
     *
     * session object
     *
     * @var \Cube\Session
     */
    protected $_session;

    /**
     *
     * class constructor
     *
     * @param string        $namespace
     * @param string        $key
     * @param \Cube\Session $session
     */
    public function __construct($namespace = null, $key = null, SessionObject $session = null)
    {
        $this->setNamespace($namespace)
            ->setKey($key)
            ->setSession($session);
    }

    /**
     *
     * get session namespace
     *
     * @return string
     */
    public function  getNamespace()
    {
        return $this->_namespace;
    }

    /**
     *
     * set session namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace = null)
    {
        if ($namespace === null) {
            $namespace = self::SESSION_NAMESPACE;
        }

        $this->_namespace = $namespace;

        return $this;
    }

    /**
     *
     * get auth storage key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     *
     * set auth storage key
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key = null)
    {
        if ($key === null) {
            $key = self::DEFAULT_KEY;
        }

        $this->_key = $key;

        return $this;
    }

    /**
     *
     * get session object
     *
     * @return \Cube\Session
     */
    public function getSession()
    {
        if (!($this->_session instanceof Session)) {
            $this->setSession();
        }

        return $this->_session;
    }

    /**
     *
     * set session object
     *
     * @param \Cube\Session $session
     *
     * @return $this
     */
    public function setSession(SessionObject $session = null)
    {
        if ($session === null) {
            $session = Front::getInstance()->getBootstrap()->getResource('session');
        }

        if (!$session instanceof SessionObject) {
            $session = new SessionObject();
            $session->setNamespace(
                $this->getNamespace());
        }

        $this->_session = $session;

        return $this;
    }

    /**
     *
     * clear storage
     *
     * @return $this
     */
    public function clear()
    {
        $this->_session->unregister($this->_key);

        return $this;
    }

    /**
     *
     * check if storage is empty and return true if it is
     *
     * @return bool
     */
    public function isEmpty()
    {
        $data = $this->_session->{$this->_key};

        return !isset($data);
    }

    /**
     *
     * return storage contents
     *
     * @return mixed
     */
    public function read()
    {
        return $this->_session->{$this->_key};
    }

    /**
     *
     * set storage contents
     *
     * @param mixed $contents
     *
     * @return $this
     */
    public function write($contents)
    {
        $this->_session->{$this->_key} = $contents;

        return $this;
    }

}

