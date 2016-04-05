<?php

/**
 *
 * PHP Pro Bid $Id$ dj86FL095gRBSOPM4ZFtqhgBKW9wEICbCbg0vmtLTNU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * full name (first name - last name) form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
        Cube\Controller\Front,
        Cube\Validate\NotEmpty;

class FullName extends Element
{

    const KEY_FIRST = 'first';
    const KEY_LAST = 'last';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'fullName';

    /**
     *
     * request object
     *
     * @var \Cube\Controller\Request\AbstractRequest
     */
    protected $_request;

//    /**
//     *
//     * the names for each of the two fields
//     *
//     * @var array
//     */
//    protected $_fieldNames = array();

    /**
     *
     * the labels for each of the two fields
     *
     * @var array
     */
    protected $_fieldLabels = array();

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);

        $this->_request = Front::getInstance()->getRequest();
    }

    /**
     *
     * set field labels
     *
     * @param array $fieldLabels
     * @return $this
     */
    public function setFieldLabels($fieldLabels)
    {
        $this->_fieldLabels = $fieldLabels;

        return $this;
    }

    /**
     *
     * get field labels
     *
     * @return array
     */
    public function getFieldLabels()
    {
        return $this->_fieldLabels;
    }

    /**
     *
     * get individual field label
     *
     * @param $key
     * @return string
     */
    public function getFieldLabel($key)
    {
        if (isset($this->_fieldLabels[$key])) {
            return $this->_fieldLabels[$key];
        }

        return $this->_label;
    }

    /**
     *
     * return the value(s) of the element, either the element's data or default value(s)
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($key = null)
    {
        $value = parent::getValue();

        if ($key !== null) {
            if (array_key_exists($key, (array) $value)) {
                return $value[$key];
            }
            else {
                return null;
            }
        }

        return $value;
    }

    /**
     *
     * render element attributes
     *
     * @param string $type
     * @return \Cube\Form\Element
     */
    public function renderAttributes($type = null)
    {
        $attributes = null;

        foreach ($this->_attributes as $key => $value) {
            $attributes .= $key . '="' . ((is_array($value)) ? $value[$type] : $value) . '" ';
        }

        return $attributes;
    }

    /**
     *
     * check if the composite element is valid
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        if (!$this->_request->isPost()) {
            return true;
        }

        if ($this->_required === true) {
            $this->addValidator(
                new NotEmpty());
        }

        $firstNameLabel = $this->getFieldLabel(self::KEY_FIRST);
        $lastNameLabel = $this->getFieldLabel(self::KEY_LAST);

        $firstNameValue = $this->getValue(self::KEY_FIRST);
        $lastNameValue = $this->getValue(self::KEY_LAST);

        // get original values
        $label = $this->getLabel();
        $data = $this->_data;

        foreach ($this->getValidators() as $validator) {
            // check first name
            $this->setLabel($firstNameLabel);
            $this->setData($firstNameValue);
            $valid = ($this->_checkValidator($validator) === true) ? $valid : false;

            // check last name
            $this->setLabel($lastNameLabel);
            $this->setData($lastNameValue);
            $valid = ($this->_checkValidator($validator) === true) ? $valid : false;
        }

        // restore values
        $this->setLabel($label);
        $this->setData($data);

        return (bool)$valid;
    }

    /**
     *
     * render composite element
     *
     * @return string
     */
    public function render()
    {
        return $this->getPrefix() . ' '
               . '<input type="' . $this->_type . '" '
               . 'name="' . $this->_name . '[' . self::KEY_FIRST . ']" '
               . $this->renderAttributes(self::KEY_FIRST)
               . 'value="' . $this->getValue(self::KEY_FIRST) . '" '
               . $this->_endTag . ' '
               . $this->getSuffix()
               . ' '
               . $this->getPrefix() . ' '
               . '<input type="' . $this->_type . '" '
               . 'name="' . $this->_name . '[' . self::KEY_LAST . ']" '
               . $this->renderAttributes(self::KEY_LAST)
               . 'value="' . $this->getValue(self::KEY_LAST) . '" '
               . $this->_endTag . ' '
               . $this->getSuffix();
    }

}

