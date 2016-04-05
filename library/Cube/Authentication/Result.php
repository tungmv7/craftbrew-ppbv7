<?php

/**
 * 
 * Cube Framework $Id$ P1BfF8QKvd+uIcqJGDjJ8sJObK7S1D8fgoXVYW69xvQ= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * authentication result class
 */

namespace Cube\Authentication;

class Result
{

    /**
     *
     * authentication result 
     * 
     * @var bool
     */
    protected $_valid;

    /**
     *
     * identity used
     * 
     * @var mixed
     */
    protected $_identity;

    /**
     *
     * messages to output
     * 
     * @var array
     */
    protected $_messages = array();

    /**
     * 
     * class constructor
     * 
     * @param bool $valid
     * @param mixed $identity
     * @param array $messages
     */
    public function __construct($valid, $identity, $messages = array())
    {
        $this->setValid($valid);
        $this->setIdentity($identity);
        $this->setMessages($messages);
    }

    /**
     * 
     * set valid flag
     * 
     * @param bool $valid
     */
    public function setValid($valid)
    {
        $this->_valid = (bool) $valid;
    }

    /**
     * 
     * get identity used
     * 
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * 
     * set identity used
     * 
     * @param mixed $identity
     */
    public function setIdentity($identity)
    {
        $this->_identity = $identity;
    }

    /**
     * 
     * get authentication failure messages
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * 
     * set authentication failure messages
     * 
     * @param mixed $messages
     */
    public function setMessages($messages)
    {
        $this->_messages = (array) $messages;
    }

    /**
     * 
     * check whether the authentication attempt was valid or not
     * 
     * @return bool
     */
    public function isValid()
    {
        return (bool) $this->_valid;
    }

}

