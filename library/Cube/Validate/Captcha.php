<?php

/**
 *
 * Cube Framework $Id$ 0ODEEX9wMJbafm7LFeIMBY5lYC75ubCX4eimHkILVos=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.2
 */
/**
 * captcha element validator class
 */

namespace Cube\Validate;

use Cube\Controller\Front,
    Cube\Session;

class Captcha extends AbstractValidate
{

    const SESSION_NAMESPACE = 'Captcha';

    protected $_message = "The captcha code is not valid.";

    /**
     *
     * session object
     *
     * @var \Cube\Session
     */
    protected $_session;

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
    public function setSession(Session $session = null)
    {
        if ($session === null) {
            $session = Front::getInstance()->getBootstrap()->getResource('session');
        }

        if (!($session instanceof Session)) {
            $session = new Session();
            $session->setNamespace(self::SESSION_NAMESPACE);
        }

        $this->_session = $session;

        return $this;
    }

    /**
     *
     * checks for a valid captcha code, and resets the code if valid
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $name = $this->getName();
        $value = $this->getValue();

        $codes = (array) $this->getSession()->get($name);

        if (($key = array_search($value, $codes)) !== false) {
            unset($codes[$key]);
            $this->getSession()->set($name, $codes);
            return true;
        }
        else {
            return false;
        }
    }

}

