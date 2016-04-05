<?php

/**
 *
 * PHP Pro Bid $Id$ 45mSAfJzf0XXWbNSjLSRTcYH20c/b7n+fGBdFyv+FuE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listing stock levels form element
 * allows the creation of the composite element used if we have product attributes
 * product attributes are generated from custom fields
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front,
    Ppb\View\Helper\ProductAttributes as ProductAttributesHelper;

class StockLevels extends TextAutocomplete
{

    const FIELD_OPTIONS = 'options';
    const FIELD_QUANTITY = 'quantity';
    const FIELD_PRICE = 'price';

    /**
     *
     * custom fields array
     *
     * @var array
     */
    protected $_customFields;

    /**
     *
     * form data - needed to display only selected product attributes
     *
     * @var array
     */
    protected $_formData;

    public function __construct($name)
    {
        parent::__construct($name);

        $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $translate = $this->getTranslate();

        $this->setBodyCode('<script type="text/javascript" src="' . $baseUrl . '/js/bootbox.min.js"></script>')
            ->setBodyCode(
                "<script type=\"text/javascript\">
                    $(document).on('click', 'button[name=\"" . $this->_name . "\"]', function(e) {
                        e.preventDefault();

                        var btn = $(this);

                        bootbox.confirm(\"" . $translate->_('Do you wish to set the same quantity value on all fields below?') . "\", function(result) {
                            if (result) {
                                var quantity = btn.closest('div').find('input[name*=\"" . self::FIELD_QUANTITY . "\"]').val();
                                btn.closest('.form-group').find('input[name*=\"" . self::FIELD_QUANTITY . "\"]').val(quantity);
                            }
                        });
                    });
                </script>");
    }

    /**
     *
     * get custom fields array
     *
     * @return array
     */
    public function getCustomFields()
    {
        return $this->_customFields;
    }

    /**
     *
     * set custom fields array
     *
     * @param array $customFields
     *
     * @return $this
     */
    public function setCustomFields($customFields)
    {
        $this->_customFields = $customFields;

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
    public function getFormData($key = null)
    {
        if ($key !== null) {
            if (!empty($this->_formData[$key])) {
                return $this->_formData[$key];
            }

            return null;
        }

        return $this->_formData;
    }

    /**
     *
     * set form data
     *
     * @param array $formData
     *
     * @return $this
     */
    public function setFormData($formData)
    {
        $this->_formData = $formData;

        return $this;
    }


    public function render()
    {
        $output = null;

        $array = array();
        $customFields = $this->getCustomFields();

        foreach ($customFields as $key => $customField) {
            if ($customField['product_attribute']) {

                $customFields[$key]['multiOptions']
                    = $multiOptions
                    = \Ppb\Utility::unserialize($customField['multiOptions']);

                if (!empty($multiOptions['key'])) {
                    $id = intval(str_replace('custom_field_', '', $customField['id']));
                    $value = array_filter($multiOptions['key']);

                    $customFieldData = $this->getFormData($customField['id']);

                    if (!empty($customFieldData)) {
                        $value = array_intersect($customFieldData, $value);
                    }

                    $array[$id] = $value;
                }
            }
        }

        $cartesian = array_filter(
            $this->_cartesian($array));

        if (count($cartesian) > 0) {
            $values = $this->getValue();
            $translate = $this->getTranslate();

            $cloneButton = true;

            $helper = new ProductAttributesHelper();
            foreach ($cartesian as $key => $row) {
                $value = str_ireplace(
                    array("'", '"'),
                    array('&#039;', '&quot;'), serialize($row));


                $price = null;
                $quantity = null;

                foreach ((array)$values as $k => $v) {
                    if (!empty($values[$k][self::FIELD_OPTIONS])) {
                        if (\Ppb\Utility::unserialize($values[$k][self::FIELD_OPTIONS]) == $row) {
                            $quantity = (!empty($values[$k][self::FIELD_QUANTITY])) ?
                                abs(intval($values[$k][self::FIELD_QUANTITY])) : null;
                            $price = (!empty($values[$k][self::FIELD_PRICE])) ?
                                abs(floatval($values[$k][self::FIELD_PRICE])) : null;
                        }
                    }
                }


                $output .= '<input type="hidden" name="' . $this->_name . '[' . $key . '][' . self::FIELD_OPTIONS . ']" '
                    . 'value="' . $value . '">';

                $this->removeAttribute('placeholder')->addAttribute('placeholder', $translate->_('Qty'));

                $output .= '<label class="col-sm-4 control-label">' . $helper->productAttributes($row)->display() . '</label>'
                    . '<div class="col-sm-8">'
                    . ' <input type="text" name="' . $this->_name . '[' . $key . '][' . self::FIELD_QUANTITY . ']" '
                    . $this->renderAttributes()
                    . ' value="' . $quantity . '" '
                    . $this->_endTag;

                if ($cloneButton === true) {
                    $output .= '<button type="button" name="' . $this->_name . '" class="btn btn-link">
                            <i class="fa fa-clone" alt="' . $translate->_('Copy Quantity Value') . '"></i>
                        </button>';
                }

                $this->removeAttribute('placeholder')->addAttribute('placeholder', $translate->_('Price'));

                $output .= ' '
                    . $translate->_('Add Extra:')
                    . ' <input type="text" name="' . $this->_name . '[' . $key . '][' . self::FIELD_PRICE . ']" '
                    . $this->renderAttributes()
                    . ' value="' . $price . '" '
                    . $this->_endTag;

                $output .= '</div>'
                    . '<div class="clearfix"></div>';

                $cloneButton = false;

            }
        }

        return $output;
    }

    /**
     *
     * create the cartesian product of the input array
     *
     * @param array $input
     *
     * @return array
     */
    protected function _cartesian($input)
    {
        // filter out empty values
        $input = array_filter($input);

        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }


}

