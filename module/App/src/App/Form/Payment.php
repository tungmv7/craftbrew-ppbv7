<?php

/**
 *
 * PHP Pro Bid $Id$ ddGQGqaXRzN/3Nfmu8BSddU45A/0nAIon/DWjbQ26Fc=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * payment form
 */

namespace App\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Model\PaymentGateway\AbstractPaymentGateway;

class Payment extends AbstractBaseForm
{

    const BTN_SUBMIT = 'submit';

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_SUBMIT => 'Make Payment',
    );

    /**
     *
     * payment gateway logo
     *
     * @var string
     */
    protected $_gatewayLogo = null;

    /**
     *
     * payment gateway description
     *
     * @var string
     */
    protected $_gatewayDescription = null;

    /**
     *
     * payment gateway name
     *
     * @var string
     */
    protected $_gatewayName = null;

    /**
     *
     * class constructor
     *
     * @param \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gateway gateway model object
     */
    public function __construct(AbstractPaymentGateway $gateway)
    {
        parent::__construct($gateway->getPostUrl());

        $this->setMethod(self::METHOD_POST);
        $this->setGatewayLogo($gateway['logo_path'])
            ->setGatewayDescription($gateway->getDescription())
            ->setGatewayName($gateway['name']);

        $this->addElements($gateway->formElements());

        if ($this->hasElement('csrf')) {
            $this->removeElement('csrf');
        }

        $element = $this->createElement('submit', self::BTN_SUBMIT)
            ->setAttributes(array(
                'class' => 'btn btn-lg btn-success',
            ))
            ->setValue($this->_buttons[self::BTN_SUBMIT]);

        $this->addElement($element);

        $this->setPartial('forms/payment.phtml');
    }

    /**
     *
     * get gateway logo path
     *
     * @return string
     */
    public function getGatewayLogo()
    {
        return $this->_gatewayLogo;
    }

    /**
     *
     * set gateway logo path
     *
     * @param string $gatewayLogo logo path
     *
     * @return \App\Form\Payment
     */
    public function setGatewayLogo($gatewayLogo)
    {
        $this->_gatewayLogo = (string)$gatewayLogo;

        return $this;
    }

    /**
     *
     * get gateway description
     *
     * @return string
     */
    public function getGatewayDescription()
    {
        return $this->_gatewayDescription;
    }

    /**
     *
     * set gateway description
     *
     * @param string $gatewayDescription
     *
     * @return \App\Form\Payment
     */
    public function setGatewayDescription($gatewayDescription)
    {
        $this->_gatewayDescription = (string)$gatewayDescription;

        return $this;
    }

    /**
     *
     * set gateway name
     *
     * @param string $gatewayName
     *
     * @return $this
     */
    public function setGatewayName($gatewayName)
    {
        $this->_gatewayName = (string)$gatewayName;

        return $this;
    }

    /**
     *
     * get gateway name
     *
     * @return string
     */
    public function getGatewayName()
    {
        return $this->_gatewayName;
    }


}