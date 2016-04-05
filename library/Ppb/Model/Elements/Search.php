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

namespace Ppb\Model\Elements;

use Ppb\Db\Table\Row\User as UserModel,
    Ppb\Form\Element\CategoriesBrowse;

class Search extends AbstractElements
{

    /**
     *
     * form id
     *
     * @var array
     */
    protected $_formId = array();

    /**
     *
     * user object (used to generate store categories)
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_store;

    /**
     *
     * listings select object - used for displaying counters for certain form elements
     *
     * @var \Cube\Db\Select
     */
    protected $_listingsSelect;

    /**
     *
     * class constructor
     *
     * @param array $formId
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->_formId = (array)$formId;
    }


    /**
     *
     * get store user object
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     *
     * set store user object
     *
     * @param \Ppb\Db\Table\Row\User $store
     *
     * @return $this
     */
    public function setStore(UserModel $store = null)
    {
        $this->_store = $store;

        return $this;
    }

    /**
     *
     * set countable listings select object
     *
     * @param \Cube\Db\Select $select
     */
    public function setListingsSelect($select)
    {
        $this->_listingsSelect = $select;
    }

    /**
     *
     * get countable listings select object
     *
     * @return \Cube\Db\Select
     */
    public function getListingsSelect()
    {
        return $this->_listingsSelect;
    }


    /**
     *
     * get form elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();
        $settings = $this->getSettings();

        $categoriesSelect = $this->getCategories()->getTable()
            ->select()
            ->where('enable_auctions = ?', 1)
            ->order(array('order_id ASC', 'name ASC'));


        if ($this->_store instanceof UserModel) {
            $categoriesSelect->where("user_id is null OR user_id = '{$this->_store['id']}'");
        }
        else {
            $categoriesSelect->where('user_id is null');
        }

        $categoriesFilter = array(0);

        if ($parentId = $this->getData('parent_id')) {
            if (in_array('advanced', $this->_formId)) {
                $categoriesSelect->where('parent_id is null');
            }
            else {
                $categoriesSelect->where('parent_id = ?', $parentId);
            }

            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $this->getCategories()->getBreadcrumbs($parentId)));
        }
        else {
            $categoriesSelect->where('parent_id is null');

            if ($this->_store instanceof UserModel) {
                $storeCategories = $this->_store->getStoreSettings('store_categories');
                if ($storeCategories != null) {
                    $categoriesSelect->where('id IN (?)', $storeCategories);
                }
            }
        }

        $categoriesMultiOptions = $this->getCategories()->getMultiOptions($categoriesSelect, null, $translate->_('All Categories'));

        $customFields = $this->getCustomFields()->getFields(
            array(
                'type'         => 'item', // TODO: needs to work with different custom field types - later
                'active'       => 1,
                'searchable'   => 1,
                'category_ids' => $categoriesFilter,
            ))->toArray();

        $showOnly = array(
            'accept_returns' => $translate->_('Returns Accepted'),
            'sold'           => $translate->_('Sold Items'),
        );

        if ($settings['enable_make_offer']) {
            $showOnly['make_offer'] = $translate->_('Offers Accepted');
        }

        $listingTypesMultiOptions = $this->getListingTypes();

        $currency = $this->getView()->amount(false)->getCurrency();
        $currencyCode = (!empty($currency['symbol'])) ? $currency['symbol'] : $currency['iso_code'];

        $countriesMultiOptions = $this->getLocations()->getMultiOptions(null, null, $translate->_('All Countries'));

//        if ($settings['search_counters']) {
//            $listingsService = new ListingsService();
//            $listings = $listingsService->fetchAll($this->getListingsSelect());
//
//            $categoriesCounters = array();
//            $listingTypesCounters = array();
//            $countriesCategories = array();
//            foreach ($listings as $listing) {
//                $categoriesCounters[$listing['category_id']] ++;
//                if ($listing['addl_category_id']) {
//                    $categoriesCounters[$listing['addl_category_id']] ++;
//                }
//                // categories count
//                // listing types count
//                // custom fields count
//                // location count
//            }
//
//        }

        $array = array(
            array(
                'form_id'    => 'global',
                'id'         => 'keywords',
                'element'    => 'text',
                'label'      => $this->_('Keywords'),
                'attributes' => array(
                    'class' => 'form-control'
                        . ((in_array('basic', $this->_formId) || in_array('stores',
                                $this->_formId)) ? '' : ' input-xlarge'),
                ),
            ),
            array(
                'form_id'      => 'advanced',
                'id'           => 'parent_id',
                'element'      => 'select',
                'label'        => $this->_('Select Category'),
                'multiOptions' => $categoriesMultiOptions,
                'attributes'   => array(
                    'class' => 'form-control input-large',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        $(document).on('change', '[name=\"parent_id\"]', function() {
                            $('body').addClass('loading');
                            $(this).closest('form').submit();
                        });
                    </script>",
            ),
            array(
                'form_id'      => array('basic', 'stores'),
                'id'           => 'parent_id',
                'element'      => '\\Ppb\\Form\\Element\\CategoriesBrowse',
                'label'        => $this->_('Categories'),
                'multiOptions' => $categoriesMultiOptions,
                'attributes'   => array(
                    CategoriesBrowse::ACTIVE_CATEGORY   => ($parentId) ? $this->_categories->getBreadcrumbs($parentId) : null,
                    CategoriesBrowse::STORES_CATEGORIES => ((in_array('stores', $this->_formId)) ? true : false),
                ),
                'customData'   => array(
                    'rowset' => $this->getCategories()->fetchAll($categoriesSelect),
                ),
            ),
            array(
                'form_id'    => 'basic',
                'id'         => 'price',
                'element'    => '\\Ppb\\Form\\Element\\Range',
                'label'      => $this->_('Price'),
                'prefix'     => $currencyCode,
                'attributes' => array(
                    'class' => 'form-control input-tiny',
                )
            ),
            array(
                'form_id'      => array('basic', 'advanced'),
                'id'           => 'show_only',
                'element'      => 'checkbox',
                'label'        => $this->_('Show Only'),
                'multiOptions' => $showOnly
            ),
            array(
                'form_id'      => array('basic', 'advanced'),
                'id'           => 'listing_type',
                'element'      => 'checkbox',
                'label'        => $this->_('Format'),
                'multiOptions' => $listingTypesMultiOptions,
            ),
            array(
                'form_id'      => 'global',
                'id'           => 'country',
                'element'      => 'select',
                'label'        => $this->_('Location'),
                'multiOptions' => $countriesMultiOptions,
                'attributes'   => array(
                    'class' => 'form-control'
                        . ((in_array('basic', $this->_formId) || in_array('stores',
                                $this->_formId)) ? '' : ' input-large'),
                ),
            ),
            array(
                'form_id' => 'global',
                'id'      => 'sort',
                'element' => 'hidden',
            ),
            array(
                'form_id' => 'global',
                'id'      => 'show',
                'element' => 'hidden',
            ),
        );

        foreach ($customFields as $key => $customField) {
            $customFields[$key]['form_id'] = array('basic', 'advanced');
            $customFields[$key]['id'] = 'custom_field_' . $customField['id'];

            // elements of type select and radio will be converted to checkboxes
            if (in_array($customField['element'], array('select', 'radio'))) {
                $customFields[$key]['element'] = 'checkbox';
            }

            if (in_array($customField['element'], array('text', 'textarea'))) {
                $attributes = unserialize($customField['attributes']);
                array_push($attributes['key'], 'class');
                if ($customField['element'] == 'text' && in_array('advanced', $this->_formId)) {
                    array_push($attributes['value'], 'form-control input-default');
                }
                else {
                    array_push($attributes['value'], 'form-control');
                }
                $customFields[$key]['attributes'] = serialize($attributes);
            }
        }

        array_splice($array, 3, 0, $customFields);

        return $array;
    }

}

