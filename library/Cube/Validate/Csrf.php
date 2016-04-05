<?php

/**
 *
 * Cube Framework $Id$ 2am6FkcYYkiRdybY2FJvCzsHwJaNqQqXLDN5CRToG90=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Validate;

use Cube\Controller\Front,
    Cube\Session;

/**
 * csrf element validator class
 *
 * Class Csrf
 *
 * @package Cube\Validate
 */
class Csrf extends AbstractValidate
{

    const SESSION_NAMESPACE = 'Csrf';

    protected $_message = "The CSRF validation has failed.";

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
     */
    public function __construct()
    {
        $this->setSession();
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
     * @return \Cube\Validate\Csrf
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
     * checks for a valid csrf field, and resets the csrf field if valid
     *
     * @return bool          return true if the validation is successful
     */
    public function isValid()
    {
        $codes = (array)$this->_session->get($this->_name);

        if (($key = array_search($this->_value, $codes)) !== false) {
            unset($codes[$key]);
            $this->_session->set($this->_name, $codes);

            return true;
        }
        else {
            return false;
        }
    }

}

