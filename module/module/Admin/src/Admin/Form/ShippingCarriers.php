<?php

/**
 * 
 * PHP Pro Bid $Id$ tiqz+t1Y5w17PMmlYmi0nnTUK+6/47b57GUmmLndMw8=
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
    Ppb\Service\Table\ShippingCarriers as ShippingCarriersService;

class ShippingCarriers extends AbstractBaseForm
{

    public function __construct($action = null)
    {
        parent::__construct($action);
        $this->setMethod(self::METHOD_POST);

        $service = new ShippingCarriersService();
        $carriers = $service->getData();

        $formElements = array();

        $id = $this->createElement('hidden', 'id')
                ->setMultiple();

        // multi options are set from the id value in the form view partial
        $enabled = $this->createElement('checkbox', 'enabled')
                ->setMultiOptions(array(
                    1 => null))
                ->setMultiple();

        $carrierDescription = array();
        foreach ($carriers as $carrier) {
            $className = '\\Ppb\\Model\\Shipping\\Carrier\\' . $carrier['name'];

            if (class_exists($className)) {
                $carrierModel = new $className();
                $carrierElements = $carrierModel->getElements();
                foreach ($carrierElements as $carrierElement) {
                    $formElements[] = $carrierElement;
                }

                $carrierDescription[$className::NAME] = $carrierModel->getDescription();
            }
        }



        $this->addElements(
                $formElements, true);

        $this->addElement($id);
        $this->addElement($enabled);

        if (count($this->getElements()) > 0) {
            $this->addSubmitElement();
            $this->getView()->formElements = $formElements;
            $this->getView()->carrierDescription = $carrierDescription;
            $this->setPartial('forms/shipping-carriers.phtml');
        }
    }

}

