<?php

/**
 *
 * Cube Framework $Id$ Akg9KeG6a9drERxTelIYGopWdcoIRtI8QtMAtJ6QvE0=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.5
 */
/**
 * textarea form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Textarea extends Element
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

    /**
     *
     * render the form element
     *
     * @return string
     */
    public function render()
    {
        $value = $this->getValue();

        $multiple = ($this->getMultiple() === true) ? $this->_brackets : '';

        return '<textarea name="' . $this->_name . $multiple . '" '
        . $this->renderAttributes() . '>'
        . $value
        . '</textarea>';
    }

}

