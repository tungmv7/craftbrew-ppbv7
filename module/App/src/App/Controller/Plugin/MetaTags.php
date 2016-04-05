<?php

/**
 *
 * PHP Pro Bid $Id$ 4zoHcSNM8Y1loxdOly8BV/muBUK1uYELBNMtlAFKbro=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * this plugin will populate automatically the meta tags view helpers (headTitle and headMeta)
 */

namespace App\Controller\Plugin;

use Cube\Controller\Plugin\AbstractPlugin,
    Cube\Controller\Front,
    Cube\Navigation,
    Cube\View\Helper\HeadTitle;
use Cube\ModuleManager;

class MetaTags extends AbstractPlugin
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * navigation container
     *
     * @var \Cube\Navigation\AbstractContainer
     */
    protected $_navigation;

    public function __construct()
    {
        $bootstrap = Front::getInstance()->getBootstrap();
        $this->_view = $bootstrap->getResource('view');
        $this->_settings = $bootstrap->getResource('settings');
        $this->_navigation = $bootstrap->getResource('navigation');
    }

    /**
     *
     * set up meta tags automatically based on the active request
     * this plugin will only set default/generic meta tags;
     * custom meta tags will be set in the appropriate controller actions:
     * - Listings / Categories / Browse
     * - Listings / Browse / Index (search, categories, store listings)
     * - Listings / Listing / Details
     * - App / Sections / View
     *
     * @return void
     */
    public function preDispatcher()
    {
        $request = $this->getRequest();
        $module = $request->getModule();

        $this->_view->headMeta()->setCharset('utf-8');

        if ($module == 'Admin') {
            $this->_view->headTitle('PHP Pro Bid - Admin Area');
            $this->_view->headMeta()->setName('robots', 'nofollow');
        }
        else {
            $controller = $request->getController();
            $action = $request->getAction();

            $metaTitle = $this->_settings['sitename'];
            if ($module == 'App' && $controller == 'Index' && $action == 'Index' && !empty($this->_settings['meta_title'])) {
                $metaTitle = $this->_settings['meta_title'];
                if (!empty($this->_settings['meta_description'])) {
                    $this->_view->headMeta()->setName('description', $this->_settings['meta_description']);
                }
            }
            $this->_view->headTitle($metaTitle);

            if (!empty($this->_settings['meta_data'])) {
                $metaData = \Ppb\Utility::unserialize($this->_settings['meta_data']);
                if (isset($metaData['key'])) {
                    foreach ($metaData['key'] as $key => $value) {
                        if (!empty($value)) {
                            $this->_view->headMeta()->appendName($value, $metaData['value'][$key]);
                        }
                    }
                }
            }
        }

        if ($this->_navigation instanceof Navigation) {
            /** @var \Cube\View\Helper\Navigation $navigationHelper */
            $navigationHelper = $this->_view->navigation()->setMinDepth(1);
            $breadcrumbs = $navigationHelper->getBreadcrumbs();

            if (count($breadcrumbs) > 0) {
                $this->_view->headTitle()->clearContainer();

                foreach ($breadcrumbs as $page) {
                    $this->_view->headTitle($page->label);
                }

                $this->_view->headTitle($this->_settings['sitename']);
            }
            else {
//                $this->_view->headTitle($controller);
            }
        }
    }

    public function preDispatch()
    {
        $response = $this->getResponse();
        if ($response->getResponseCode() == 404) {
            $this->_view->headTitle('Page Not Found', HeadTitle::SET);
        }
    }
}

