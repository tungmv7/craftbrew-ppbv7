<?php

/**
 *
 * Cube Framework $Id$ 7gHVWJO3aHJR2EzpSnF2mJ85MMLh9UXiF7o0Msww7tM=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * authentication service class
 */

namespace Cube\Authentication;

class Authentication
{

    /**
     *
     * auth adapter interface
     *
     * @var \Cube\Authentication\Adapter\AdapterInterface
     */
    protected $_adapter;

    /**
     *
     * auth storage interface
     *
     * @var \Cube\Authentication\Storage\StorageInterface
     */
    protected $_storage;

    /**
     *
     * holds an instance of the object
     *
     * @var \Cube\Authentication\Authentication
     */
    protected static $_instance;

    /**
     *
     * class constructor
     */
    public function __construct(Storage\StorageInterface $storage = null)
    {
        $this->setStorage($storage);
    }

    /**
     *
     * returns an instance of the object and creates it if it wasnt instantiated yet
     *
     * @return \Cube\Authentication\Authentication
     */
    public static function getInstance()
    {

        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * get authentication adapter
     *
     * @return \Cube\Authentication\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     *
     * set authentication adapter
     *
     * @param \Cube\Authentication\Adapter\AdapterInterface $adapter
     * @return \Cube\Authentication\Authentication
     */
    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     *
     * get authentication storage
     *
     * @return \Cube\Authentication\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     *
     * set authentication storage
     * if no storage is submitted, a default session storage is created
     *
     * @param \Cube\Authentication\Storage\StorageInterface $storage
     * @return \Cube\Authentication\Authentication
     */
    public function setStorage(Storage\StorageInterface $storage = null)
    {
        if ($storage === null) {
            $storage = new Storage\Session();
        }

        $this->_storage = $storage;

        return $this;
    }

    /**
     *
     * authenticate using the supplied adapter
     *
     * @param \Cube\Authentication\Adapter\AdapterInterface $adapter
     * @return \Cube\Authentication\Result
     * @throws \RuntimeException
     */
    public function authenticate(Adapter\AdapterInterface $adapter = null)
    {
        if ($adapter === null) {
            if ((!$adapter = $this->getAdapter()) instanceof Adapter\AdapterInterface) {
                throw new \RuntimeException("The authentication service requires 
                    an object of type \Cube\Authentication\Adapter\AdapterInterface.");
            }
        }

        $result = $adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write(
                $result->getIdentity());
        }

        return $result;
    }

    /**
     *
     * check if identity exists
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     *
     * get identity
     *
     * @return null|mixed
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return null;
        }

        return $storage->read();
    }

    /**
     *
     * clear identity
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }

}

