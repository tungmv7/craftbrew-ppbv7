<?php

/**
 *
 * Cube Framework $Id$ qdcav3PgLFcU5Wdn2n9Z7UxZGdMDD/lGosur28fDQFM=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */

namespace Cube\Form;

use Cube\Validate\AbstractValidate,
    Cube\Filter\AbstractFilter,
    Cube\Validate\NotEmpty,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Translate,
    Cube\Controller\Front;

/**
 * form elements generator class
 * creates an element and outputs it to the view directly
 *
 * Class Element
 *
 * @package Cube\Form
 */
class Element
{

    /**
     *
     * the type of the element
     * accepted values: text|hidden|select|textarea|button|submit
     *
     * @var string
     */
    protected $_type;

    /**
     *
     * the name of the element
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * the element's label
     *
     * @var string
     */
    protected $_label;

    /**
     *
     * the subtitle tag, used for display purposes only
     *
     * @var string
     */
    protected $_subtitle;

    /**
     *
     * the name of the subform the element belongs to
     *
     * @var string
     */
    protected $_subForm = null;

    /**
     *
     * a string prefix for the element, for display purposes only
     * applies to text elements for now only
     *
     * @var string
     */
    protected $_prefix;

    /**
     *
     * a string suffix for the element, for display purposes only
     * applies to text elements for now only
     *
     * @var string
     */
    protected $_suffix;

    /**
     *
     * a description of the element
     *
     * @var string
     */
    protected $_description;

    /**
     *
     * setts if an element is hidden or not
     *
     * @var bool
     */
    protected $_hidden = false;

    /**
     *
     * set if we have an array of elements with the same name
     *
     * @var bool
     */
    protected $_multiple = false;

    /**
     *
     * brackets display for elements that will render an array
     *
     * @var string
     */
    protected $_brackets = '[]';

    /**
     *
     * the element's value if it accepts or set of values if accepts multiple values
     *
     * @var mixed
     */
    protected $_value;

    /**
     *
     * data resulted from a previous form submit, used to pre-fill the form element
     *
     * @var mixed
     */
    protected $_data;

    /**
     *
     * the element's attributes, format: 'id' => value
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     *
     * the end tag of the html element
     *
     * @var string
     */
    protected $_endTag = '>';

    /**
     *
     * an array of objects of type  \Cube\Validate\AbstractValidate that apply to the element
     *
     * @var array
     */
    protected $_validators = array();

    /**
     *
     * an array of objects of type \Cube\Filter\AbstractFilter that apply to the element
     *
     * @var array
     */
    protected $_filters = array();

    /**
     *
     * element is required or not in the form
     *
     * @var mixed       bool or array if we have fields dependencies
     */
    protected $_required = false;

    /**
     *
     * disabled attribute
     *
     * @var bool
     */
    protected $_disabled = false;

    /**
     *
     * options array for select, radio, checkbox form elements
     *
     * @var array
     */
    protected $_multiOptions = array();

    /**
     *
     * html code that is to be appended in the <head> tag of the output page
     *
     * @var array
     */
    protected $_headerCode = array();

    /**
     *
     * html code that is to be appended in the <body> tag of the output page
     *
     * @var array
     */
    protected $_bodyCode = array();

    /**
     *
     * set the custom data that applies to the form element in key => value array format
     *
     * @var array
     */
    protected $_customData;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * an array of validator messages resulted from the element validation method
     *
     * @var array
     */
    protected $_messages = array();

    /**
     *
     * class constructor
     *
     * @param string $element the type of the element text|hidden etc
     * @param string $name    the name of the element
     */
    public function __construct($element, $name)
    {
        $this->setType($element);
        $this->setName($name);
    }

    /**
     *
     * get the element type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     *
     * set the type of the element
     *
     * @param string $type
     *
     * @return \Cube\Form\Element
     */
    public function setType($type)
    {
        $this->_type = (string)$type;
        $this->setHidden(false);

        if (in_array($this->_type, array('hidden', 'csrf'))) {
            $this->setHidden(true);
        }

        return $this;
    }

    /**
     *
     * add a single attribute to the attributes array
     *
     * @param string $key       the attribute key (eg. class, id, placeholder etc)
     * @param string $value     the attribute value
     * @param bool   $append    set whether to append data to an existing attribute (default = true)
     *                          if set to false, the attribute will be overridden
     *
     * @return \Cube\Form\Element
     */
    public function addAttribute($key, $value, $append = true)
    {
        if (isset($this->_attributes[$key]) && $append === true) {
            $this->_attributes[$key] .= ' ' . $value;
        }
        else {
            $this->_attributes[$key] = $value;
        }

        return $this;
    }

    /**
     *
     * remove an attribute from the attributes array
     *
     * @param string $key
     *
     * @return $this
     */
    public function removeAttribute($key)
    {
        if (isset($this->_attributes[$key])) {
            unset($this->_attributes[$key]);
        }

        return $this;
    }

    /**
     *
     * get an attribute from the attributes array
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if (isset($this->_attributes[$key])) {
            return $this->_attributes[$key];
        }

        return null;
    }

    /**
     *
     * set the attributes of the element
     *
     * @param array $attributes attributes, format 'id' => value
     *
     * @return \Cube\Form\Element
     */
    public function setAttributes($attributes)
    {
        foreach ((array)$attributes as $key => $value) {
            $this->addAttribute($key, $value);
        }

        return $this;
    }

    /**
     *
     * clear attributes array
     *
     * @return \Cube\Form\Element
     */
    public function clearAttributes()
    {
        $this->_attributes = array();

        return $this;
    }

    /**
     *
     * return the label of the element
     *
     * @return string
     */
    public function getLabel()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_label);
        }

        return $this->_label;
    }

    /**
     *
     * set the label of the element
     *
     * @param string $label
     *
     * @return \Cube\Form\Element
     */
    public function setLabel($label)
    {
        $this->_label = (string)$label;

        return $this;
    }

    /**
     *
     * return the subtitle of a set of elements (like a subform)
     *
     * @return string
     */
    public function getSubtitle()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_subtitle);
        }

        return $this->_subtitle;
    }

    /**
     *
     * set the subtitle for a set of elements
     *
     * @param string $subtitle
     *
     * @return \Cube\Form\Element
     */
    public function setSubtitle($subtitle)
    {
        $this->_subtitle = (string)$subtitle;

        return $this;
    }

    /**
     *
     * clear the subtitle variable
     *
     * @return $this
     */
    public function clearSubtitle()
    {
        $this->_subtitle = null;

        return $this;
    }

    /**
     *
     * get the subform(s) the element belongs to
     *
     * @return string
     */
    public function getSubForm()
    {
        return $this->_subForm;
    }

    /**
     *
     * set the subform(s) the element belongs to
     *
     * @param mixed $subForm string or array
     *
     * @return \Cube\Form\Element
     */
    public function setSubForm($subForm)
    {
        $this->_subForm = $subForm;

        return $this;
    }

    /**
     *
     * get the prefix of the element
     *
     * @return string
     */
    public function getPrefix()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_prefix);
        }

        return $this->_prefix;
    }

    /**
     *
     * set the prefix of the element
     *
     * @param string $prefix
     *
     * @return \Cube\Form\Element
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = (string)$prefix;

        return $this;
    }

    /**
     *
     * get the suffix of element
     *
     * @return string
     */
    public function getSuffix()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_suffix);
        }

        return $this->_suffix;
    }

    /**
     *
     * set a suffix for the element
     *
     * @param string $suffix
     *
     * @return \Cube\Form\Element
     */
    public function setSuffix($suffix)
    {
        $this->_suffix = (string)$suffix;

        return $this;
    }

    /**
     *
     * return the value(s) of the element, either the element's data or default value(s)
     *
     * @return mixed
     */
    public function getValue()
    {
        return (!empty($this->_data) || $this->_data === '0') ? $this->_data : $this->_value;
    }

    /**
     *
     * set the value(s) of the element
     *
     * @param mixed $value
     *
     * @return \Cube\Form\Element
     */
    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }

    /**
     *
     * set the data of the element, resulted from a previous form input
     * apply any filters that have been enabled for the element
     *
     * @param mixed $data form data
     *
     * @return \Cube\Form\Element
     */
    public function setData($data)
    {
        $this->_data = $this->applyFilters($data);

        return $this;
    }

    /**
     *
     * clear element data
     *
     * @return \Cube\Form\Element
     */
    public function clearData()
    {
        $this->_data = null;

        return $this;
    }

    /**
     *
     * get the name of the element
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * set the name for the element
     *
     * @param string $name
     *
     * @return \Cube\Form\Element
     */
    public function setName($name)
    {
        $this->_name = (string)$name;

        return $this;
    }

    /**
     *
     * proxy to getType()
     *
     * @return string
     */
    public function getElement()
    {
        return $this->getType();
    }

    /**
     *
     * get validators array
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->_validators;
    }

    /**
     *
     * get a validator from the element
     *
     * @param string $name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getValidator($name)
    {
        if (isset($this->_validators[$name])) {
            return $this->_validators[$name];
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("The validator named '%s' could not be found
                        in the '%s' element.", $name, $this->_name));
        }
    }

    /**
     *
     * remove a validator from the element
     *
     * @param string $name
     *
     * @return \Cube\Form\Element
     */
    public function removeValidator($name)
    {
        if (isset($this->_validators[$name])) {
            unset($this->_validators[$name]);
        }

        return $this;
    }

    /**
     *
     * clear the validators of the element
     *
     * @return \Cube\Form\Element
     */
    public function clearValidators()
    {
        $this->_validators = array();

        return $this;
    }

    /**
     *
     * add an array of validator methods that will apply to the element
     *
     * @param array $validators
     *
     * @return \Cube\Form\Element
     */
    public function setValidators(array $validators)
    {
        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }

        return $this;
    }

    /**
     *
     * adds a new validator to the element
     * accepts a string if creating standard validators
     *
     * @param string|array|\Cube\Validate\AbstractValidate $validator the name of the validator method to be added
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addValidator($validator)
    {
        if ($validator instanceof AbstractValidate) {
            $this->_validators[get_class($validator)] = $validator;
        }
        else {
            $options = array();

            if (is_array($validator)) {
                $array = $validator;
                $validator = $array[0];
                $options = $array[1];
            }

            // create the validator class
            $validatorClass = '\\Cube\\Validate\\' . ucfirst($validator);

            if (class_exists($validator)) {
                $this->_validators[$validator] = new $validator($options);
            }
            else if (class_exists($validatorClass)) {
                $this->_validators[$validator] = new $validatorClass($options);
            }
            else {
                throw new \InvalidArgumentException(
                    sprintf("Class '%s' doesn\'t exist.", $validatorClass));
            }
        }

        return $this;
    }


    /**
     *
     * get filters array
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     *
     * get a filter from the element
     *
     * @param string $name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getFilter($name)
    {
        if (isset($this->_filters[$name])) {
            return $this->_filters[$name];
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("The filter named '%s' could not be found
                        in the '%s' element.", $name, $this->_name));
        }
    }

    /**
     *
     * remove a filter from the element
     *
     * @param string $name
     *
     * @return \Cube\Form\Element
     */
    public function removeFilter($name)
    {
        if (isset($this->_filters[$name])) {
            unset($this->_filters[$name]);
        }

        return $this;
    }

    /**
     *
     * clear the filters of the element
     *
     * @return \Cube\Form\Element
     */
    public function clearFilters()
    {
        $this->_filters = array();

        return $this;
    }

    /**
     *
     * add an array of filter methods that will apply to the element
     *
     * @param array $filters
     *
     * @return \Cube\Form\Element
     */
    public function setFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     *
     * adds a new filter to the element
     * accepts a string if creating standard filters
     *
     * @param string|array|\Cube\Filter\AbstractFilter $filter the name of the filter method to be added
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addFilter($filter)
    {
        if ($filter instanceof AbstractFilter) {
            $this->_filters[get_class($filter)] = $filter;
        }
        else {
            $options = array();

            if (is_array($filter)) {
                $array = $filter;
                $filter = $array[0];
                $options = $array[1];
            }

            // create the filter class
            $filterClass = '\\Cube\\Filter\\' . ucfirst($filter);

            if (class_exists($filter)) {
                $this->_filters[$filter] = new $filter($options);
            }
            else if (class_exists($filterClass)) {
                $this->_filters[$filter] = new $filterClass($options);
            }
            else {
                throw new \InvalidArgumentException(
                    sprintf("Class '%s' doesn\'t exist.", $filterClass));
            }
        }

        return $this;
    }

    /**
     *
     * apply all filters to the element data
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function applyFilters($data)
    {
        /** @var \Cube\Filter\AbstractFilter $filter */
        foreach ($this->_filters as $filter) {
            $data = $filter->filter($data);
        }

        return $data;
    }

    /**
     *
     * check if element is required
     *
     * @return mixed
     */
    public function getRequired()
    {
        return $this->_required;
    }

    /**
     *
     * set required status
     *
     * @param mixed $required
     *
     * @return \Cube\Form\Element
     */
    public function setRequired($required = true)
    {
        $this->_required = (bool)$required;

        return $this;
    }

    /**
     *
     * set disabled attribute
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled = true)
    {
        $this->_disabled = (bool)$disabled;
        if ($disabled === true) {
            $this->addAttribute('disabled', 'disabled');
        }
        else {
            $this->removeAttribute('disabled');
        }

        return $this;
    }

    /**
     *
     * get disabled attribute
     *
     * @return bool
     */
    public function getDisabled()
    {
        return $this->_disabled;
    }


    /**
     *
     * checks if an element is of hidden type
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->_hidden;
    }

    /**
     *
     * sets an element as hidden
     *
     * @param bool $hidden
     *
     * @return \Cube\Form\Element
     */
    public function setHidden($hidden = true)
    {
        $this->_hidden = (bool)$hidden;

        return $this;
    }

    /**
     *
     * get multiple status
     *
     * @return bool
     */
    public function getMultiple()
    {
        return $this->_multiple;
    }

    /**
     *
     * set multiple status
     *
     * @param bool $multiple
     *
     * @return \Cube\Form\Element
     */
    public function setMultiple($multiple = true)
    {
        $this->_multiple = (bool)$multiple;

        return $this;
    }

    /**
     *
     * get brackets
     *
     * @return string
     */
    public function getBrackets()
    {
        return $this->_brackets;
    }

    /**
     *
     * set custom brackets for the multiple setting
     *
     * @param string $brackets
     *
     * @return \Cube\Form\Element\Checkbox
     */
    public function setBrackets($brackets)
    {
        $this->_brackets = $brackets;

        return $this;
    }

    /**
     *
     * get the description of the element
     *
     * @return string
     */
    public function getDescription()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_description);
        }

        return $this->_description;
    }

    /**
     *
     * sets a description for the element
     *
     * @param string $description
     *
     * @return \Cube\Form\Element
     */
    public function setDescription($description)
    {
        $this->_description = (string)$description;

        return $this;
    }

    /**
     *
     * get the multi options array
     *
     * @return array
     */
    public function getMultiOptions()
    {
        return $this->_multiOptions;
    }

    /**
     *
     * set the multi options array
     *
     * @param array $multiOptions
     *
     * @return \Cube\Form\Element
     */
    public function setMultiOptions($multiOptions)
    {
        $this->_multiOptions = (array)$multiOptions;

        return $this;
    }

    /**
     *
     * add single multi option key value pair
     *
     * @param string $key
     * @param string $value
     *
     * @return \Cube\Form\Element
     */
    public function addMultiOption($key, $value)
    {
        $this->_multiOptions[$key] = $value;

        return $this;
    }

    /**
     *
     * clear multi options array
     *
     * @return $this
     */
    public function clearMultiOptions()
    {
        $this->_multiOptions = array();

        return $this;
    }

    /**
     *
     * get header code
     *
     * @return array
     */
    public function getHeaderCode()
    {
        return $this->_headerCode;
    }

    /**
     *
     * set the header code needed by the element
     *
     * @param string $code
     *
     * @return \Cube\Form\Element
     */
    public function setHeaderCode($code)
    {
        $this->_headerCode[] = (string)$code;

        return $this;
    }

    /**
     *
     * get body code
     *
     * @return array
     */
    public function getBodyCode()
    {
        return $this->_bodyCode;
    }

    /**
     *
     * set the body code needed by the element
     *
     * @param string $code
     *
     * @return \Cube\Form\Element
     */
    public function setBodyCode($code)
    {
        $this->_bodyCode[] = (string)$code;

        return $this;
    }

    /**
     *
     * get the custom data of the element
     *
     * @return array
     */
    public function getCustomData()
    {
        return $this->_customData;
    }

    /**
     *
     * set the custom data for the element
     *
     * @param array $customData
     *
     * @return \Cube\Form\Element
     */
    public function setCustomData($customData)
    {
        $this->_customData = $customData;

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
     * get all validation messages for the element
     *
     * @return array
     */
    public function getMessages()
    {
        return (array)$this->_messages;
    }

    /**
     *
     * set multiple validation messages
     *
     * @param array $messages
     *
     * @return \Cube\Form\Element
     */
    public function setMessages(array $messages = null)
    {
        foreach ($messages as $message) {
            $this->setMessage($message);
        }

        return $this;
    }

    /**
     *
     * clear element validator messages
     *
     * @return \Cube\Form\Element
     */
    public function clearMessages()
    {
        $this->_messages = array();

        return $this;
    }

    /**
     *
     * add a new validation message, but only if the message is not empty
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        if (!empty($message)) {
            $translate = $this->getTranslate();

            if (null !== $translate) {
                $message = $translate->_($message);
            }

            $this->_messages[] = $message;
        }
    }

    /**
     *
     * render element attributes
     * @version 1.6 - renders only attributes that have the value !== false
     *
     * @return \Cube\Form\Element
     */
    public function renderAttributes()
    {
        $attributes = null;

        foreach ($this->_attributes as $key => $value) {
            if ($value !== false) {
                $attributes .= $key . '="' . $value . '" ';
            }
        }

        return $attributes;
    }

    /**
     *
     * check if the element is valid
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = true;

        $request = Front::getInstance()->getRequest();

        if (!$request->isPost()) {
            return true;
        }

        if ($this->_required === true) {
            $this->addValidator(
                new NotEmpty());
        }

        foreach ($this->getValidators() as $validator) {
            $valid = ($this->_checkValidator($validator) === true) ? $valid : false;
        }

        return (bool)$valid;
    }

    /**
     *
     * renders the html form element
     * the method is run by all subclasses who don't have it overridden
     *
     * @return string   the html code of the element
     */
    public function render()
    {
        $value = $this->getValue();

        if (!is_string($value)) {
            $value = '';
        }
        else {
            $value = str_replace('"', '&quot;', $value);
        }

        $multiple = ($this->getMultiple() === true) ? $this->_brackets : '';

        return $this->getPrefix() . ' '
        . '<input type="' . $this->_type . '" '
        . 'name="' . $this->_name . $multiple . '" '
        . $this->renderAttributes()
        . 'value="' . $value . '" '
        . $this->_endTag . ' '
        . $this->getSuffix();
    }

    /**
     *
     * check single validator
     *
     * @param AbstractValidate $validator
     * @param string           $name
     *
     * @return bool
     */
    protected function _checkValidator(AbstractValidate $validator, $name = null)
    {
        $elementLabel = $this->getLabel();

        if (empty($elementLabel)) {
            $elementLabel = $this->getPrefix();
        }

        if ($name === null) {
            $name = $this->_name;
        }

        $validator->setName($name)
            ->setValue($this->_data);

        $valid = $validator->isValid();

        if (!$valid) {
            $this->setMessage(sprintf($validator->getMessage(), $elementLabel));
        }

        return $valid;
    }

    /**
     *
     * toString magic method, render element
     * enables <code> echo $this->formElement(); ?>
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}

