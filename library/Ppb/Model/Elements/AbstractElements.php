<?php

/**
 *
 * PHP Pro Bid $Id$ p/vs/sG4b11qZksDc19Yz7RbfD2UpT2ws94JikPLemk=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

namespace Ppb\Model\Elements;

use Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\View,
    Ppb\Service;

abstract class AbstractElements
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
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * form data
     *
     * @var array
     */
    protected $_data;

    /**
     * custom fields service
     *
     * @var \Ppb\Service\CustomFields
     */
    protected $_customFields;

    /**
     *
     * categories service object
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * locations table service
     *
     * @var \Ppb\Service\Table\Relational\Locations
     */
    protected $_locations;

    /**
     *
     * tax types table service
     *
     * @var \Ppb\Service\Table\TaxTypes
     */
    protected $_taxTypes;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * listing types available
     * Default: auction, product
     *
     * @var array
     */
    protected $_listingTypes = array();

    /**
     *
     * class constructor
     *
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     *
     * get view object
     *
     * @return \Cube\View
     */
    public function getView()
    {
        if (!$this->_view instanceof View) {
            $this->_view = Front::getInstance()->getBootstrap()->getResource('view');
        }

        return $this->_view;
    }

    /**
     *
     * set view object
     *
     * @param \Cube\View $view
     *
     * @return \Ppb\Model\Elements\AbstractElements
     */
    public function setView(View $view)
    {
        $this->_view = $view;

        return $this;
    }

    /**
     *
     * get settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (!is_array($this->_settings)) {
            $this->setSettings(
                Front::getInstance()->getBootstrap()->getResource('settings'));
        }

        return $this->_settings;
    }

    /**
     *
     * set settings array
     *
     * @param array $settings
     *
     * @return \Ppb\Model\Elements\AbstractElements
     */
    public function setSettings(array $settings)
    {
        $this->_settings = $settings;

        return $this;
    }

    /**
     *
     * get form data
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getData($key = null)
    {
        if ($key !== null) {
            if (!empty($this->_data[$key])) {
                return $this->_data[$key];
            }

            return null;
        }

        return $this->_data;
    }

    /**
     *
     * set form data
     *
     * @param array $data
     *
     * @return \Ppb\Model\Elements\AbstractElements
     */
    public function setData($data)
    {
        $this->_data = (array)$data;

        return $this;
    }

    /**
     *
     * get categories table service
     *
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        if (!$this->_categories instanceof Service\Table\Relational\Categories) {
            $this->setCategories(
                new Service\Table\Relational\Categories());
        }

        return $this->_categories;
    }

    /**
     *
     * set categories table service
     *
     * @param \Ppb\Service\Table\Relational\Categories $categories
     *
     * @return $this
     */
    public function setCategories(Service\Table\Relational\Categories $categories)
    {
        $this->_categories = $categories;

        return $this;
    }

    /**
     *
     * get custom fields service
     *
     * @return \Ppb\Service\CustomFields
     */
    public function getCustomFields()
    {
        if (!$this->_customFields instanceof Service\CustomFields) {
            $this->setCustomFields(
                new Service\CustomFields());
        }

        return $this->_customFields;
    }

    /**
     *
     * set custom fields service
     *
     * @param \Ppb\Service\CustomFields $customFields
     *
     * @return $this
     */
    public function setCustomFields(Service\CustomFields $customFields)
    {
        $this->_customFields = $customFields;

        return $this;
    }

    /**
     *
     * get tax types service
     *
     * @return \Ppb\Service\Table\TaxTypes
     */
    public function getTaxTypes()
    {
        if (!$this->_taxTypes instanceof Service\CustomFields) {
            $this->setTaxTypes(
                new Service\Table\TaxTypes());
        }

        return $this->_taxTypes;
    }

    /**
     *
     * set tax types service
     *
     * @param \Ppb\Service\Table\TaxTypes $taxTypes
     *
     * @return $this
     */
    public function setTaxTypes(Service\Table\TaxTypes $taxTypes)
    {
        $this->_taxTypes = $taxTypes;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * get item types
     *
     * @return array
     */
    public function getListingTypes()
    {
        if (empty($this->_listingTypes)) {
            $this->setListingTypes();
        }

        return $this->_listingTypes;
    }

    /**
     *
     * set listing types array - proxy to listings service setListingTypes() method
     *
     * @param array $listingTypes
     *
     * @return \Ppb\Model\Elements\Listing
     */
    public function setListingTypes(array $listingTypes = null)
    {
        if ($listingTypes === null) {
            $listingsService = new Service\Listings();
            $listingTypes = $listingsService->getListingTypes();
        }

        $this->_listingTypes = $listingTypes;

        return $this;
    }

    /**
     *
     * get locations table service
     *
     * @return \Ppb\Service\Table\Relational\Locations
     */
    public function getLocations()
    {
        if (!$this->_locations instanceof Service\Table\Relational\Locations) {
            $this->setLocations(
                new Service\Table\Relational\Locations());
        }

        return $this->_locations;
    }

    /**
     *
     * set locations table service
     *
     * @param \Ppb\Service\Table\Relational\Locations $locations
     *
     * @return $this
     */
    public function setLocations(Service\Table\Relational\Locations $locations)
    {
        $this->_locations = $locations;

        return $this;
    }

    /**
     *
     * get the first element of an array
     *
     * @param array $array
     *
     * @return mixed
     */
    public function getFirstElement(array $array)
    {
        $array = array_keys($array);

        return array_shift($array);
    }

    /**
     *
     * return only the elements that match the requested condition ($element[$key] in array $value)
     *
     * @param string     $key
     * @param array      $included
     * @param array|null $input elements array to use
     *
     * @return array
     */
    public function getElementsWithFilter($key, array $included, $input = null)
    {
        $elements = array();
        if ($input === null) {
            $input = $this->getElements();
        }

        foreach ($input as $element) {
            $value = (isset($element[$key])) ? $element[$key] : null;

            if (array_intersect((array)$value, $included)) {
                array_push($elements, $element);
            }
        }

        return $elements;
    }

    /**
     *
     * dummy function used as a placeholder for translatable sentences
     *
     * @param $string
     *
     * @return string
     */
    protected function _($string)
    {
        return $string;
    }

    abstract public function getElements();
}

