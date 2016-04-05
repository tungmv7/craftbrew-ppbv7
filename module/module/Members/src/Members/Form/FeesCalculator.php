<?php

/**
 * 
 * PHP Pro Bid $Id$ MYCOYOtcBOvkzvm6WG6midzAyF6xVmfOdze1K73cQ+0=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.4
 */
/**
 * fees calculator form
 */

namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Model\Elements;

class FeesCalculator extends AbstractBaseForm
{

    const BTN_SUBMIT = 'fees_calculator';

    /**
     *
     * submit buttons values 
     * 
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Calculate',
    );

    /**
     *
     * override include forms array 
     * 
     * @var array
     */
    protected $_includedForms = array('fees_calculator');

    /**
     * 
     * class constructor
     * 
     * @param string $action    the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);

        $this->setMethod(self::METHOD_POST);

        $this->_model = new Elements\Listing('fees_calculator');

        $this->addElements(
                $this->_model->getElements());

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     * 
     * set form data
     * 
     * @param array $data
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->_model->setData($data);

        $this->addElements(
                $this->_model->getElements());

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        parent::setData($data);

        return $this;
    }

}