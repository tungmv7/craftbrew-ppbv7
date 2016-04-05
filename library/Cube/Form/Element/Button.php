<?php

/**
 * 
 * Cube Framework $Id$ SjCqtzr63gEmSOkJzNwMz6QkuVyBC5FEmr0eBWw6UP4= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.3
 */
/**
 * button form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Button extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'button';

    /**
     * 
     * class constructor
     * 
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);
    }

    /**
     *
     * get translated button value
     *
     * @return string
     */
    public function getValue()
    {
        $value = parent::getValue();

        return $this->getTranslate()->_($value);
    }

    /**
     * 
     * render the form element
     * 
     * @return string
     */
    public function render()
    {
        $value = $this->getValue();

        return '<button type="' . $this->_type . '" name="' . $this->_name . '" '
                . $this->renderAttributes() . '>'
                . $value
                . '</button>';
    }

}

