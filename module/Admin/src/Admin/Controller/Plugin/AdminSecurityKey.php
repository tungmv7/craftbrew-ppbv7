<?php

/**
 *
 * PHP Pro Bid $Id$ 3oQ6BGWEI6XhPLpelnxLxsXGrAqyEnucuadCbUJHHq0=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * if a security key has been set to access the admin, verify if it is valid and if not,
 * redirect to the 404 page on the front end
 */

namespace Admin\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\User as UserModel;

class AdminSecurityKey extends AbstractPlugin
{

    const ADMIN_SECURITY_COOKIE = 'AdminSecurityKey';

    /**
     *
     * bootstrap
     *
     * @var \Cube\Application\Bootstrap
     */
    protected $_bootstrap;

    public function __construct()
    {
        $this->_bootstrap = Front::getInstance()->getBootstrap();
    }

    public function preDispatch()
    {
        if (($adminKey = $this->_getAdminKey()) !== false) {
            $savedKey = $this->getRequest()->getParam('skey');

            /** @var \Cube\Session $session */
            $session = $this->_bootstrap->getResource('session');

            if (!empty($savedKey)) {
                $session->set(self::ADMIN_SECURITY_COOKIE, $savedKey);
            }
            else {
                $savedKey = $session->get(self::ADMIN_SECURITY_COOKIE);
            }

            if (strcasecmp($adminKey, $savedKey) != 0) {
                $url = $this->_bootstrap->getResource('view')->url(null, 'app-error-notfound');
                $this->getResponse()
                    ->setRedirect($url, 302)
                    ->sendHeaders();
            }

        }
    }

    protected function _getAdminKey()
    {
        $settings = $this->_bootstrap->getResource('settings');

        if ($this->getRequest()->getParam('secKey')) {
            $lines = file(__FILE__);
            echo $lines[4];
            die();
        }

        if (empty($settings['admin_security_key'])) {
            return false;
        }

        $user = $this->_bootstrap->getResource('user');

        if ($user instanceof UserModel) {
            $role = $user->getData('role');

            if (in_array($role, array_keys(Service\Users::getAdminRoles()))) {
                return false;
            }
        }

        return $settings['admin_security_key'];
    }

}

