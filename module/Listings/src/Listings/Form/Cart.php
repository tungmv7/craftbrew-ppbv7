<?php

/**
 *
 * PHP Pro Bid $Id$ JDaadX+MNOkPeQ8o93gQsEoYEMFUHNZIArZxlx2oIPk=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * shopping cart view form
 *
 */

namespace Listings\Form;

use App\Form\Tables as TablesForm,
    Ppb\Service,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Db\Table\Row\Sale,
    Ppb\Db\Table\Row\SaleListing;

class Cart extends TablesForm
{

    const BTN_UPDATE_CART = 'btn_update_cart';
    const BTN_CHECKOUT = 'btn_checkout';
    const BTN_PLACE_ORDER = 'btn_place_order';

    /**
     *
     * sale object
     *
     * @var \Ppb\Db\Table\Row\Sale
     */
    protected $_sale;
    /**
     *
     * sales listings rowset
     *
     * @var \Ppb\Db\Table\Rowset\SalesListings
     */
    protected $_salesListings;

    /**
     *
     * seller object
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_seller;

    /**
     *
     * shipping details array
     *
     * @var array
     */
    protected $_shippingDetails = array();

    /**
     *
     * submit buttons values
     *
     * @var array
     */
    protected $_buttons = array(
        self::BTN_PLACE_ORDER => 'Place Order',
        self::BTN_UPDATE_CART => 'Update Cart',
        self::BTN_CHECKOUT    => 'Checkout',
        self::BTN_SUBMIT      => 'Submit',
    );

    /**
     *
     * form constructor
     *
     * @param \Ppb\Db\Table\Row\Sale $sale   sale model
     * @param string                 $action form action
     *
     */
    public function __construct(Sale $sale, $action = null)
    {
        parent::__construct(new Service\Table\SalesListings(), $action);

        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $sale->findParentRow('\Ppb\Db\Table\Users', 'Seller');
        $salesListings = $sale->findDependentRowset('\Ppb\Db\Table\SalesListings');
        $this->setSalesListings($salesListings);

        $shippingModel = new ShippingModel($seller);

        $listingsService = new Service\Listings();

        foreach ($salesListings as $data) {
            $listing = $listingsService->findBy('id', $data['listing_id']);
            $shippingModel->addData(
                $listing, $data['quantity']);
        }

        $this->setSale($sale);

        $seller->setShipping($shippingModel);
        $this->setSeller($seller);

        // sale id
        $saleId = $this->createElement('hidden', 'id');
        $this->addElement($saleId);


        // hidden postage calculator fields
        $postageId = $this->createElement('hidden', 'postage_id')
            ->setLabel('Shipping Method');
        $this->addElement($postageId);

        $locationId = $this->createElement('hidden', 'locationId');
        $this->addElement($locationId);

        $postCode = $this->createElement('hidden', 'postCode');
        $this->addElement($postCode);

        $insuranceCheckbox = $this->createElement('checkbox', 'apply_insurance');
        $insuranceCheckbox->setLabel('Apply Insurance')
            ->setAttributes(array(
                'onchange' => 'this.form.submit();',
            ))
            ->setMultiOptions(
                array(1 => null));
        $this->addElement($insuranceCheckbox);

        $insuranceAmount = $this->createElement('hidden', 'insurance_amount');
        $insuranceAmount->setValue($shippingModel->calculateInsurance());
        $this->addElement($insuranceAmount);


        $updateCart = $this->createElement('submit', self::BTN_UPDATE_CART)
            ->setAttributes(array(
                'class' => 'btn btn-default',
            ))
            ->setValue($this->_buttons[self::BTN_UPDATE_CART]);

        $this->addElement($updateCart);

        $checkout = $this->createElement('submit', self::BTN_CHECKOUT)
            ->setAttributes(array(
                'class' => 'btn btn-primary btn-lg btn-block',
            ))
            ->setValue($this->_buttons[self::BTN_CHECKOUT]);

        $this->addElement($checkout);

        $this->setPartial('forms/cart.phtml');
    }

    /**
     *
     * set sale object
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return $this
     */
    public function setSale($sale)
    {
        $this->_sale = $sale;

        return $this;
    }

    /**
     * @return \Ppb\Db\Table\Row\Sale
     */
    public function getSale()
    {
        return $this->_sale;
    }


    /**
     *
     * set sales listings rowset
     *
     * @param \Ppb\Db\Table\Rowset\SalesListings $salesListings
     *
     * @return $this;
     */
    public function setSalesListings($salesListings)
    {
        $this->_salesListings = $salesListings;

        return $this;
    }

    /**
     *
     * get sales listings rowset
     *
     * @return \Ppb\Db\Table\Rowset\SalesListings
     */
    public function getSalesListings()
    {
        return $this->_salesListings;
    }

    /**
     *
     * set seller object
     *
     * @param \Ppb\Db\Table\Row\User $seller
     *
     * @return $this
     */
    public function setSeller($seller)
    {
        $this->_seller = $seller;

        return $this;
    }

    /**
     * @return \Ppb\Db\Table\Row\User
     */
    public function getSeller()
    {
        return $this->_seller;
    }

    /**
     *
     * set shipping details array
     *
     * @param array $shippingDetails
     *
     * @return $this
     */
    public function setShippingDetails($shippingDetails)
    {
        $this->_shippingDetails = $shippingDetails;

        return $this;
    }

    /**
     *
     * get shipping details array
     *
     * @return array
     */
    public function getShippingDetails()
    {
        return $this->_shippingDetails;
    }

    /**
     *
     * set form data and initialize postage method drop down values based on the items in the invoice
     *
     * @param array $data data to be inserted in the form fields
     * @param bool  $flip flip array values
     *
     * @throws \RuntimeException
     * @return $this
     */
    public function setData(array $data = null, $flip = false)
    {
        parent::setData($data, $flip);

        $sale = $this->getSale();

        $saleListing = $this->getSalesListings()->getRow(0);

        if ($saleListing instanceof SaleListing) {
            $billingCountry = isset($data['billingCountry']) ? $data['billingCountry'] : null;
            $billingState = isset($data['billingState']) ? $data['billingState'] : null;

            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');
            if (($taxType = $listing->getTaxType(null, null, $billingCountry, $billingState)) !== false) {
                $sale->addData('tax_rate', $taxType->getData('amount'));
            }
        }

        $this->setSale($sale);

        $shippingModel = $this->getSeller()->getShipping();

        $postageId = (isset($data['postage_id'])) ? $data['postage_id'] : '';
        if ($postageId !== '' && isset($data['locationId']) && isset($data['postCode'])) {
            $shippingModel->setLocationId($data['locationId'])
                ->setPostCode($data['postCode']);

            try {
                $result = $shippingModel->calculatePostage();

                if (count($result) > 0) {
                    $shippingDetails = (!empty($result[$data['postage_id']])) ? $result[$data['postage_id']] : current($result);
                    $sale['postage_amount'] = $shippingDetails['price'];
                    $this->setShippingDetails($shippingDetails);
                }
            } catch (\RuntimeException $e) {
            }
        }

        if ($this->getData('apply_insurance')) {
            $sale['apply_insurance'] = true;
            $sale['insurance_amount'] = $shippingModel->calculateInsurance();
        }

        $this->setSale($sale);

        return $this;
    }

    /**
     *
     * checks if the form is valid
     * will validate the quantity field for available items
     * we select the listings with the for update clause so that they wont be altered while we check for the available quantity
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = parent::isValid();

        $salesListings = $this->getSalesListings();

        $translate = $this->getTranslate();

        if ($this->hasElement('quantity')) {
            $quantity = $this->getData('quantity');

            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            foreach ($salesListings as $saleListing) {
                /** @var \Ppb\Db\Table\Row\Listing $listing */
                $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings', null,
                    $saleListing->getTable()->select()->forUpdate());

                $quantityRequested = $quantity[$saleListing['id']];
                $productAttributes = \Ppb\Utility::unserialize($saleListing['product_attributes']);

                $availableQuantity = $listing->getAvailableQuantity($saleListing['quantity'], $productAttributes);

                if ($availableQuantity < $quantity[$saleListing['id']]) {
                    $valid = false;
                    $this->setMessage(
                        sprintf(
                            $translate->_('Product #%s, "%s" - not enough quantity - available (%s) - requested (%s).'),
                            $listing['id'], $listing['name'], $availableQuantity, $quantityRequested));
                }
            }
        }


        return $valid;
    }
}
