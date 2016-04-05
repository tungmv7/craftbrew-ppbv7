<?php

/**
 *
 * PHP Pro Bid $Id$ AO9tO5D8xetzZpD/pieLb6HdtUYoJGrKpjYNNmpXuMU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * members module - stores browse controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction,
    Cube\Db\Expr,
    Cube\Paginator,
    Ppb\Service;

class Stores extends AbstractAction
{

    /**
     *
     * categories service object
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * stores select object
     *
     * @var \Cube\Db\Select
     */
    protected $_select;

    public function init()
    {
        $this->_categories = new Service\Table\Relational\Categories();

        /* TODO: the below initialization should be done in the constructor of the parent class but its not working */
        $this->_users = new Service\Users();

        $keywords = $this->getRequest()->getParam('keywords');
        $parentId = $this->getRequest()->getParam('parent_id');
        $country = $this->getRequest()->getParam('country');

        $this->_select = $this->_users->getTable()->getAdapter()->select()
            ->from(array('u' => 'users'), '*')
            ->joinLeft(array('s' => 'stores_subscriptions'), 's.id = u.store_subscription_id',
                's.featured_store')
            ->where('u.active = ?', 1)
            ->where('u.approved = ?', 1)
            ->where('u.store_active = ?', 1);

        if (!empty($keywords)) {
            $params = '%' . str_replace(' ', '%', $keywords) . '%';
            $this->_select->where('u.store_name LIKE ?', $params);
        }

        if ($this->_settings['hide_empty_stores']) {
            $this->_select->joinLeft(array('l' => 'listings'), 'l.user_id = u.id', 'l.id as listing_id')
                ->where('l.list_in != ?', 'site')
                ->where('l.closed = ?', 0)
                ->where('l.deleted = ?', 0)
                ->where('l.active = ?', 1)
                ->where('l.approved = ?', 1)
                ->group('u.id');
        }

        /*
         * search by category
         */
        if ($parentId) {
            $categoriesIds = array_keys($this->_categories->getChildren($parentId, true));

            $this->_select->where('u.store_category_id IN (?)', new Expr(implode(', ', $categoriesIds)));
        }

        if ($country) {
            $this->_select->joinLeft(array('a' => 'users_address_book'), 'a.user_id = u.id')
                ->where('a.is_primary = ?', 1)
                ->where('a.address REGEXP \'"country";s:[[:digit:]]+:"' . intval($country) . '"\'');
        }

    }

    public function Index()
    {
        $select = clone $this->_select;

        $select->where('(s.featured_store = ? OR s.featured_store IS NULL)', 0)
            ->where('u.store_subscription_id IS NOT NULL')
            ->order(new Expr('rand()'))
            ->limit(10);

        return array(
            'headline'        => $this->_('Stores'),
            'isMembersModule' => false,
            'categories'      => $this->_categories->fetchAll(
                    $this->_categories->getTable()->select()
                        ->where('parent_id is null')
                        ->where('user_id is null')
                        ->order(array('order_id ASC', 'name ASC'))
                ),
            'stores'          => $this->_users->fetchAll($select),
        );
    }

    public function Browse()
    {
        $paginator = new Paginator(
            new Paginator\Adapter\DbTableSelect($this->_select, $this->_users->getTable()));

        $pageNumber = $this->getRequest()->getParam('page');
        $itemsPerPage = $this->getRequest()->getParam('limit');

        if (!$itemsPerPage) {
            $itemsPerPage = 20;
        }

        $paginator->setPageRange(5)
            ->setItemCountPerPage($itemsPerPage)
            ->setCurrentPageNumber($pageNumber);

        return array(
            'paginator'       => $paginator,
            'messages'        => $this->_flashMessenger->getMessages(),
            'params'          => $this->getRequest()->getParams(),
            'itemsPerPage'    => $itemsPerPage,
            'isMembersModule' => false,
        );
    }

    public function Featured()
    {
        $select = clone $this->_select;

        $select->where('s.featured_store = 1 OR u.store_subscription_id IS NULL')
            ->order(new Expr('rand()'))
            ->limit(8);

        return array(
            'stores'          => $this->_users->fetchAll($select),
            'isMembersModule' => false,
        );
    }
}

