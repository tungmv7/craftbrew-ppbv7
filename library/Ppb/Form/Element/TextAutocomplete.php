<?php

/**
 *
 * PHP Pro Bid $Id$ 35Gk+La9p+Yj+AvzjSqH31bMlSi/R9UF8sQNVGLrJjs=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * text element with autocomplete widget
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class TextAutocomplete extends Element
{

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'textAutocomplete';

    /**
     *
     * autocomplete source (formatted)
     *
     * @var string
     */
    protected $_source;

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct('text', $name);

    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        if (is_array($source)) {
            $array = array();
            foreach ($source as $key => $value) {
                $array[] = '{ label: "' . $value . '", value: "' . $key . '" }';
            }

            $source = implode(', ', $array);
        }

        $this->setBodyCode(
            "<script type=\"text/javascript\"> " . "\n"
            . " $(document).ready(function() { " . "\n"
            . "     $('input[name=\"" . $this->_name . "\"]').autocomplete({ " . "\n"
            . "         minLength: 0, " . "\n"
            . "         source: [ " . $source . " ]  " . "\n"
            . "     }); " . "\n"
            . " }); " . "\n"
            . "</script>");

        $this->_source = $source;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

}

