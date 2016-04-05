<?php
/**
 * Created by PhpStorm.
 * User: tungmangvien
 * Date: 4/4/16
 * Time: 11:00 PM
 */

namespace Home\Controller;

use Ppb\Controller\Action\AbstractAction,
    Ppb\Service,
    Cube\Controller\Front,
    Cube\Feed,
    Cube\Controller\Request,
    Cube\View;

class Listing extends AbstractAction
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
}