<?php

/**
 *
 * PHP Pro Bid $Id$ WVI35HInsKQmyTZiPo8P/M5hIgy1J6VVRg8Wq+iwBK4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * make offer ranges view helper class
 */

namespace Listings\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Service;

class BrowsePageTitle extends AbstractHelper
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
     * class constructor
     */
    public function __construct()
    {
        $this->setCategories();
    }

    /**
     *
     * get categories table service
     *
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        return $this->_categories;
    }

    /**
     *
     * set categories table service
     *
     * @param \Ppb\Service\Table\Relational\Categories $categories
     *
     * @return \Listings\View\Helper\BrowsePageTitle
     */
    public function setCategories(Service\Table\Relational\Categories $categories = null)
    {
        if (!$categories instanceof Service\Table\Relational\Categories) {
            $categories = new Service\Table\Relational\Categories();
        }

        $this->_categories = $categories;

        return $this;
    }

    /**
     *
     * browse page title view helper
     *
     * @return string
     */
    public function browsePageTitle()
    {
        $output = null;

        $request = Front::getInstance()->getRequest();

        $show = $request->getParam('show');

        if ($show == 'store') {
            $show = null;
        }

        $translate = $this->getTranslate();
        $showKeyword = $translate->_('Listings');

        $listingTypes = array_filter((array)$request->getParam('listing_type'));
        if (count($listingTypes) == 1) {
            if (in_array('auction', $listingTypes)) {
                $showKeyword = $translate->_('Auctions');
            }
            else if (in_array('product', $listingTypes)) {
                $showKeyword = $translate->_('Products');
            }
        }

        if ($keywords = $request->getParam('keywords')) {
            $output .= $translate->_('Search') . ' "' . $keywords . '" ';
        }

        if (empty($output)) {
            switch ($show) {
                case 'featured':
                    $output .= sprintf($translate->_('Featured %s'), $showKeyword);
                    break;
                case 'recent':
                    $output .= sprintf($translate->_('Recent %s'), $showKeyword);
                    break;
                case 'ending':
                    $output .= sprintf($translate->_('Ending Soon %s'), $showKeyword);
                    break;
                case 'popular':
                    $output .= sprintf($translate->_('Popular %s'), $showKeyword);
                    break;
                case 'other-items':
                    $output .= $translate->_($showKeyword);
                    $username = $request->getParam('username');
                    if (!empty($username)) {
                        $output .= ' ' . sprintf(
                                $translate->_("from '%s'"),
                                $username);
                    }
                    break;
                case 'discounted':
                    $output .= sprintf($translate->_('Discounted %s'), $showKeyword);
                    break;
                default:
                    $output .= sprintf($translate->_('Browse %s'), $showKeyword);
                    break;
            }
        }

        if ($parentId = $request->getParam('parent_id')) {
            $view = $this->getView();
            $breadcrumbs = array();
            foreach ($this->_categories->getBreadcrumbs($parentId) as $key => $value) {
                $url = $view->url(array('category_name' => $value, 'parent_id' => $key), null, true, array('category_slug', 'page', 'submit'));
                $breadcrumbs[] = '<a href="' . $url . '">' . $value . '</a>';
            }

            $breadcrumbs = implode(' > ', $breadcrumbs);
            if (!empty($output)) {
                $output .= ' ' . $translate->_('in') . ' ' . $breadcrumbs;
            }
            else {
                $output .= sprintf($translate->_('%s from'), $showKeyword) . ' ' . $breadcrumbs;
            }
        }

        return $output;
    }

}

