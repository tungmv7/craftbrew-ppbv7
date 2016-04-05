<?php

/**
 * 
 * Cube Framework $Id$ GT+Jvzc99n/H0PgfJElLfXYOHfXJ9xnLEOL/7HZt18k= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.3
 */
/**
 * creates a checkbox element
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Checkbox extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     * 
     * @var string
     */
    protected $_element = 'checkbox';

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

        $multiple = '';
        if (count((array) $this->_multiOptions) > 1 || $this->getMultiple() === true) {
            $multiple = $this->_brackets;
        }


        $output .= '<input type="hidden" name="' . $this->_name . $multiple . '" value=""'
                . $this->_endTag;

        foreach ((array) $this->_multiOptions as $key => $option) {
            $checked = (in_array($key, (array) $value)) ? ' checked="checked" ' : '';

            if (is_array($option)) {
                $title = $option[0];
                $description = $option[1];
            }
            else {
                $title = $option;
                $description = null;
            }
            $output .= '<label class="checkbox">'
                    . '<input type="' . $this->_element . '" name="' . $this->_name . $multiple . '" value="' . $key . '" '
                    . $this->renderAttributes()
                    . $checked
                    . $this->_endTag
                    . ' ' . $translate->_($title)
                    . ((!empty($description)) ? '<span class="help-block">' . $translate->_($description) . '</span>' : '')
                    . '</label>'
                    . "\n";
        }

        return $output;
    }

}

