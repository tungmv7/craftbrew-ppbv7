<?php

/**
 *
 * PHP Pro Bid $Id$ iRQnvayqmUVhAD2MS7wRL1MYg+mujyiITAi++UOO8/COlExDS6lIcpA75Sn/Zgtr8ZMoYMkrTHTXj6ot5LrqkQ==
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * flat rates location groups composite element
 *
 * creates an element that will contain an unlimited number of rows that include the following columns:
 * - a "first" field - for the postage cost of the first item in an invoice
 * - an "additional" field - for each additional item in an invoice
 * - a name text field
 * - a value select field of type ChznSelect
 */

namespace Ppb\Form\Element;

use Cube\Controller\Front,
    Cube\Form\Element;

class FlatRatesLocationGroups extends Element
{

    const FIELD_NAME = 'name';
    const FIELD_LOCATIONS = 'locations';
    const FIELD_FIRST = 'first';
    const FIELD_ADDL = 'addl';

    /**
     *
     * type of element - override the variable from the parent class
     *
     * @var string
     */
    protected $_element = 'flatRatesLocationGroups';

    /**
     *
     * chzn select elements options
     *
     * @var array
     */
    protected $_chznMultiOptions = array();

    /**
     *
     * default currency
     *
     * @var string
     */
    protected $_currency;

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

        $this->_baseUrl = Front::getInstance()->getRequest()->getBaseUrl();
        $settings = Front::getInstance()->getBootstrap()->getResource('settings');

        $this->_currency = $settings['currency'];

        $this->setHeaderCode('<link href="' . $this->_baseUrl . '/js/chosen/chosen.css" media="screen" rel="stylesheet" type="text/css">')
            ->setBodyCode('<script type="text/javascript" src="' . $this->_baseUrl . '/js/chosen/chosen.jquery.min.js"></script>')
            ->setBodyCode(
                "<script type=\"text/javascript\">" . "\n"
                . " $('." . ChznSelect::ELEMENT_CLASS . "').chosen(); " . "\n"
                . "</script>");

        $this->setBodyCode(
            "<script type=\"text/javascript\">" . "\n"
            . " $(document).on('click', '.delete-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var cnt = 0; " . "\n"
            . "     $(this).closest('.field-row').remove(); " . "\n"
            . "     $('.{$name}-row').each(function() { " . "\n"
            . "         var selectName = $(this).find('select').attr('name').replace(/(\[\d+\])/g, '[' + cnt + ']');" . "\n"
            . "         $(this).find('select').attr('name', selectName); " . "\n"
            . "         cnt++; " . "\n"
            . "     }); " . "\n"
            . " }); " . "\n"
            . " $(document).on('click', '.add-field-row', function(e) { " . "\n"
            . "     e.preventDefault(); " . "\n"
            . "     var nbRows = $('.{$name}-row').length; " . "\n"
            . "     var row = $(this).closest('.field-row'); " . "\n"
            . "     var cloned = row.clone(true, true); " . "\n"
            . "     cloned.find('.add-field-row').remove(); " . "\n"
            . "     cloned.find('select').val(row.find('select').val()); " . "\n" // as per jquery clone bug (doesnt copy selected values)
            . "     cloned.find('.chzn-container').remove(); " . "\n"
            . "     cloned.find('select').css({display: 'inline-block'}).removeAttr('id').removeClass('chzn-done'); " . "\n"
            . "     $('<button>').attr('class', 'delete-field-row btn').html('" . $translate->_('Delete') . "').appendTo(cloned); " . "\n"
            . "     cloned.insertBefore(row); " . "\n"
            . "     var selectName = row.find('select').attr('name').replace(/(\[\d+\])/g, '[' + nbRows + ']'); " . "\n"
            . "     row.find('.chzn-container').remove(); " . "\n"
            . "     row.find('select').css({display: 'inline-block'}).removeAttr('id').removeClass('chzn-done').attr('name', selectName); " . "\n"
            . "     row.find('input[type=text]').val(''); " . "\n"
            . "     row.find('select').val(''); " . "\n"
            . "     $('." . ChznSelect::ELEMENT_CLASS . "').chosen(); " . "\n"
            . " }); " . "\n"
            . "</script>");
    }

    /**
     *
     * get chzn elements multi options
     *
     * @return array
     */
    public function getChznMultiOptions()
    {
        return $this->_chznMultiOptions;
    }

    /**
     *
     * set chzn elements multi options
     *
     * @param array $chznMultiOptions
     *
     * @return $this
     */
    public function setChznMultiOptions($chznMultiOptions)
    {
        $this->_chznMultiOptions = (array)$chznMultiOptions;

        return $this;
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
        $counter = 0;

        $values = $this->getValue();

        foreach ((array)$values[self::FIELD_NAME] as $id => $key) {
            if (!empty($key)) {
                $output .= $this->_renderRow(false, $key, $values[self::FIELD_LOCATIONS][$id], $values[self::FIELD_FIRST][$id], $values[self::FIELD_ADDL][$id], $counter++);
            }
        }

        $output .= $this->_renderRow(true, null, null, null, null, $counter);

        return $output;
    }

    /**
     *
     * render a single row of the element
     *
     * @param bool   $new
     * @param string $key
     * @param string $value
     * @param string $first
     * @param string $addl
     * @param int    $counter
     *
     * @return string
     */
    protected function _renderRow($new = true, $key = null, $value = null, $first = null, $addl = null, $counter = null)
    {
        $translate = $this->getTranslate();

        $brackets = '';
        if ($counter !== null) {
            $brackets = '[' . $counter . ']';
        }


        $chznSelect = new ChznSelect($this->_name . '[' . self::FIELD_LOCATIONS . ']' . $brackets, false);
        $chznSelect->setAttributes(array(
            'data-placeholder' => $translate->_('Choose Locations...'),
        ))
            ->setMultiOptions(
                $this->getChznMultiOptions())
            ->setMultiple()
            ->setValue($value);

        return '<div class="field-row ' . $this->_name . '-row">'
        . ' <input type="text" name="' . $this->_name . '[' . self::FIELD_FIRST . '][]" '
        . ' placeholder="' . $translate->_('First') . '" class="form-control input-mini input-flat-rates"'
        . ' value="' . $first . '" '
        . $this->_endTag
        . ' <input type="text" name="' . $this->_name . '[' . self::FIELD_ADDL . '][]" '
        . ' placeholder="' . $translate->_('Addl.') . '" class="form-control input-mini input-flat-rates"'
        . ' value="' . $addl . '" '
        . $this->_endTag
        . ' <input type="text" name="' . $this->_name . '[' . self::FIELD_NAME . '][]" '
        . $this->renderAttributes()
        . 'value="' . $key . '" '
        . $this->_endTag
        . ' '
        . $chznSelect->render()
        . (($new === true) ?
            ' <button class="add-field-row btn btn-default">' . $translate->_('Add') . '</button>' :
            ' <button class="delete-field-row btn btn-default">' . $translate->_('Delete') . '</button>')
        . '</div>';
    }

}

