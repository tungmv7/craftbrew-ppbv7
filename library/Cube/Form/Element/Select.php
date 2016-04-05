<?php

/**
 *
 * Cube Framework $Id$ nXxphfIpBC6tFFROxkHJOglKP+5RAaD7rUqcdehorxQ=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.6
 */
/**
 * select form element generator class
 * apply translator to select options
 */

namespace Cube\Form\Element;

use Cube\Form\Element;

class Select extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'select';

    /**
     *
     * hide default value
     *
     * @var bool
     */
    protected $_hideDefault = false;

    /**
     *
     * uni-dimensional array containing multiOptions keys that will be considered disabled
     *
     * @var array
     */
    protected $_disabledOptions = array();

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
     * get _hide default option
     *
     * @return string
     */
    public function isHideDefault()
    {
        return $this->_hideDefault;
    }

    /**
     *
     * set hide default option
     *
     * @param boolean $default
     *
     * @return $this
     */
    public function setHideDefault($default = true)
    {
        $this->_hideDefault = (bool)$default;

        return $this;
    }

    /**
     *
     * set disabled options array
     *
     * @param array $disabledOptions
     *
     * @return $this
     */
    public function setDisabledOptions($disabledOptions)
    {
        $this->_disabledOptions = $disabledOptions;

        return $this;
    }

    /**
     *
     * get disabled options array
     *
     * @return array
     */
    public function getDisabledOptions()
    {
        return $this->_disabledOptions;
    }

    /**
     *
     * get disabled option status
     *
     * @param $key
     *
     * @return bool
     */
    public function getDisabledOption($key)
    {
        if (in_array($key, $this->_disabledOptions)) {
            return true;
        }

        return false;
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
        $brackets = null;
        $value = $this->getValue();

        $translate = $this->getTranslate();

        $multipleAttribute = $this->getAttribute('multiple');

        if ($this->getMultiple() === true || !empty($multipleAttribute)) {
            $brackets = $this->getBrackets();

            if (!empty($multipleAttribute)) {
                $output .= '<input type="hidden" name="' . $this->_name . '" value=""'
                    . $this->_endTag;
            }
        }

        $output .=
            $this->getPrefix()
            . ' <select name="' . $this->_name . $brackets . '" '
            . $this->renderAttributes() . '>';

        $required = $this->getRequired();
        $hideDefault = $this->isHideDefault();

        if ($required && !$hideDefault) {
            $output .= '<option value="" selected>' . $translate->_('-- select --') . '</option>';
        }

        foreach ((array)$this->_multiOptions as $key => $option) {
            $selected = (in_array($key, (array)$value)) ? ' selected' : '';
            $disabled = $this->getDisabledOption($key) ? ' disabled' : '';

            $output .= '<option value="' . $key . '"' . $selected . $disabled . '>' . $translate->_($option) . '</option>';
        }

        $output .= '</select> '
            . $this->getSuffix();

        return $output;
    }

}

