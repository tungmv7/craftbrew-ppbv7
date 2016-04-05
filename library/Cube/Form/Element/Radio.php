<?php

/**
 * 
 * Cube Framework $Id$ fDeYAIQOHUnymztNhIxjQH9BkkaDWd4IzL436wPllqM= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.3
 */
/**
 * radio buttons form element generator class
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Radio extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'radio';

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
        $output = null;
        $value = $this->getValue();

        $translate = $this->getTranslate();

        foreach ((array) $this->_multiOptions as $key => $option) {
            $checked = ($value == $key) ? ' checked="checked" ' : '';

            if (is_array($option)) {
                $title = isset($option[0]) ? $option[0] : null;

                $description = isset($option[1]) ? $option[1] : null;
            }
            else {
                $title = $option;
                $description = null;
            }
            
            $output .= '<label class="radio">'
                    . '<input type="' . $this->_element . '" name="' . $this->_name . '" value="' . $key . '" '
                    . $this->renderAttributes()
                    . $checked
                    . $this->_endTag
                    . ' ' . $translate->_($title)
                    . (($description !== null) ? '<span class="help-block">' . $translate->_($description) . '</span>' : '')
                    . '</label>'
                    . "\n";
        }

        return $output;
    }

}

