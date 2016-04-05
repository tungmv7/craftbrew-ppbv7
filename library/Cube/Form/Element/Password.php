<?php

/**
 * 
 * Cube Framework $Id$ DjX+/Z5LMBLRas1Tm11bM3Dcno4O/oDSrywgBy7JUKU= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.4
 */
/**
 * creates a password element
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Password extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'password';

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
     * renders the password form element
     * the element wont render the data resulted from a previous input
     *
     * @return string   the html code of the element
     */
    public function render()
    {
        $multiple = ($this->getMultiple() === true) ? $this->_brackets : '';

        return $this->getPrefix() . ' '
               . '<input type="' . $this->_type . '" '
               . 'name="' . $this->_name . $multiple . '" '
               . $this->renderAttributes()
               . $this->_endTag . ' '
               . $this->getSuffix();
    }

}

