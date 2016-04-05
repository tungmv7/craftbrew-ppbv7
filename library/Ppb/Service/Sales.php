<?php

/**
 *
 * PHP Pro Bid $Id$ jghX25IWD/hdDZR1c1H/iixTjAoEaC/Oy+vBj3YV1RA=
 *
 * @link        https://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     https://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * sales table service class
 * creates/edits sales (a sale can include one ore more listings in it)
 */

namespace Ppb\Service;

use Ppb\Db\Table,
    Cube\Db\Expr,
    Ppb\Service,
    Ppb\Service\Table\SalesListings as SalesListingsTableService,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Db\Table\Row\Sale as SaleModel,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\UserAddressBook as UserAddressBookModel;

class Sales extends AbstractService
{

    /**
     *
     * the id of the sale row updated/created by a save operation
     *
     * @var int
     */
    protected $_saleId;

    /**
     *
     * sales listings table service
     *
     * @var \Ppb\Service\Table\SalesListings
     */
    protected $_salesListings;

    /**
     *
     * listings table service class
     *
     * @var \Ppb\Service\Listings
     */
    protected $_listings = null;

    /**
     *
     * reputation table service class
     *
     * @var \Ppb\Service\Reputation
     */
    protected $_reputation = null;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\Sales());
    }

    /**
     *
     * set sale id
     *
     * @param int $saleId
     *
     * @return $this
     */
    public function setSaleId($saleId)
    {
        $this->_saleId = $saleId;

        return $this;
    }

    /**
     *
     * get sale id
     *
     * @return int
     */
    public function getSaleId()
    {
        return $this->_saleId;
    }


    /**
     *
     * get sales listings table service
     *
     * @return \Ppb\Service\Table\SalesListings
     */
    public function getSalesListings()
    {
        if (!$this->_salesListings instanceof SalesListingsTableService) {
            $this->setSalesListings(
                new SalesListingsTableService());
        }

        return $this->_salesListings;
    }

    /**
     *
     * set sales listings table
     *
     * @param \Ppb\Service\Table\SalesListings $salesListings
     *
     * @return \Ppb\Service\Sales
     */
    public function setSalesListings(SalesListingsTableService $salesListings)
    {
        $this->_salesListings = $salesListings;

        return $this;
    }


    /**
     *
     * get listings table service class
     *
     * @return \Ppb\Service\Listings
     */
    public function getListings()
    {
        if (!$this->_listings instanceof Service\Listings) {
            $this->setListings(
                new Service\Listings());
        }

        return $this->_listings;
    }

    /**
     *
     * set listings table service class
     *
     * @param \Ppb\Service\Listings $listings
     *
     * @return \Ppb\Service\Sales
     */
    public function setListings(Service\Listings $listings)
    {
        $this->_listings = $listings;

        return $this;
    }

    /**
     *
     * get reputation table service class
     *
     * @return \Ppb\Service\Reputation
     */
    public function getReputation()
    {
        if (!$this->_reputation instanceof Service\Reputation) {
            $this->setReputation(
                new Service\Reputation());
        }

        return $this->_reputation;
    }

    /**
     *
     * set reputation table service class
     *
     * @param \Ppb\Service\Reputation $reputation
     *
     * @return \Ppb\Service\Sales
     */
    public function setReputation(Service\Reputation $reputation)
    {
        $this->_reputation = $reputation;

        return $this;
    }

    /**
     *
     * create or edit a sale (invoice). when creating a sale, also add rows in
     * the sales listings table. when updating a sale, modify the sale id of the involved sales
     * listings, and then remove any sales that do not contain any listings.
     *
     * calculates the postage when creating or editing the invoice
     * the postage will be calculated based on the "postage_id" key, or the first option in the postage array will
     * be used if the "postage_id" key doesnt correspond to a key in the results array
     *
     * will also add the calculated or edited insurance amount if "apply_insurance" is checked.
     *
     * an element from the listings array must include:
     *      'listing' => object of type listing
     *      'price' => the price the listing has been sold for
     *      'quantity' => the quantity sold
     *
     * @param array $post
     *
     * @return \Ppb\Service\Sales
     * @throws \InvalidArgumentException
     */
    public function save($post)
    {
        $sale    = null;
        $pending = (!empty($post['pending'])) ? $post['pending'] : 0;

        if ((empty($post['buyer_id']) || empty($post['user_token'])) && empty($post['seller_id'])) {
            throw new \InvalidArgumentException("The 'buyer_id'/'user_token' and 'seller_id' keys need to be specified when creating/editing a sale/shopping cart");
        }

        $data = $this->_prepareSaveData($post);

        if (array_key_exists('id', $data)) {
            $sale = $this->findBy('id', $data['id']);
            unset($data['id']);
        }

        $newSale = false;
        if (count($sale) > 0) {
            $data['updated_at'] = new Expr('now()');
            $sale->save($data);
            $id = $sale['id'];
        } else {
            $data['created_at'] = $data['updated_at'] = new Expr('now()');
            $this->_table->insert($data);
            $id = $this->_table->getAdapter()->lastInsertId();

            $listing = $this->getListings()->findBy('id', $post['listings'][0]['listing_id']);

            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->findBy('id', $id);
            $sale->saveSaleData(array(
                'currency'       => $listing['currency'],
                'country'        => $listing['country'],
                'state'          => $listing['state'],
                'address'        => $listing['address'],
                'pickup_options' => $listing['pickup_options'],
                'apply_tax'      => $listing['apply_tax'],
            ));

            $newSale = true;
        }

        $this->setSaleId($id);

        if (isset($post['listings'])) {
            foreach ($post['listings'] as $data) {
                $data['sale_id'] = $id;
                // for shopping carts, don't allow an item to be added to a cart more than once.
                if ($pending) {
                    $salesListingsTable = $this->getSalesListings()->getTable();
                    $select             = $salesListingsTable->select('id')
                        ->where("listing_id = ?", $data['listing_id'])
                        ->where("sale_id = ?", $data['sale_id']);

                    if (!empty($data['product_attributes'])) {
                        $select->where("product_attributes = ?", $data['product_attributes']);
                    }

                    $saleListing = $salesListingsTable->fetchRow($select);
                    if ($saleListing) {
                        $data['id'] = $saleListing->getData('id');
                    }
                }
                $this->getSalesListings()->save($data);
            }
        }

        if (!$pending) {
            $postageId      = (!empty($post['postage_id'])) ? $post['postage_id'] : 0;
            $applyInsurance = (!empty($post['apply_insurance'])) ? $post['apply_insurance'] : 0;

            $this->_processPostageFields($sale, $post, $postageId, $applyInsurance);

            if ($newSale || isset($post['checkout'])) {
                $this->_processPostSaleActions();
            }
        }


        return $this;
    }

    /**
     *
     * delete a sale (and all sale listings attached)
     *
     * @param int $id sale id
     *
     * @return int the number of affected rows
     */
    public function delete($id)
    {
        return $this->_table->delete(
            $this->_table->getAdapter()->quoteInto('id = ?', $id));
    }

    /**
     *
     * if a sale is complete, do the following actions:
     *
     * - save sale transaction to accounting table
     * - update the payer's balance if in account mode
     * - prepare reputation rows for each listing in the sale
     * - close any listings from the sale which had their quantity expired and update the quantity field in the listings table
     * - add the tax rate to the sale if tax applies
     * - set 'expires_at' field if force payment is enabled
     * - email seller and buyer
     * - V7.5: if sale total is 0.00 then flag_payment is set to 1
     *
     * @return $this
     */
    protected function _processPostSaleActions()
    {
        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $this->findBy('id', $this->getSaleId());

        $settings = $this->getSettings();

        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $sale->findParentRow('\Ppb\Db\Table\Users', 'Seller');

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = $sale->findParentRow('\Ppb\Db\Table\Users', 'Buyer');

        /** @var \Ppb\Db\Table\Row\User $payer */
        $payer = ($settings['sale_fee_payer'] == 'buyer') ? $buyer : $seller;

        $saleTransactionService = new Service\Fees\SaleTransaction(
            $sale, $payer
        );

        $saleTransactionFees = $saleTransactionService->calculate();
        $totalAmount         = $saleTransactionService->getTotalAmount();

        $accountingService = new Service\Accounting();

        if ($payer->userPaymentMode() == 'account') {
            $payer->updateBalance(
                $totalAmount);
            $sale->updateActive();

            $accountingService->setRefundFlag(\Ppb\Db\Table\Row\Accounting::REFUND_ALLOWED);
        } else if ($totalAmount <= 0) {
            $sale->updateActive();
        }

        $accountingService->setUserId($payer['id'])
            ->setSaleId($this->getSaleId())
            ->saveMultiple($saleTransactionFees);
        $reputation = $this->getReputation();

        $voucher = $sale->getVoucher();

        if ($voucher !== null) {
            $voucher->updateUses();
        }

        $salesListings = $sale->findDependentRowset('\Ppb\Db\Table\SalesListings');

        $taxCalculated = false;
        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

            // get tax rate - retrieved based on the first listing in the sale
            if ($taxCalculated !== true) {
                if (($taxType = $listing->getTaxType($buyer, $sale['billing_address_id'])) !== false) {
                    $sale->save(array(
                        'tax_rate' => $taxType->getData('amount')
                    ));
                }

                $taxCalculated = true;
            }

            $quantity = $listing->updateQuantity($saleListing['quantity'], $saleListing['product_attributes'], ListingModel::SUBTRACT);

            if ($quantity == 0) {
                $listing->close();
            }

            if ($voucher !== null) {
                $price = $voucher->apply($saleListing->price(), $sale['currency'], $listing['id']);
                $saleListing->save(array(
                    'price' => $price,
                ));
            }

            // prepare reputation row: seller => buyer
            $reputation->save(array(
                'user_id'         => $sale['buyer_id'],
                'poster_id'       => $sale['seller_id'],
                'sale_listing_id' => $saleListing['id'],
                'listing_name'    => $listing['name'],
                'reputation_type' => Reputation::PURCHASE,
            ));

            // prepare reputation row: buyer => seller
            $reputation->save(array(
                'user_id'         => $sale['seller_id'],
                'poster_id'       => $sale['buyer_id'],
                'sale_listing_id' => $saleListing['id'],
                'listing_name'    => $listing['name'],
                'reputation_type' => Reputation::SALE,
            ));
        }

        if ($seller->isForcePayment()) {
            $sale->setExpiresFlag();
        }

        $sale->clearSalesListings();

        if ($sale->calculateTotal() <= 0) {
            $sale->save(array(
                'flag_payment' => 1,
            ));
            $sale->setExpiresFlag(true);
        }

        $mail = new \Members\Model\Mail\User();
        $mail->saleBuyerNotification($sale, $buyer)->send();
        $mail->saleSellerNotification($sale, $seller)->send();

        return $this;
    }


    /**
     *
     * process postage fields - calculate and save postage costs
     * the shipping_address_id field will always need to be set in the sale row
     * shipping needs to be enabled for this
     *
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     * @param array                  $post
     * @param int                    $postageId
     * @param int                    $applyInsurance
     *
     * @return $this
     */
    protected function _processPostageFields(SaleModel $sale, $post, $postageId, $applyInsurance)
    {
        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $sale->findParentRow('\Ppb\Db\Table\Users', 'Seller');

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = $sale->findParentRow('\Ppb\Db\Table\Users', 'Buyer');

        $shippingAddress = $buyer->getAddress($sale['shipping_address_id']);

        $shippingModel = new ShippingModel($seller);
        $shippingModel->setLocationId($shippingAddress['country'])
            ->setPostCode($shippingAddress['zip_code']);

        $salesListings = $sale->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $shippingModel->addData(
                $saleListing->findParentRow('\Ppb\Db\Table\Listings'), $saleListing['quantity']);
        }

        $result = array();

        try {
            $result = $shippingModel->calculatePostage();
        } catch (\RuntimeException $e) {
        }

        $shippingDetails = (!empty($result[$postageId])) ? $result[$postageId] : $result[0];

        // TODO: only the seller or admin can enter a custom amount for the postage and insurance amounts
        $insuranceAmount = (isset($post['insurance_amount'])) ? $post['insurance_amount'] : $shippingModel->calculateInsurance();
        $postageAmount   = (isset($post['postage_amount'])) ? $post['postage_amount'] : $shippingDetails['price'];

        $sale->saveSaleData(array(
            'postage_id'      => $postageId,
            'apply_insurance' => $applyInsurance,
            'postage'         => $shippingDetails,
        ));

        $postageData = array(
            'postage_amount'   => (double)$postageAmount,
            'insurance_amount' => (double)$insuranceAmount
        );

        if ($shippingAddress instanceof UserAddressBookModel) {
            if ($shippingAddressId = $shippingAddress->getData('id')) {
                $postageData['shipping_address_id'] = $shippingAddressId;
            }
        }

        $sale->save($postageData);

        return $this;
    }

    /**
     *
     * get all carts of a certain user based on his token
     *
     * @var string $userToken
     * @return array
     */
    public function getMultiOptions($userToken)
    {
        $data = array();

        $select = $this->getTable()->select()
            ->where('pending = ?', 1)
            ->where('user_token = ?', $userToken)
            ->order(array('updated_at DESC', 'created_at DESC'));

        $rowset = $this->fetchAll($select);

        /** @var \Ppb\Db\Table\Row\Sale $row */
        foreach ($rowset as $row) {
            /** @var \Ppb\Db\Table\Row\User $seller */
            $seller = $row->findParentRow('\Ppb\Db\Table\Users', 'Seller');

            $data[(string)$row['id']] = '[ ' . $row['id'] . ' ] ' .
                (($seller->storeStatus(true) == true) ?
                    $seller->getData('store_name') : $seller->getData('username'));
        }

        return $data;
    }
}
