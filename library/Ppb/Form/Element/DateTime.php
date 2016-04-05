<?php

/**
 *
 * PHP Pro Bid $Id$ QEzhgFv9KUnY0T/BGC9KWRr4eyGiDSk5DFdL44kDuYg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * date time custom form element
 *
 * creates an element of type datetime which also includes the datetime jquery ui plugin
 */

namespace Ppb\Form\Element;

use Cube\Form\Element,
    Cube\Controller\Front;

class DateTime extends Element
{

    const ELEMENT_CLASS = 'date-time';

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

        $this->_baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

        $this->setBodyCode("<script type=\"text/javascript\" src=\"" . $this->_baseUrl . "/js/jquery-ui-timepicker-addon.js\"></script>");

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
            . "     $('." . self::ELEMENT_CLASS . "').datetimepicker({ " . "\n"
            . "         {$formData} " . "\n"
            . "     }); " . "\n"
            . " }); " . "\n"
            . "</script>");

        return $this;
    }

}

