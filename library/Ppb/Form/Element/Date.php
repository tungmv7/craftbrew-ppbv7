<?php

/**
 *
 * PHP Pro Bid $Id$ qP1GbE2btFhCWgpckhd+na8RbuASmKCt9x2ssxt0ftA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * date custom form element
 *
 * creates an element of type date with the datepicker jquery ui component enabled
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class Date extends Element
{

    const ELEMENT_CLASS = 'element-date';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'text';

    /**
     *
     * base url of the application
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $this->addAttribute('class', self::ELEMENT_CLASS)
            ->addAttribute('readonly', 'readonly');
    }


    /**
     *
     * set the custom data for the element, and add the javascript code
     *
     * @param array $customData
     *
     * @return $this
     */
    public function setCustomData($customData)
    {
        $this->_customData = $customData;

        $formData = array();
        if (isset($this->_customData['formData'])) {
            foreach ((array)$this->_customData['formData'] as $key => $value) {
                $formData[] = "'{$key}' : '{$value}'";
            }
        }
        $formData = implode(", \n", $formData);

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " $(document).ready(function() { " . "\n"
            . "     $('." . self::ELEMENT_CLASS . "').datepicker({ " . "\n"
            . "         {$formData} " . "\n"
            . "     }); " . "\n"
            . " }); " . "\n"
            . "</script>");

        return $this;
    }
}

