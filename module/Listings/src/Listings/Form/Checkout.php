<?php

/**
 *
 * PHP Pro Bid $Id$ jGY8xge/EeBI3EyTTOM14FXsN1SgCA9nUF8qGck01KQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * shopping cart checkout form
 */

namespace Listings\Form;

use Cube\Validate,
        Ppb\Db\Table\Row\Sale,
        Ppb\Db\Table\Row\User,
        Ppb\Model\Elements\User\CartCheckout;

class Checkout extends Cart
{

    public function __construct(Sale $sale, $action = null)
    {
        parent::__construct($sale, $action);

        $settings = $this->getSettings();

        // remove all multiple elements and buttons
        /* @var \Cube\Form\Element $element */
        foreach ($this->getElements() as $element) {
            if ($element->getMultiple() || $element->getType() == 'submit') {
                $this->removeElement($element->getName());
            }
        }

        $this->_addUserModelElements();

        $salesListings = $this->getSalesListings();
        $ids = array();
        $quantity = array();
        foreach ($salesListings as $saleListing) {
            $ids[] = $saleListing['listing_id'];
            $quantity[] = $saleListing['quantity'];
        }

        // the ids and qty fields are needed to return the available postage options.
        $ids = $this->createElement('hidden', 'ids')
                ->setAttributes(
                    array('class' => 'ids'))
                ->setValue($ids);
        $this->addElement($ids);

        $quantity = $this->createElement('hidden', 'qty')
                ->setAttributes(
                    array('class' => 'qty'))
                ->setValue($quantity);
        $this->addElement($quantity);

        $this->getElement('sale_id')
                ->setValue($sale['id']);

        // the postage method selected
        if ($settings['enable_shipping'] && $this->hasElement('postage_id')) {
            $this->getElement('postage_id')
                    ->setRequired();
        }

        $this->setPartial('forms/checkout.phtml');
    }

    /**
     *
     * set form data and initialize postage method drop down values based on the items in the invoice
     *
     * @param array $data data to be inserted in the form fields
     * @param bool  $flip flip array values
     * @return $this
     */
    public function setData(array $data = null, $flip = false)
    {
        $user = $this->getUser();
        $this->_addUserModelElements($data);

        $altShip = (isset($data['alt_ship'])) ? $data['alt_ship'] : null;

        $prefix = ($altShip) ? CartCheckout::PRF_SHP : CartCheckout::PRF_BLG;
        if (!isset($data[CartCheckout::PRF_BLG . 'address_id']) && $user) {
            $address = $user->getAddress();
            if ($address) {
            $data[CartCheckout::PRF_BLG . 'address_id'] = $address->getData('id');
            }
        }

        $addressId = (isset($data[$prefix . 'address_id'])) ?
                $data[$prefix . 'address_id'] : null;

        if ($addressId) {
            if ($user) {
                $address = $user->getAddress($addressId);
                $data['locationId'] = $address['country'];
                $data['postCode'] = $address['zip_code'];
            }
        }
        else {
            $data['locationId'] = (isset($data[$prefix . 'country'])) ?
                    $data[$prefix . 'country'] : null;
            $data['postCode'] = (isset($data[$prefix . 'zip_code'])) ?
                    $data[$prefix . 'zip_code'] : null;
        }

        if ($user && isset($data[CartCheckout::PRF_BLG . 'address_id'])) {
            $billingAddress = $user->getAddress($data[CartCheckout::PRF_BLG . 'address_id']);
            $data['billingCountry'] = $billingAddress['country'];
            $data['billingState'] = $billingAddress['state'];
        }
        else {
            $data['billingCountry'] = (isset($data[CartCheckout::PRF_BLG . 'country'])) ?
                    $data[CartCheckout::PRF_BLG . 'country'] : null;
            $data['billingState'] = (isset($data[CartCheckout::PRF_BLG . 'state'])) ?
                    $data[CartCheckout::PRF_BLG . 'state'] : null;
        }

        parent::setData($data, $flip);

        return $this;
    }


    /**
     *
     * add user model elements into the form
     * the method will also make any general code compatible with the two different address sub-forms
     *
     * @param array $data
     * @return $this
     */
    protected function _addUserModelElements(array $data = null)
    {
        $user = $this->getUser();

        $model = new CartCheckout();
        $model->setData($data);
        $model->setUser($user);

        $allElements = $model->getElements();
        $this->addElements(
            $allElements, true, false);

        if (!$user) {
            $this->getElement(CartCheckout::PRF_BLG . 'name')
                    ->setSubtitle('Billing Address');

            if ($this->hasElement(CartCheckout::PRF_SHP . 'name')) {
                $this->getElement(CartCheckout::PRF_SHP . 'name')
                        ->setSubtitle('Shipping Address');
            }
        }
        else {
            $this->getElement(CartCheckout::PRF_BLG . 'address_id')
                    ->setSubtitle('Billing Address');
            $this->getElement(CartCheckout::PRF_BLG . 'name')
                    ->clearSubtitle();

            if ($this->hasElement(CartCheckout::PRF_SHP . 'name')) {
                $this->getElement(CartCheckout::PRF_SHP . 'address_id')
                        ->setSubtitle('Shipping Address');
                $this->getElement(CartCheckout::PRF_SHP . 'name')
                        ->clearSubtitle();
            }
        }

        return $this;
    }


}

