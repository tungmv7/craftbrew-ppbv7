<?php

/**
 *
 * PHP Pro Bid $Id$ 3bARaqYd4VApuBcyngYqo0oK7TuJhMrn6rVBI99qSjY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service,
    Listings\Form;

class Search extends AbstractAction
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
     * search form action
     *
     * @var string
     */
    protected $_formAction;

    public function init()
    {
        $this->_view = Front::getInstance()->getBootstrap()->getResource('view');
        $this->_formAction = $this->_view->url(
            array('module' => 'listings', 'controller' => 'browse', 'action' => 'index'));
    }

    public function Advanced()
    {
        $form = new Form\Search(array('advanced', 'item'));

        $params = $this->getRequest()->getParams();
        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {
            //redirect
            $this->_helper->redirector()->redirect('index', 'browse', 'listings', $params);
        }

        return array(
            'form'     => $form,
            'headline' => $form->getTitle(),
        );
    }

    public function Basic()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $listingsSelect = $this->getRequest()->getParam('listings_select');
        $store = null;

        if ($storeId) {
            $usersService = new Service\Users();
            $store = $usersService->findBy('id', $storeId);
        }

        $form = new Form\Search(array('basic', 'item'), null, $store, $listingsSelect);
        $form->setData(
            $this->getRequest()->getParams())
            ->generateBasicForm();

        return array(
            'form' => $form,
        );
    }

    public function Stores()
    {
        $form = new Form\Search(array('stores'));
        $form->setData(
            $this->getRequest()->getParams())
            ->generateBasicForm();

        return array(
            'form' => $form,
        );
    }

}

