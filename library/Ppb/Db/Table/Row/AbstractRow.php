<?php

/**
 *
 * PHP Pro Bid $Id$ rpmkiISsH5YDcD72rZWwJQTXMcw7ZB8WYEtmd0W4/RI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * abstract row class
 */

namespace Ppb\Db\Table\Row;

use Cube\Db\Table\Row\AbstractRow as CubeAbstractRow,
    Cube\Controller\Front;

class AbstractRow extends CubeAbstractRow
{

    /**
     *
     * logged in user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * get user model
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getUser()
    {
        if (!$this->_user instanceof User) {
            $user = Front::getInstance()->getBootstrap()->getResource('user');

            if ($user instanceof User) {
                $this->setUser(
                    $user);
            }
        }

        return $this->_user;
    }

    /**
     *
     * set the user model of the currently logged in user
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (!is_array($this->_settings)) {
            $this->setSettings(
                Front::getInstance()->getBootstrap()->getResource('settings'));
        }

        return $this->_settings;
    }

    /**
     *
     * set the settings array
     *
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

}

