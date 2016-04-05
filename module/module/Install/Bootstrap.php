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

namespace Install;

use Cube\Application\Bootstrap as ApplicationBootstrap,
    Cube\Authentication\Authentication,
    Ppb\View\Helper,
    Ppb\Service;

class Bootstrap extends ApplicationBootstrap
{
    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings = array();

    /**
     *
     * database connection flag
     *
     * @var bool
     */
    protected $_connected = false;

    /**
     *
     * acl object
     *
     * @var \Cube\Permissions\Acl
     */
    protected $_acl;

    /**
     *
     * acl role
     *
     * @var string|\Cube\Permissions\RoleInterface
     */
    protected $_role = 'Guest';

    /**
     *
     * current logged in user data (storage)
     *
     * @var null|array|\Ppb\Db\Table\Row\User
     */
    protected $_user;

    protected function _initConnected()
    {
        $this->bootstrap('db');
        $db = $this->getResource('db');

        if ($db instanceof \Cube\Db\Adapter\AbstractAdapter) {
            /** @var \Cube\Db\Adapter\PDO\Mysql $db */
            try {
                $this->_connected = $db->canConnect();
            } catch (\Exception $e) {

            }
        }

        return $this->_connected;
    }

    protected function _initSettings()
    {
        if ($this->_connected === true) {
            $settingsService = new Service\Settings();

            try {
                $this->_settings = $settingsService->get();
            } catch (\Exception $e) {
            }
        }

        return $this->_settings;
    }

    protected function _initAuthentication()
    {
        $authentication = Authentication::getInstance();

        if ($authentication->hasIdentity()) {
            $storage = $authentication->getStorage()->read();

            if ($storage['role'] == 'Admin') {
                $this->_role = $storage['role'];
                $this->_user = $storage;
            }
        }

        $view = $this->getResource('view');
        $view->loggedInUser = $this->_user;
    }

    protected function _initUser()
    {
        if (isset($this->_user['id'])) {
            $usersService = new Service\Users();

            $user = $usersService->findBy('id', $this->_user['id']);

            if (count($user) > 0) {
                if ($user['role'] == 'Admin') {
                    $this->_role = $user['role'];
                    return $user;
                }
            }
        }

        return null;
    }

    protected function _initAcl()
    {
        $front = $this->getResource('FrontController');

        $this->_acl = new Model\Acl();

        $front->registerPlugin(
            new Controller\Plugin\Acl($this->_acl, $this->_role));

        $view = $this->getResource('view');
        $view->navigation()->setAcl($this->_acl)
            ->setRole($this->_role);
    }

    protected function _initControllerPlugins()
    {
        $front = $this->getResource('FrontController');

        $front->registerPlugin(
            new Controller\Plugin\InstallerEnabled());
    }

    protected function _initModRewrite()
    {
        $modRewriteSetting = (isset($this->_settings['mod_rewrite_urls'])) ? $this->_settings['mod_rewrite_urls'] : 0;

        if (!\Ppb\Utility::checkModRewrite() && !$modRewriteSetting) {
            \Ppb\Utility::activateStandardRouter();
        }
    }

    protected function _initViewHelpers()
    {
        $dateFormat = '%m/%d/%Y %H:%M:%S';
        $sitePath = (!empty($this->_settings['site_path'])) ? $this->_settings['site_path'] : '/';

        $view = $this->getResource('view');
        $view->setHelper('request', new Helper\Request())
            ->setHelper('url', new Helper\Url($sitePath))
            ->setHelper('date', new Helper\Date($dateFormat))
            ->setHelper('liveTime', new Helper\LiveTime($dateFormat))
            ->setHelper('thumbnail', new Helper\Thumbnail());

        $view->themesFolder = \Ppb\Utility::getFolder('themes');

        $view->script()
            ->addHeaderCode('<!--[if lt IE 9]> <script type="text/javascript" src="' . $view->baseUrl . '/js/html5shiv.min.js"></script><![endif]-->')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/css/bootstrap.min.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/css/font-awesome.min.css" media="screen" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/css/style.css" rel="stylesheet" type="text/css">')
            ->addHeaderCode('<!--[if lt IE 9]><link href="' . $view->baseUrl . '/css/style.ie.css" media="all" rel="stylesheet" type="text/css"><![endif]-->')
            ->addHeaderCode('<link href="' . $view->baseUrl . '/img/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">');

        /* add javascript plugins */
        $view->script()->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/jquery.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/bootstrap.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/masonry.pkgd.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/bootbox.min.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/cookie.js"></script>')
            ->addBodyCode('<script type="text/javascript" src="' . $view->baseUrl . '/js/global.js"></script>')
            ->addBodyCode('<!--[if lt IE 9]> <script type="text/javascript" src="' . $view->baseUrl . '/js/respond.min.js"></script><![endif]-->')
            ->addBodyCode('<!--[if lt IE 10]> <script type="text/javascript" src="' . $view->baseUrl . '/js/placeholders.jquery.min.js"></script><![endif]-->');
    }
}