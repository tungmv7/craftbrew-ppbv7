<?php

/**
 *
 * PHP Pro Bid $Id$ pSDcq50uuxcH1jiF8nRW5lOYPgL86IdnLnS+xYpyiZQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * payment gateways management form
 */

namespace Admin\Form;

use Ppb\Form\AbstractBaseForm,
    Ppb\Service\Table\PaymentGateways as PaymentGatewaysService;

class PaymentGateways extends AbstractBaseForm
{

    public function __construct($action = null)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $service = new PaymentGatewaysService();
        $gateways = $service->getData();

//        $formElements = new Elements\PaymentGateways();
        $formElements = array();

        // each gateway will have 3 variables that need to be saved in the 'payment_gateways' table:
        // id, site_fees & direct_payment
        $id = $this->createElement('hidden', 'id')
            ->setMultiple();

        // multi options are set from the id value in the form view partial
        $siteFees = $this->createElement('checkbox', 'site_fees')
            ->setMultiOptions(array(
                1 => null))
            ->setMultiple();

        $directPayment = $this->createElement('checkbox', 'direct_payment')
            ->setMultiOptions(array(
                1 => null))
            ->setMultiple();


        foreach ($gateways as $gateway) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

            if (class_exists($className)) {
                $gatewayModel = new $className();
                $gatewayElements = $gatewayModel->getElements();
                if (!empty($gatewayElements)) {
                    $formElements = array_merge($formElements, $gatewayElements);
                }
            }
        }

        $this->addElements(
            $formElements, true);

        $this->addElement($id);
        $this->addElement($siteFees);
        $this->addElement($directPayment);

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->getView()->formElements = $formElements;
            $this->setPartial('forms/payment-gateways.phtml');
        }
    }

}

