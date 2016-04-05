<?php

/**
 *
 * PHP Pro Bid $Id$ rgcGymO7MuiA4HtwZBwQemKJ79IbYoCamUrsIVQZEWQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * authentication adapter
 */

namespace Ppb\Authentication;

use Cube\Authentication\Adapter\AdapterInterface,
    Cube\Authentication\Result as AuthenticationResult,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Controller\Front,
    Ppb\Service\Users as UsersService;

class Adapter implements AdapterInterface
{

    /**
     *
     * whether to check old v6.x passwords
     */
    const V6_HASHES = true;

    /**
     *
     * user id
     *
     * @var int
     */
    protected $_id = null;
    /**
     *
     * username
     *
     * @var string
     */
    protected $_username = null;

    /**
     *
     * password
     *
     * @var string
     */
    protected $_password = null;

    /**
     *
     * email address
     *
     * @var string
     */
    protected $_email = null;

    /**
     *
     * allowed roles
     *
     * @var array
     */
    protected $_allowedRoles = array();

    /**
     *
     * denied roles
     *
     * @var array
     */
    protected $_deniedRoles = array();

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    public function __construct($params = array(), $id = null, $allowedRoles = array(), $deniedRoles = array())
    {
        if (array_key_exists('username', $params)) {
            $this->_username = $params['username'];
        }
        if (array_key_exists('password', $params)) {
            $this->_password = $params['password'];
        }
        if (array_key_exists('email', $params)) {
            $this->_email = $params['email'];
        }

        $this->_id = $id;
        $this->_allowedRoles = $allowedRoles;
        $this->_deniedRoles = $deniedRoles;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * authenticate user by username and password or if id is set, authenticate directly
     *
     * @return AuthenticationResult
     */
    public function authenticate()
    {
        $usersService = new UsersService();

        $user = null;
        if ($this->_id !== null) {
            $user = $usersService->findBy('id', $this->_id);
        }
        else if ($this->_username !== null || $this->_email !== null) {
            $user = $usersService->findBy('username', $this->_username);
            if (!$user && $this->_email !== null) {
                $user = $usersService->findBy('email', $this->_email);
            }
        }

        $success = false;
        if (count($user) > 0) {
            if ($this->_id !== null) {
                $success = true;
            }
            else if (strcmp($usersService->hashPassword($this->_password, $user['salt']), $user['password']) === 0) {
                $success = true;
            }
            else if (self::V6_HASHES && strcmp(md5(md5($this->_password) . $user['salt']), $user['password']) === 0) {
                $success = true;
            }

            if (count($this->_allowedRoles) && !array_key_exists($user['role'], $this->_allowedRoles)) {
                $success = false;
            }

            if (array_key_exists($user['role'], $this->_deniedRoles)) {
                $success = false;
            }
        }

        if ($success === true) {
            return new AuthenticationResult(true, array(
                'id'         => $user['id'],
                'username'   => $user['username'],
                'first_name' => $user['first_name'],
                'role'       => $user['role'],
            ));
        }
        else {
            return new AuthenticationResult(false, array(), array($this->getTranslate()->_('Authentication Failed')));
        }
    }

}

