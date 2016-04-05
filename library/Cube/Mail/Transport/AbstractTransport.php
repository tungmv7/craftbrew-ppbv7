<?php

/**
 *
 * Cube Framework $Id$ 8jfKe/ZCxkyc3VQWsE7zWKlSNZvFPc/buhBQ8KqIq68=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * mailer transport abstract class
 */

namespace Cube\Mail\Transport;

use Cube\Mail;

abstract class AbstractTransport
{

    /**
     *
     * mail object
     *
     * @var \Cube\Mail
     */
    protected $_mail;

    /**
     *
     * options array
     *
     * @var array
     */
    protected $_options;

    /**
     *
     * class constructor
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->setOptions($options);
    }

    /**
     *
     * get options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     *
     * set options array
     * override to accommodate smtp settings
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_options = (array)$options;

        foreach ($this->_options as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }

        return $this;
    }

    /**
     *
     * get mail object
     *
     * @throws \RuntimeException
     * @return \Cube\Mail
     */
    public function getMail()
    {
        if (!$this->_mail instanceof Mail) {
            throw new \RuntimeException("An object of type \Cube\Mail 
                is required by the mail transport class.");
        }

        return $this->_mail;
    }

    /**
     *
     * set mail object
     *
     * @param \Cube\Mail $mail
     *
     * @return \Cube\Mail\Transport\AbstractTransport
     */
    public function setMail(Mail $mail)
    {
        $this->_mail = $mail;

        return $this;
    }

    abstract public function send();
}

