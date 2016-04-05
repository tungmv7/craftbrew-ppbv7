<?php

/**
 *
 * PHP Pro Bid $Id$ 32ivxrCmaKvWJeQ6YMcg0BGdwkDV4COPAdp9wQwJASA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * category selector form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Ppb\Service\Table\Relational\Categories as CategoriesService;

class Category extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'category';

    /**
     *
     * base url of the application
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     *
     * categories table service
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * refresh page on select
     *
     * @var bool
     */
    protected $_refresh = true;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $translate = $this->getTranslate();

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " function displayCategoryBoxes(el, selected, option) { " . "\n"
            . "     var storeId = el.find('.btn-category').attr('data-store-id'); " . "\n"
            . "     var categoryId = el.find('.btn-category').attr('data-store-id'); " . "\n"
            . "     var refreshPage = el.find('.btn-category').attr('data-no-refresh'); " . "\n"
            . "     $.post( " . "\n"
            . "         '" . $view->url(array('module' => 'app', 'controller' => 'async', 'action' => 'select-category')) . "', " . "\n"
            . "         { " . "\n"
            . "             id: selected, " . "\n"
            . "             storeId: storeId, " . "\n"
            . "             option: option " . "\n"
            . "         }, " . "\n"
            . "         function(data) { " . "\n"
            . "             el.find('.btn-category').attr('disabled', false).html(data['category_name']); " . "\n"
            . "             el.find('.category-id').val(data['category_id']); " . "\n"
            . "             el.find('.form-boxes').html(data['output']); " . "\n"
            . "             if (!data['output'] && data['refresh'] && refreshPage != 'true') { " . "\n"
            . "                 $('body').addClass('loading'); " . "\n"
            . "                 el.closest('form').submit(); " . "\n"
            . "             } " . "\n"
            . "         },  " . "\n"
            . "         'json' " . "\n"
            . "     ); " . "\n"
            . " } " . "\n"
            . " $(document).on('click', '.btn-category', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     $(this).attr('disabled', true).text('" . $translate->_('Please wait ...') . "'); " . "\n"
            . "     var el = $(this).closest('.form-group'); " . "\n"
            . "     el.find('.btn-category-cancel').show(); " . "\n"
            . "     var selected = el.find('.category-id').val(); " . "\n"
            . "     displayCategoryBoxes(el, selected, 'change'); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.btn-category-cancel', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var el = $(this).closest('.form-group'); " . "\n"
            . "     el.find('.btn-category').attr('disabled', true).text('" . $translate->_('Please wait ...') . "'); " . "\n"
            . "     el.find('.category-id').val(''); " . "\n"
            . "     el.find('.btn-category-cancel').hide(); " . "\n"
            . "     displayCategoryBoxes(el, '', 'reset'); " . "\n"
            . " }); " . "\n"
            . " $(document).on('change', '.category-selector', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     $(this).closest('.form-group').find('.btn-category').attr('disabled', true).text('" . $translate->_('Please wait ...') . "'); " . "\n"
            . "     var el = $(this).closest('.form-group'); " . "\n"
            . "     var selected = $(this).val(); " . "\n"
            . "     displayCategoryBoxes(el, selected, 'select'); " . "\n"
            . " }); " . "\n"
            . "</script>");
    }

    /**
     *
     * get categories table service
     *
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        if (!$this->_categories instanceof CategoriesService) {
            $this->setCategories(
                new CategoriesService()
            );
        }

        return $this->_categories;
    }

    /**
     *
     * set categories table service
     *
     * @param \Ppb\Service\Table\Relational\Categories $categories
     *
     * @return \Ppb\Form\Element\Category
     */
    public function setCategories(CategoriesService $categories)
    {
        $this->_categories = $categories;

        return $this;
    }

    /**
     *
     * set refresh flag
     *
     * @param boolean $refresh
     *
     * @return $this
     */
    public function setRefresh($refresh)
    {
        $this->_refresh = $refresh;

        return $this;
    }

    /**
     *
     * get refresh flag
     *
     * @return boolean
     */
    public function getRefresh()
    {
        return $this->_refresh;
    }


    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $output = null;

        $translate = $this->getTranslate();

        $value        = (string)$this->getValue();
        $categoryName = $this->getCategories()->getFullName($value); // this is already translated

        $this->removeAttribute('class');

        $output = $this->getPrefix() . ' '
            . '<input type="hidden" name="' . $this->_name . '" value="' . $value . '" class="category-id" '
            . $this->_endTag
            . '<button class="btn btn-primary btn-category" '
            . $this->renderAttributes() . '>'
            . (($categoryName === null) ? $translate->_('Select Category') : $categoryName)
            . '</button> '
            . '<i class="fa fa-times btn-category-cancel" style="display: '
            . (($categoryName === null) ? 'none' : 'inline-block') . '"></i>'
            . $this->getSuffix()
            . '<div class="form-boxes"></div>';

        return $output;
    }

}


