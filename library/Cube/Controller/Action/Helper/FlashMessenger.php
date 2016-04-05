<?php

/**
 * 
 * Cube Framework $Id$ OycVVEXEO0aaKLIfs5gtwtr1CudkpucBxTBEvrD+VLA= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * flash messenger action helper
 */

namespace Cube\Controller\Action\Helper;

use Cube\Controller\Front,
    Cube\Session;

class FlashMessenger extends AbstractHelper
{

    const SESSION_NAMESPACE = 'FlashMessenger';
    const MESSAGES_NAMESPACE = 'Messages';

    /**
     *
     * name of session variable
     * 
     * @var string
     */
    protected $_name = null;

    /**
     *
     * array of messages held by the flash messenger 
     * 
     * @var array
     */
    protected $_messages = array();

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
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
        $this->setSession();
    }

    /**
     * 
     * get session variable name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 
     * set session variable name
     * 
     * @param string $name
     * @return \Cube\Controller\Action\Helper\FlashMessenger
     */
    public function setName($name = null)
    {
        if ($name === null) {
            $name = self::MESSAGES_NAMESPACE;
        }

        $this->_name = $name;

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
     * @return \Cube\Controller\Action\Helper\FlashMessenger
     */
    public function setSession(Session $session = null)
    {
        if ($session === null) {
            $session = Front::getInstance()->getBootstrap()->getResource('session');
        }

        if (!$session instanceof Session) {
            $session = new Session();
            $session->setNamespace(self::SESSION_NAMESPACE);
        }

        $this->_session = $session;

        return $this;
    }

    /**
     * 
     * add a new message to the flash messenger action helper
     * 
     * @param mixed $message
     * @return \Cube\Controller\Action\Helper\FlashMessenger
     */
    public function setMessage($message)
    {
        $this->_messages = $this->getMessages();
        array_push($this->_messages, $message);

        $this->_session->set($this->_name, $this->_messages);

        return $this;
    }

    /**
     * 
     * get all messages saved in the current flash messenger helper
     * 
     * @return array
     */
    public function getMessages()
    {
        $this->_messages = (array) $this->_session->get($this->_name);
        $this->_session->unregister($this->_name);

        return $this->_messages;
    }

}

