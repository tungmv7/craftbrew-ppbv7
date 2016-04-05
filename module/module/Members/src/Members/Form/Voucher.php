<?php

/**
 *
 * PHP Pro Bid $Id$ LqQT9b2iVzs2xHySxAw0hLOUnjPyg1ekV+GDYMwYoA8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * voucher creation form
 */
namespace Members\Form;

use Ppb\Form\AbstractBaseForm,
    Cube\Validate;

class Voucher extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

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
     * @param int    $userId if user id != null, we have a listing voucher
     */
    public function __construct($action = null, $userId = null)
    {
        parent::__construct($action);

        $settings = $this->getSettings();

        $this->setMethod(self::METHOD_POST);

        $id = $this->createElement('hidden', 'id');
        $this->addElement($id);

        $name = $this->createElement('text', 'name');
        $name->setLabel('Name')
            ->setDescription('Enter the name of the voucher.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($name);

        $code = $this->createElement('text', 'code');
        $code->setLabel('Voucher Code')
            ->setDescription('Enter the code of the voucher.')
            ->setAttributes(array(
                'class' => 'form-control input-large',
            ))
            ->setRequired()
            ->setValidators(array(
                'NoHtml',
                array('StringLength', array(null, 255)),
            ));
        $this->addElement($code);

        $reductionAmount = $this->createElement('text', 'reduction_amount');
        $reductionAmount->setLabel('Reduction')
            ->setRequired()
            ->setValidators(array(
                'Numeric'
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($reductionAmount);

        $reductionType = $this->createElement('select', 'reduction_type');
        $reductionType->setDescription('Enter the reduction this voucher will apply.')
            ->setMultiOptions(array(
                'percent' => '%',
                'flat'    => $settings['currency'],
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($reductionType);


        $expirationDate = $this->createElement('\\Ppb\\Form\\Element\\DateTime', 'expiration_date');
        $expirationDate->setLabel('Expiration Date')
            ->setDescription('(Optional) Set an expiration date for this voucher.')
            ->setAttributes(array(
                'class' => 'form-control input-medium'
            ))
            ->setCustomData(array(
                'formData' => array(
                    'stepMinute' => 10,
                    'hourGrid'   => 4,
                    'minuteGrid' => 10,
                    'dateFormat' => 'yy-mm-dd',
                    'minDate'    => 'new Date()'
                ),
            ))
            ->setValidators(array(
                array('GreaterThan', array(date('Y-m-d H:i:s', time()), false)),
            ));
        $this->addElement($expirationDate);

        $nbUses = $this->createElement('text', 'uses_remaining');
        $nbUses->setLabel('Number of Uses')
            ->setDescription('(Optional) Enter the number of times this voucher can be used.')
            ->setValidators(array(
                'Digits'
            ))
            ->setAttributes(array(
                'class' => 'form-control input-small'
            ));
        $this->addElement($nbUses);

        if ($userId) {
            $assignedListings = $this->createElement('text', 'assigned_listings');
            $assignedListings->setLabel('Assign to Listings')
                ->setDescription('(Optional) Enter the ids of the listings, separated by comma you wish to assign the voucher to or leave empty if you wish '
                                 . 'for it to apply to all your listings.')
                ->setAttributes(array(
                   'class' => 'form-control input-xlarge'
                ));
            $this->addElement($assignedListings);
        }

        $this->addSubmitElement($this->_buttons[self::BTN_SUBMIT], self::BTN_SUBMIT);

        $this->setPartial('forms/generic-horizontal.phtml');
    }

    /**
     *
     * will generate the edit form
     *
     * @param int $id
     *
     * @return $this
     */
    public function generateEditForm($id = null)
    {
        parent::generateEditForm($id);

        $id = ($id !== null) ? $id : $this->_editId;

        if ($id !== null) {
            $translate = $this->getTranslate();

            $this->setTitle(
                sprintf($translate->_('Edit Voucher - ID: #%s'), $id));
        }

        return $this;
    }
}