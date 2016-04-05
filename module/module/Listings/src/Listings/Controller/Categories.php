<?php

/**
 *
 * PHP Pro Bid $Id$ BOlDQIqCKNAafbGqCKLCNrniNlkGfeHFKSJ2QAlYy+o=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * categories controller
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service;

class Categories extends AbstractAction
{

    /**
     *
     * categories service object
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    public function init()
    {
        $this->_categories = new Service\Table\Relational\Categories();

        $parentId = $this->getRequest()->getParam('parent_id');
        $slug = $this->getRequest()->getParam('category_slug');

        if (!$parentId && $slug) {
            $category = $this->_categories->findBy('slug', $slug);

            if (count($category) > 0) {
                $this->getRequest()->setParam('parent_id', $category->getData('id'));
            }
        }
    }

    public function Browse()
    {
        $parentId = $this->getRequest()->getParam('parent_id');

        $headline = null;

        $translate = $this->getTranslate();

        // moved to meta tags controller plugin
        $htmlHeader = null;
        if ($parentId) {
            $category = $this->_categories->findBy('id', $parentId);
            if ($category !== null) {
                $htmlHeader = $category->getData('html_header');
            }

            $breadcrumbs = $this->_categories->getBreadcrumbs($parentId);
            $headline = implode(' > ', array_values($breadcrumbs));
        }
        else {
            $headline = $this->_('All Categories');
        }

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        // META TAGS
        $view->headTitle()->prepend($headline);
        $view->headMeta()->setName('description', sprintf($translate->_('Browse Categories - %s'), $headline));

        $select = $this->_categories->getTable()->select()
            ->where('user_id is null')
            ->order(array('order_id ASC', 'name ASC'));

        if ($parentId) {
            $select->where('parent_id = ?', $parentId);
        }
        else {
            $select->where('parent_id is null');
        }

        return array(
            'headline'   => $headline,
            'parentId'   => $parentId,
            'htmlHeader' => $htmlHeader,
            'categories' => $this->_categories->fetchAll($select),
        );
    }
}

