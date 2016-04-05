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

namespace Cube\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Cube\Session;

/**
 * csrf (cross site request forgery) form element generator class
 *
 * Class Csrf
 *
 * @package Cube\Form\Element
 */
class Csrf extends Element
{

    const SESSION_NAMESPACE = 'Csrf';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'csrf';

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
    public function __construct($name = 'csrf')
    {
        parent::__construct($this->_element, $name);

        $this->addValidator('Csrf')
            ->setSession()
            ->setHidden(true);
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

        if (!$session instanceof Session) {
            $session = new Session();
            $session->setNamespace(self::SESSION_NAMESPACE);
        }

        $this->_session = $session;

        return $this;
    }

    /**
     *
     * create a csrf token for the csrf form element
     *
     * @return string
     */
    public function getToken()
    {
        return sha1(uniqid(rand(), true));
    }

    /**
     *
     * render element
     *
     * @return string
     */
    public function render()
    {
        $value = $this->getToken();

        $variable = array_filter((array)$this->_session->get($this->_name));

        array_push($variable, $value);

        $this->_session->set($this->_name, $variable);

        return '<input type="hidden" name="' . $this->_name . '" '
        . ((!empty($value)) ? 'value="' . $value . '" ' : '')
        . $this->_endTag;
    }

}

