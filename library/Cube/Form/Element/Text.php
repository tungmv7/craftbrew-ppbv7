<?php

/**
 * 
 * Cube Framework $Id$ AkpaT+D4zpK0GHL+44QQxswrO9dQT13PVFM/WNlUnxU= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * text form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Text extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'text';

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

}

