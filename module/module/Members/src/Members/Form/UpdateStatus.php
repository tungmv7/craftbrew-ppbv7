<?php

/**
 *
 * PHP Pro Bid $Id$ AohabiWCQb/0M61Zaf0Et/02tG1rNeMR9f8yKzyOa7A=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * sale invoice update status form
 * TODO: since this form is accessed through a pop-up, it currently is problematic to post it
 * TODO: need workaround on posting forms when in pop-ups (most likely will use an async solution).
 */
namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
        Ppb\Db\Table\Row\Sale as SaleModel,
        Cube\Validate;

class UpdateStatus extends AbstractBaseForm
{

    const BTN_SUBMIT = 'update_status';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Save',
    );

    /**
     *
     * class constructor
     *
     * @param string $action the form's action
     */
    public function __construct($action = null)
    {
        parent::__construct($action);


        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $flagPayment = $this->createElement('select', 'flag_payment');
        $flagPayment->setLabel('Payment Status')
                ->setSubtitle('Payment Details')
                ->setDescription('Please the payment status of this sale.')
                ->setAttributes(array('class' => 'form-control input-medium'))
                ->setMultiOptions(SaleModel::$paymentStatuses);
        $this->addElement($flagPayment);

        $flagShipping = $this->createElement('select', 'flag_shipping');
        $flagShipping->setLabel('Shipping Status')
                ->setSubtitle('Shipping Details')
                ->setDescription('Please select the postage status for this sale.')
                ->setAttributes(array('class' => 'form-control input-default'))
                ->setMultiOptions(SaleModel::$shippingStatuses);
        $this->addElement($flagShipping);

        $trackingLink = $this->createElement('text', 'tracking_link');
        $trackingLink->setLabel('Enter Tracking Link')
                ->setDescription('(Optional) Enter the link url the buyer can access to track this package.')
                ->setAttributes(array('class' => 'form-control'));
        $this->addElement($trackingLink);

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/popup-form.phtml');
    }

}