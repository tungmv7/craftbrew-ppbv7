<?php

/**
 * when using the module separately, copy the contents of App\Bootstrap here
 */

namespace Home;

use App\Bootstrap as AppBootstrap,
    Ppb\Service;

class Bootstrap extends AppBootstrap
{

    protected function _initUser()
    {
        $this->bootstrap('settings');
        $this->bootstrap('authentication');

        if (isset($this->_user['id'])) {
            $usersService = new Service\Users();

            $user = $usersService->findBy('id', $this->_user['id'], true);

            if (count($user) > 0) {
                $this->_role = $user->getRole();
                $user['role'] = $this->_role;

                return $user;
            }
        }

        return null;
    }

    protected function _initAcl()
    {
//        $front = $this->getResource('FrontController');

        $this->_acl = new Model\Acl();

//        $front->registerPlugin(new Controller\Plugin\Acl($this->_acl, $this->_role));

//        echo "<pre>";
//        var_dump($front); exit;

        $view = $this->getResource('view');

//        var_dump($view->getLayoutsPath()); exit;

        $view->navigation()
            ->setAcl($this->_acl)
            ->setRole($this->_role);
    }

    protected function _initPlugins()
    {
//        $front = $this->getResource('FrontController');

//        $front->registerPlugin(new Controller\Plugin\ListingExistsCheck())
//            ->registerPlugin(new Controller\Plugin\UserVerificationCheck());

//        $this->_registerModuleControllerPlugins('Listings');
    }

    protected function _initListingViewHelpers()
    {
//        $this->_registerModuleViewHelpers('Home');
    }

}

