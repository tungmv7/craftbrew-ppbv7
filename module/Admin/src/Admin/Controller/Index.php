<?php

/**
 *
 * PHP Pro Bid $Id$ 5tXTQRUmBmY8Yv2BtEI+Quf8X4snXqO4F/EWyUb/NAM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Admin\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Cube\View,
    Cube\Db\Expr,
    Ppb,
    Admin\Form;

class Index extends AbstractAction
{

    public function Index()
    {
        if (strcasecmp($this->_user['role'], \Ppb\Service\Users::ADMIN_ROLE_PRIMARY) === 0) {
            $this->_helper->redirector()->redirect('index', 'settings', null, array('page' => 'site_setup'));
        }

        return array();
    }

    public function Login()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $view->setLayout('login.phtml');
        $view->headTitle()->prepend('Login');

        $loginForm = new Form\Login();

        if ($this->getRequest()->isPost()) {
            $loginForm->setData($this->getRequest()->getParams());

            $adapter = new Adapter(
                $this->getRequest()->getParams(),
                null,
                \Ppb\Service\Users::getAdminRoles()
            );

            $authentication = Authentication::getInstance();

            $result = $authentication->authenticate($adapter);

            if ($authentication->hasIdentity()) {
                $redirectUrl = $this->getRequest()->getBaseUrl() .
                    $this->getRequest()->getRequestUri();
                $this->_helper->redirector()->gotoUrl($redirectUrl);
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Invalid Login Credentials'),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'loginForm' => $loginForm,
            'messages'  => $this->_flashMessenger->getMessages(),
        );
    }

    public function Logout()
    {
        Authentication::getInstance()->clearIdentity();

        $this->_helper->redirector()->redirect('index', 'index', 'app');
    }

    public function QuickNavigation()
    {
        $this->getResponse()->setHeader('Content-Type: application/json');

        $input = $this->getRequest()->getParam('input');

        $view = new View();

        $navigation = Front::getInstance()->getBootstrap()->getResource('navigation');
        $pages = $navigation->findAllBy('label', $input, false);

        $data = array();

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($pages as $page) {
            if (!$page->hidden && !$page->filter) {
                $data[] = array(
                    'label' => $page->getLabel(),
                    'path'  => $view->url($page->getParams()),
                );
            }
        }

        $view->setContent(
            json_encode($data));

        return $view;
    }

    public function InitializeCategoryCounters()
    {
        $limit = $this->getRequest()->getParam('limit', 500);
        $offset = $this->getRequest()->getParam('offset', 0);

        $categoriesService = new Ppb\Service\Table\Relational\Categories();
        if ($offset == 0) {
            $categoriesService->resetCounters();
        }


        $listingsService = new Ppb\Service\Listings();
        $select = $listingsService->select(Ppb\Service\Listings::SELECT_LISTINGS)
            ->limit($limit, $offset)
            ->order('id ASC');


        $listings = $listingsService->fetchAll($select);

        $counter = 0;
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        foreach ($listings as $listing) {
            $counted = $listing->processCategoryCounter(true);
            if ($counted) {
                $counter++;
            }

        }

        $this->getResponse()->setHeader('Content-Type: application/json');

        $view = new View();
        $view->setContent(json_encode(array(
            'counter' => $counter,
        )));

        return $view;
    }

    public function CountListings()
    {
        $this->getResponse()->setHeader('Content-Type: application/json');

        $listingsService = new Ppb\Service\Listings();
        $select = $listingsService->select(Ppb\Service\Listings::SELECT_LISTINGS);

        $select->columns(array('nb_rows' => new Expr('count(*)')));

        $stmt = $select->query();

        $view = new View();
        $view->setContent(json_encode(array(
            'counter' => (integer)$stmt->fetchColumn('nb_rows'),
        )));

        return $view;
    }

}

