<?php
/**
 *
 * PHP Pro Bid $Id$ kqfDUxSypBoNQ+anIXQQSTEDhZyNv3QarlX/1kzIXls=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * address selector form element
 */

namespace Ppb\Form\Element;

use Cube\Form\Element\Radio;

class SelectAddress extends Radio
{
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

        foreach ((array)$this->_multiOptions as $key => $option) {
            $checked = ($value == $key) ? ' checked="checked" ' : '';

            if (is_array($option)) {
                $title = $this->_getData($option, 'title');
                $description = $this->_getData($option, 'description');
                $locationId = $this->_getData($option, 'locationId');
                $postCode = $this->_getData($option, 'postCode');
            }
            else {
                $title = $option;
                $description = null;
            }

            $output .= '<label class="radio">'
                . '<input type="' . $this->_element . '" name="' . $this->_name . '" value="' . $key . '" '
                . $this->renderAttributes()
                . (($locationId !== null) ? ' data-location-id="' . $locationId . '"' : '')
                . (($postCode !== null) ? ' data-post-code="' . $postCode . '"' : '')
                . $checked
                . $this->_endTag
                . ' ' . $title
                . (($description !== null) ? '<span class="help-block">' . $description . '</span>' : '')
                . '</label>'
                . "\n";
        }

        return $output;
    }

    protected function _getData($array, $key)
    {
        return (isset($array[$key])) ? $array[$key] : null;
    }
} 