<?php

/**
 *
 * PHP Pro Bid $Id$ 4du5sDWOc44hMTS7wlhQwgO8aH6oRg0fQy3fNB7LmTo=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */
/**
 * multi key value form element
 *
 * creates an element containing an unlimited (jquery powered) list of key => value rows
 * the row will contain one text field for "key" and the other for "value"
 */

namespace Ppb\Form\Element;

use Cube\Form\Element;

class MultiKeyValue extends Element
{

    const FIELD_KEY = 'key';
    const FIELD_VALUE = 'value';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'multiKeyValue';

    /**
     *
     * class constructor
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($this->_element, $name);

        $translate = $this->getTranslate();

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " function moveUp(item) { " . "\n"
            . "     var before = item.prev(); " . "\n"
            . "     item.insertBefore(before); " . "\n"
            . " } " . "\n"
            . " function moveDown(item) { " . "\n"
            . "     var after = item.next(); " . "\n"
            . "     item.insertAfter(after); " . "\n"
            . " } " . "\n"
            . " $(document).on('click', '.delete-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     $(this).closest('.field-row').remove(); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.add-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var parent = $(this).closest('.form-group').find('.multi-key-value-rows'); " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     var cloned = row.clone(true, true); " . "\n"
            . "     row.find('input[type=text]').val(''); " . "\n"
            . "     cloned.find('.add-field-row').remove(); " . "\n"
            . "     $('<a>').attr('href', '#').attr('class', 'btn-fld-move-up').html(' <i class=\"fa fa-angle-up\"></i>').appendTo(cloned); " . "\n"
            . "     $('<a>').attr('href', '#').attr('class', 'btn-fld-move-down').html('<i class=\"fa fa-angle-down\"></i>&nbsp;').appendTo(cloned); " . "\n"
            . "     $('<button>').attr('class', 'delete-field-row btn btn-default').html('" . $translate->_('Delete') . "').appendTo(cloned); " . "\n"
            . "     parent.append(cloned); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.btn-fld-move-up', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     moveUp(row); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.btn-fld-move-down', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     moveDown(row); " . "\n"
            . " }); " . "\n"
            . "</script>");
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

        $values = $this->getValue();

        $output .= '<div class="multi-key-value-rows">';
        foreach ((array)$values[self::FIELD_KEY] as $id => $key) {
            if (!empty($key)) {
                $output .= $this->_renderRow(false, $key, $values[self::FIELD_VALUE][$id]);
            }
        }
        $output .= '</div>';

        $output .= $this->_renderRow();

        return $output;
    }

    /**
     *
     * render a single row of the element
     *
     * @param bool   $new
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected function _renderRow($new = true, $key = null, $value = null)
    {
        $translate = $this->getTranslate();

        $placeholder = $this->getAttribute('placeholder');
        if (!empty($placeholder)) {
            $placeholder .= ' ';
        }

        $this->removeAttribute('placeholder')->addAttribute('placeholder', $translate->_('Key'));
        $output = '<div class="field-row">'
            . ' <input type="text" name="' . $this->_name . '[' . self::FIELD_KEY . '][]" '
            . $this->renderAttributes()
            . 'value="' . $key . '" '
            . $this->_endTag;


        $this->removeAttribute('placeholder')->addAttribute('placeholder', $translate->_('Value'));
        $output .= ' <input type="text" name="' . $this->_name . '[' . self::FIELD_VALUE . '][]" '
            . $this->renderAttributes()
            . 'value="' . $value . '" '
            . $this->_endTag
            . (($new === true) ?
                ' <button class="add-field-row btn btn-default">' . $translate->_('Add') . '</button>' :
                ' <a class="btn-fld-move-up" href="#"><i class="fa fa-angle-up"></i></a><a class="btn-fld-move-down" href="#"><i class="fa fa-angle-down"></i></a>
                  <button class="delete-field-row btn btn-default">' . $translate->_('Delete') . '</button>')
            . '</div>';

        return $output;
    }

}

