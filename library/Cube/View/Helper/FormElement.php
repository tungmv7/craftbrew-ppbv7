<?php

/**
 * 
 * Cube Framework $Id$ /xF6yM5fLkV2SquFPbPYTUAoWBzlIsSQdUh0p2v6fZs= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * text form element view helper
 * TODO: formElement and _prepareData appear in AbstractBaseForm as well
 */

namespace Cube\View\Helper;

use Cube\Form\Element;

class FormElement extends AbstractHelper
{

    /**
     *
     * the form element
     * 
     * @var \Cube\Form\Element
     */
    protected $_element;

    /**
     * 
     * method to create a new form element
     * 
     * @param string $element       the element type
     * @param string $name          the name of the element
     * @return \Cube\Form\Element    returns a form element object
     */
    protected function _createElement($element, $name)
    {
        $elementClass = '\\Cube\\Form\\Element\\' . ucfirst($element);

        if (class_exists($element)) {
            return new $element($name);
        }
        else if (class_exists($elementClass)) {
            return new $elementClass($name);
        }
        else {
            return new Element($element, $name);
        }
    }

    /**
     * 
     * create the form element from a view script
     * 
     * @param string|array $element     element type or an array element
     * @param string $name              element name
     * @param mixed $value              value(s)
     * @return \Cube\Form\Element
     */
    public function formElement($element, $name = null, $value = null)
    {
        if (is_array($element)) {
            $type = (!empty($element['element'])) ? $element['element'] : 'text';

            $name = ($name === null) ? $element['id'] : $name;

            $this->_element = $this->_createElement($type, $name);

            foreach ($element as $method => $params) {
                $methodName = 'set' . ucfirst($method);
                if (method_exists($this->_element, $methodName) && !empty($element[$method])) {
                    $this->_element->$methodName(
                            $this->_prepareData($params));
                }
            }
        }
        else {
            $type = (string) $element;
            $this->_element = $this->_createElement($type, $name);
        }

        $this->_element->setValue($value);

        // add header and body code
        /* @var \Cube\View\Helper\Script $helper */
        $helper = $this->getView()->getHelper('script');

        $headerCode = $this->_element->getHeaderCode();
        foreach ($headerCode as $code) {
            $helper->addHeaderCode($code);
        }

        $bodyCode = $this->_element->getBodyCode();
        foreach ($bodyCode as $code) {
            $helper->addBodyCode($code);
        }

        return $this->_element;
    }

    /**
     *
     * prepare serialized data and return it as an array which is parsable by the class methods
     *
     * @param mixed $data
     * @return array
     */
    protected function _prepareData($data)
    {
        if (!is_array($data)) {
            $array = @unserialize($data);

            if ($array === false) {
                return $data;
            }

            $keys = (isset($array['key'])) ? array_values($array['key']) : array();
            $values = (isset($array['value'])) ? array_values($array['value']) : array();

            return array_filter(
                    array_combine($keys, $values));
        }

        return $data;
    }

}

