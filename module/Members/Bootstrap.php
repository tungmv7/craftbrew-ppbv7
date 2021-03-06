<?php

/**
 * when using the module separately, copy the contents of App\Bootstrap here
 */

namespace Members;

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

    /**
     * 
     * method that initializes the _members.phtml sub-layout
     */
    protected function _initSubLayout()
    {
        $view = $this->getResource('view');
        $view->set('isMembersModule', true);
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
        $this->_registerModuleControllerPlugins('Listings');
    }

    protected function _initMembersViewHelpers()
    {
        $view = $this->getResource('view');
        $view->setHelper('offerRanges', new \Listings\View\Helper\OfferRanges());

        $this->_registerModuleViewHelpers('Members');
    }

}

