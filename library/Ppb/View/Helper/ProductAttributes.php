<?php

/**
 *
 * PHP Pro Bid $Id$ ih7ZlENjykqEqruLC4HHa57rlyYEBPU4Iqcx3SIygCY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * product attributes view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Service\CustomFields as CustomFieldsService;

class ProductAttributes extends AbstractHelper
{
    /**
     *
     * custom fields service
     *
     * @var \Ppb\Service\CustomFields
     */
    protected $_customFieldsService;

    /**
     *
     * product attributes input array
     *
     * @var array
     */
    protected $_productAttributes;

    /**
     *
     * get custom fields table service
     *
     * @return \Ppb\Service\CustomFields
     */
    public function getCustomFieldsService()
    {
        if (!$this->_customFieldsService instanceof CustomFieldsService) {
            $this->setCustomFieldsService(
                new CustomFieldsService());
        }

        return $this->_customFieldsService;
    }

    /**
     *
     * set custom fields table service
     *
     * @param \Ppb\Service\CustomFields $customFieldsService
     *
     * @return $this
     */
    public function setCustomFieldsService(CustomFieldsService $customFieldsService)
    {
        $this->_customFieldsService = $customFieldsService;

        return $this;
    }

    /**
     *
     * main helper method
     *
     * @param mixed $productAttributes
     *
     * @return $this
     */
    public function productAttributes($productAttributes = null)
    {
        if ($productAttributes !== null) {
            $this->setProductAttributes(
                $productAttributes);
        }

        return $this;
    }

    /**
     *
     * set product attributes input array
     *
     * @param mixed $productAttributes
     *
     * @return $this
     */
    public function setProductAttributes($productAttributes)
    {
        $this->_productAttributes = \Ppb\Utility::unserialize($productAttributes);

        return $this;
    }

    /**
     *
     * get product attributes input array
     *
     * @return array
     */
    public function getProductAttributes()
    {
        return $this->_productAttributes;
    }


    /**
     *
     * format product attributes corresponding for the sale listing for display purposes
     *
     * @param string $separator
     *
     * @return string|null
     */
    public function display($separator = '; ')
    {
        $productAttributes = $this->getProductAttributes();

        if (count($productAttributes) > 0) {
            $output = array();

            $customFieldsService = $this->getCustomFieldsService();

            $translate = $this->getTranslate();

            foreach ($productAttributes as $key => $value) {
                $customField = $customFieldsService->findBy('id', $key);

                $multiOptions = \Ppb\Utility::unserialize($customField['multiOptions']);
                $multiOptions = array_filter(array_combine($multiOptions['key'], $multiOptions['value']));

                $multiOptionsValue = (isset($multiOptions[$value])) ? $multiOptions[$value] : 'N/A';
                $output[] = $translate->_($customField['label']) . ': ' . $translate->_($multiOptionsValue);
            }

            return implode($separator, $output);
        }

        return null;
    }

}

