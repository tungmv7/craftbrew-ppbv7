<?php

/**
 *
 * PHP Pro Bid $Id$ vRSKr4fljMMjoBeOcb2gFvKlyxdGOkXBJfuA08HMY3s=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.3
 */
/**
 * categories browse custom form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front;

class CategoriesBrowse extends Element
{

    const ACTIVE_CATEGORY = 'active-category';
    const STORES_CATEGORIES = 'stores-categories';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'CategoriesBrowse';

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * active request
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);

        $frontController = Front::getInstance();

        $this->_view = $frontController->getBootstrap()->getResource('view');
        $this->_request = $frontController->getRequest();
    }

    public function render()
    {
        $output = null;

        $translate = $this->getTranslate();
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        $activeCategory = (isset($this->_attributes[self::ACTIVE_CATEGORY])) ? $this->_attributes[self::ACTIVE_CATEGORY] : null;
        $storesCategories = (isset($this->_attributes[self::STORES_CATEGORIES])) ? $this->_attributes[self::STORES_CATEGORIES] : false;

        if ($activeCategory !== null) {
            $output .= '<div class="category-breadcrumbs">';
            $breadcrumbs = array();

            foreach ($activeCategory as $key => $value) {
                $breadcrumbs[] = '<a href="' . $this->_view->url(array('category_name' => $value, 'parent_id' => $key), null, true,
                        array('category_slug', 'page', 'submit')) . '">' . $translate->_($value) . '</a> ';
            }
            $output .= implode(' > ', $breadcrumbs)
                . '[ <a href="' . $this->_view->url(null, null, true,
                    array('category_slug', 'category_name', 'parent_id', 'page', 'submit')) . '">' . $translate->_('Reset') . '</a> ]'
                . '</div>';
        }

        $params = $this->_request->getParams(
            array('parent_id', 'page', 'limit', 'submit', 'category_slug', 'category_name', 'controller', 'action')
        );

        $params = array_filter(
            $params, function (&$element) {
            if (is_array($element)) {
                return array_filter($element) ? true : false;
            }

            return (!empty($element));
        });

        /** @var \Ppb\Db\Table\Row\Category $category */
        foreach ($this->_customData['rowset'] as $category) {
            $counter = $category->getCounter();

            if ($counter > 0 || !$settings['hide_empty_categories'] || count($params) > 0 || $storesCategories) {
                $url = $this->_view->url(array('category_name' => $category['name'], 'parent_id' => $category['id']), null, true, array('category_slug', 'page', 'submit'));
                $output .= '<div><a href="' . $url . '">'
                    . $translate->_($category['name'])
                    . (($settings['category_counters'] && !count($params) && !$storesCategories) ? ' (' . $counter . ')' : '')
                    . '</a></div>';

            }
        }

        $output .= '<input type="hidden" name="' . $this->_name . '" value="' . $this->getValue() . '" '
            . $this->_endTag;

        return $output;
    }

}

