<?php

/**
 *
 * PHP Pro Bid $Id$ qfuW+b/TM3AsUKopvyG2YJzTO8HgNxMyxcjFqpAHTYc=
 *
 * @link        https://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     https://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */

namespace Admin;

use App\Bootstrap as AppBootstrap,
    Ppb\Service;

class Bootstrap extends AppBootstrap
{

    protected function _initLayout()
    {
        $this->getResource('view')->setLayoutsPath(\Ppb\Utility::getPath('themes') . '/admin');
    }

    protected function _initUser()
    {
        $this->bootstrap('settings');
        $this->bootstrap('authentication');

        if (isset($this->_user['id'])) {
            $usersService = new Service\Users();

            $user = $usersService->findBy('id', $this->_user['id']);

            if (count($user) > 0) {
                $this->_role = $user['role'];

                return $user;
            }
        }

        return null;
    }

    protected function _initAcl()
    {
        $this->bootstrap('authentication');

        $front = $this->getResource('FrontController');

        $this->_acl = new Model\Acl();

        $front->registerPlugin(
            new Controller\Plugin\Acl($this->_acl, $this->_role));

        $view = $this->getResource('view');
        $view->navigation()->setAcl($this->_acl)
            ->setRole($this->_role);
    }


    protected function _initPlugins()
    {
        $this->_registerModuleControllerPlugins('Admin');
    }

    protected function _initAdminViewHelpers()
    {
        $this->_registerModuleViewHelpers('Admin');
    }
}

