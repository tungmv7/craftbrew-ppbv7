<?php

/**
 *
 * PHP Pro Bid $Id$ G2BE1NZUwwCBxPobPey6jUrN/jDySFbBv8NO4Gf+HIE=
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
    Cube\Paginator,
    Cube\Db\Select,
    Ppb\Service,
    Ppb\Db\Table\Row\Category as CategoryModel,
    Ppb\Db\Table\Row\User as UserModel,
    Listings\Form;

class Browse extends AbstractAction
{

    /**
     *
     * listings service
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings;

    /**
     *
     * categories table service
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * selected category object
     *
     * @var \Ppb\Db\Table\Row\Category
     */
    protected $_category;

    public function init()
    {
        $this->_listings = new Service\Listings();
        $this->_categories = new Service\Table\Relational\Categories();

        $parentId = $this->getRequest()->getParam('parent_id');
        $slug = $this->getRequest()->getParam('category_slug');

        if (!$parentId && $slug) {
            $this->_category = $this->_categories->findBy('slug', $slug);

            if (count($this->_category) > 0) {
                $this->getRequest()->setParam('parent_id', $this->_category->getData('id'));
            }
        }
        else if ($parentId) {
            $this->_category = $this->_categories->findBy('id', $parentId);
        }
    }

    public function Index()
    {
        $select = $this->_listings->select(Service\Listings::SELECT_LISTINGS, $this->getRequest());

        $storeId = $this->getRequest()->getParam('store_id');
        $store = null;

        $usersService = new Service\Users();
        if ($slug = $this->getRequest()->getParam('store_slug')) {
            $store = $usersService->findBy('store_slug', $slug);
        }
        else if ($storeId) {
            $store = $usersService->findBy('id', $storeId);
        }

        $basicSearchForm = new Form\Search(array('basic', 'item'), null, $store, $select);
        $basicSearchForm->setData(
            $this->getRequest()->getParams())
            ->generateBasicForm();

        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($select, $this->_listings->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $itemsPerPage = $this->getRequest()->getParam('limit');

        if (!$itemsPerPage) {
            $itemsPerPage = 20;
        }

        $paginator->setPageRange(5)
            ->setItemCountPerPage($itemsPerPage)
            ->setCurrentPageNumber($pageNumber);

        $show = $this->getRequest()->getParam('show');

        $store = null;

        $view = Front::getInstance()->getBootstrap()->getResource('view');
        $browsePageTitle = $view->browsePageTitle();

        // default generated meta tags
        $metaTitle = $metaDescription = $browsePageTitle;

        if ($this->_category instanceof CategoryModel) {
            if ($categoryMetaTitle = $this->_category->getData('meta_title')) {
                $metaTitle = $categoryMetaTitle;
            }
            if ($categoryMetaDescription = $this->_category->getData('meta_description')) {
                $metaDescription = $categoryMetaDescription;
            }
        }

        if ($show == 'store') {
            $users = new Service\Users();

            if ($userId = $this->getRequest()->getParam('user_id')) {
                $store = $users->findBy('id', $userId);
            }
            else if ($slug = $this->getRequest()->getParam('store_slug')) {
                $store = $users->findBy('store_slug', $slug);
            }

            $showStore = false;
            if (count($store) > 0) {
                if ($store->storeStatus(true)) {
                    $showStore = true;

                    $storeSettings = $store->getStoreSettings();
                    $metaTitle = $store->storeName();
                    if (!empty($storeSettings['store_meta_description'])) {
                        $metaDescription = $storeSettings['store_meta_description'];
                    }

                    $view->setViewFileName('store.phtml');
                }
            }
            if (!$showStore) {
                // if the store is not active, forward to the not found page
                $this->_helper->redirector()->redirect('not-found', 'error', null, array());
            }
        }

        // META TAGS
        $view->headTitle()->prepend(strip_tags($metaTitle));
        $view->headMeta()->setName('description', strip_tags($metaDescription));


        return array(
            'paginator'       => $paginator,
            'parentId'        => $this->getRequest()->getParam('parent_id'),
            'page'            => $this->getRequest()->getParam('page'),
            'messages'        => $this->_flashMessenger->getMessages(),
            'params'          => $this->getRequest()->getParams(),
            'itemsPerPage'    => $itemsPerPage,
            'browsePageTitle' => $browsePageTitle,
            'store'           => $store,
            'basicSearchForm' => $basicSearchForm,
        );
    }

    public function Listings()
    {
        $select = $this->_listings->select(Service\Listings::SELECT_LISTINGS, $this->getRequest());

        $select->limit(
            $this->getRequest()->getParam('limit', 4)
        );

        $class = $this->getRequest()->getParam('class', 'grid');

        $carousel = ($this->getRequest()->getParam('carousel')) ? true : false;

        return array(
            'listings' => $this->_listings->fetchAll($select),
            'class'    => $class,
            'carousel' => $carousel,
            'params'   => $this->getRequest()->getParams(),
        );
    }

    public function RecentlyViewed()
    {
        $bootstrap = Front::getInstance()->getBootstrap();
        $session = $bootstrap->getResource('session');

        $userToken = strval($session->getCookie(UserModel::USER_TOKEN));
        $userId = (!empty($this->_user['id'])) ? $this->_user['id'] : null;

        $select = $this->_listings->select(Service\Listings::SELECT_LISTINGS);

        $select->join(array('rvl' => 'recently_viewed_listings'), "rvl.listing_id = l.id", 'rvl.id AS recently_viewed_listings_id')
            ->reset(Select::ORDER)
            ->order('IF(rvl.updated_at is null, rvl.created_at, rvl.updated_at) DESC')
            ->limit(
                $this->getRequest()->getParam('recently-viewed-limit', 6)
            )
            ->group('l.id');

        if ($userId !== null) {
            $select->where('rvl.user_token = "' . $userToken . '" OR rvl.user_id = "' . $userId . '"');
        }
        else {
            $select->where('rvl.user_token = ?', $userToken);
        }

        $class = $this->getRequest()->getParam('class', 'grid');

        return array(
            'listings' => $this->_listings->fetchAll($select),
            'class'    => $class,
        );
    }

    public function FavoriteStore()
    {
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $users = new Service\Users();
        $store = $users->findBy('id', $this->getRequest()->getParam('id'));

        $this->_flashMessenger->setMessage(array(
            'msg'   => $store->isFavoriteStore($this->_user['id']) ?
                    $this->_('The store has been removed from your favorites list.') :
                    $this->_('The store has been added to your favorites list.'),
            'class' => 'alert-success',
        ));

        $store->processFavoriteStore($this->_user['id']);

        $this->_helper->redirector()->gotoUrl($view->url($store->storeLink()));
    }

}

