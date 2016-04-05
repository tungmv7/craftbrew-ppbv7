<?php

/**
 *
 * PHP Pro Bid $Id$ we8Lo79p6+JcMOw6RhRmVCKEi8N32NoNL8XOAXqN5Fw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * sales table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Db\Table\SalesListings,
    Ppb\Db\Table\Vouchers as VouchersTable,
    Cube\Db\Expr,
    Ppb\Service;

class Sale extends AbstractRow
{

    /**
     * sale payment statuses
     */
    const PAYMENT_UNPAID = 0;
    const PAYMENT_PAID = 1;
    const PAYMENT_PAID_DIRECT_PAYMENT = 2;
    const PAYMENT_PAY_ARRIVAL = 3;


    /**
     * sale shipping statuses
     */
    const SHIPPING_PROCESSING = 0;
    const SHIPPING_SENT = 1;
    const SHIPPING_PROBLEM = 2;
    const SHIPPING_NA = -1;

    /**
     *
     * payment statuses array
     *
     * @var array
     */
    public static $paymentStatuses = array(
        self::PAYMENT_UNPAID              => 'Unpaid',
        self::PAYMENT_PAID                => 'Paid',
        self::PAYMENT_PAID_DIRECT_PAYMENT => 'Paid (Direct Payment)',
        self::PAYMENT_PAY_ARRIVAL         => 'Payment on Arrival',
    );

    /**
     *
     * shipping statuses array
     *
     * @var array
     */
    public static $shippingStatuses = array(
        self::SHIPPING_PROCESSING => 'Processing',
        self::SHIPPING_SENT       => 'Posted/Sent',
        self::SHIPPING_PROBLEM    => 'Problem',
        self::SHIPPING_NA         => 'N/A',
    );


    /**
     *
     * sale data array keys
     *
     * @var array
     */
    protected static $saleDataKeys = array(
        'currency',
        'country',
        'state',
        'address',
        'pickup_options'
    );

    /**
     *
     * sales listings rowset
     * (used to override the sales listings table in order to preview a sale total)
     *
     * @var array
     */
    protected $_salesListings = array();

    /**
     *
     * sales service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array('sale_data');

    /**
     *
     * the tax amount that corresponds to the sale
     * calculated by the calculateTotal() method
     *
     * @var bool|float
     */
    protected $_taxAmount = false;

    /**
     *
     * voucher object to be applied
     *
     * @var \Ppb\Db\Table\Row\Voucher|null
     */
    protected $_voucher = false;

    /**
     *
     * get sales listings array / rowset
     *
     * @return array|\Ppb\Db\Table\Rowset\SalesListings
     */
    public function getSalesListings()
    {
        if (empty($this->_salesListings)) {
            $this->setSalesListings(
                $this->findDependentRowset('\Ppb\Db\Table\SalesListings'));
        }

        return $this->_salesListings;
    }

    /**
     *
     * set sales listings array / rowset
     *
     * @param array|\Ppb\Db\Table\Rowset\SalesListings $salesListings
     *
     * @return $this
     */
    public function setSalesListings($salesListings)
    {
        $this->clearSalesListings();

        foreach ($salesListings as $saleListing) {
            $this->addSaleListing($saleListing);
        }

        return $this;
    }

    /**
     *
     * clear sales listings array
     *
     * @return $this
     */
    public function clearSalesListings()
    {
        $this->_salesListings = array();

        return $this;
    }

    /**
     *
     * add sale listing row
     *
     * @param array|\Ppb\Db\Table\Row\SaleListing $saleListing
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addSaleListing($saleListing)
    {
        if ($saleListing instanceof SaleListing) {
            $this->_salesListings[] = $saleListing;
        }
        else {
            if (!array_key_exists('price', $saleListing) ||
                !array_key_exists('quantity', $saleListing)
            ) {
                throw new \InvalidArgumentException("The sale listing array must include the 'price' and 'quantity' keys");
            }

            $this->_salesListings[] = new SaleListing(array(
                'data'  => $saleListing,
                'table' => new SalesListings(),
            ));
        }

        return $this;
    }

    /**
     *
     * set tax amount
     *
     * @param bool|float $taxAmount
     *
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->_taxAmount = floatval($taxAmount);

        return $this;
    }

    /**
     *
     * get tax amount - calculate it if unset
     *
     * @param bool $force force tax calculation
     *
     * @return bool|float
     */
    public function getTaxAmount($force = false)
    {
        if ($this->_taxAmount === false || $force === true) {
            $this->calculateTotal();
        }

        return $this->_taxAmount;
    }

    /**
     * @param null|\Ppb\Db\Table\Row\Voucher $voucher
     */
    public function setVoucher($voucher)
    {
        $this->_voucher = $voucher;
    }

    /**
     * @return null|\Ppb\Db\Table\Row\Voucher
     */
    public function getVoucher()
    {
        if ($this->_voucher === false) {
            if (($data = \Ppb\Utility::unserialize($this->getData('voucher_details'))) !== false) {
                $voucher = new Voucher(array(
                    'table' => new VouchersTable(),
                    'data'  => $data,
                ));
            }
            else {
                $voucher = null;
            }

            $this->setVoucher($voucher);
        }

        return $this->_voucher;
    }


    /**
     *
     * the method will calculate the total value of the sale invoice, including postage and insurance amounts
     * "postage_amount" and "insurance_amount" will always be calculated when the sale row is created
     * the total is calculated based on the "tax_rate" field and tax applies to the total amount
     *
     * @param bool $simple
     * @param bool $applyVoucher we can opt to not apply the voucher when calculating the total price (for example on the initial cart page)
     *
     * @return float
     */
    public function calculateTotal($simple = false, $applyVoucher = true)
    {
        $result = 0;
        $taxAmount = null;

        $salesListings = $this->getSalesListings();

        $applyVoucher = ($this->getData('pending') && $applyVoucher) ? true : false;

        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            $result += $saleListing->calculateTotal($applyVoucher);
        }

        if ($simple === false) {
            if ($this->hasPostage()) {
                $result += $this->getData('postage_amount');

                if ($this->getInsuranceAmount()) {
                    $result += $this->getData('insurance_amount');
                }
            }

            if ($this->getData('tax_rate') > 0) {
                $taxAmount = $result * $this->getData('tax_rate') / 100;
                $result += $taxAmount;
            }
        }

        $this->setTaxAmount($taxAmount);

        return $result;
    }

    /**
     *
     * get the active status of a sale invoice
     *
     * @return bool
     */
    public function isActive()
    {
        return ($this->getData('active') && !$this->getData('pending')) ? true : false;
    }

    /**
     *
     * checks if the sale is marked as paid
     *
     * @return bool
     */
    public function isPaid()
    {
        return ($this->getData('flag_payment') == self::PAYMENT_UNPAID) ? false : true;
    }

    /**
     *
     * whether an invoice can be edited/combined
     * if the logged in user is the buyer, we add an additional flag to check if the sale is locked for editing
     * (as in only the seller can edit it)
     *
     * @return bool
     */
    public function canEdit()
    {
        if (!count($this)) {
            return false;
        }

        $canEdit = (
            $this->isActive() &&
            !$this->isPaid() &&
            !$this->getData('expires_at') &&
            $this->getData('buyer_id') &&
            $this->getData('seller_id')
        ) ? true : false;

        $user = $this->getUser();

        if ($user['id'] === $this->getData('buyer_id') && $this->getData('edit_locked')) {
            $canEdit = false;
        }

        return $canEdit;
    }

    /**
     *
     * check if the active user can combine invoices
     * invoices cannot be combined if a voucher has been applied on one of them
     *
     * @return bool
     */
    public function canCombinePurchases()
    {
        if (!$this->getData('voucher_details')) {
            $settings = $this->getSettings();

            if ($this->isSeller() || $settings['buyer_create_invoices']) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if the logged in user is the seller in the sale transaction
     *
     * @param bool $admin check if the logged in user is an admin as well
     *
     * @return bool
     */
    public function isSeller($admin = false)
    {
        $user = $this->getUser();

        $result = false;
        if ($this->getData('seller_id') == $user['id']) {
            $result = true;
        }
        else if ($admin == true && $user['role'] == 'Admin') {
            $result = true;
        }

        return $result;
    }

    /**
     *
     * check if the logged in user is the buyer in the sale transaction
     *
     * @return bool
     */
    public function isBuyer()
    {
        $user = $this->getUser();

        if ($this->getData('buyer_id') == $user['id']) {
            return true;
        }

        return false;
    }

    /**
     *
     * get the pickup option that applies to this sale
     *
     * @return string|null
     */
    public function getPickupOptions()
    {
        /** @var \Ppb\Db\Table\Rowset\SalesListings $salesListings */
        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

            return $listing[\Ppb\Model\Shipping::FLD_PICKUP_OPTIONS];
        }

        return null;
    }

    /**
     *
     * get the direct payment methods that apply to this sale
     *
     * @param string $type type of payment methods to retrieve ('direct', 'offline' or null for all)
     *
     * @return array
     */
    public function getPaymentMethods($type = null)
    {
        $paymentMethods = null;

        /** @var \Ppb\Db\Table\Rowset\SalesListings $salesListings */
        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');
            if ($listing instanceof Listing) {
                $listingPaymentMethods = $listing->getPaymentMethods($type);

                if ($paymentMethods === null) {
                    $paymentMethods = $listingPaymentMethods;
                }
                else {
                    $paymentMethods = array_uintersect($paymentMethods, $listingPaymentMethods,
                        function ($a, $b) {
                            return strcmp($a['name'] . $a['type'], $b['name'] . $b['type']);
                        });
                }
            }
        }

        return $paymentMethods;
    }

    /**
     *
     * return true if direct payment can be made for the sale by the logged in user (the buyer)
     *
     * @return array|false  an array of direct payment methods or false if payment is not possible
     */
    public function canPayDirectPayment()
    {
        $user = $this->getUser();

        if ($this->isActive() &&
            $user['id'] == $this->getData('buyer_id') &&
            !$this->isPaid()
        ) {
            $paymentMethods = $this->getPaymentMethods('direct');

            if (count($paymentMethods) > 0) {
                return $paymentMethods;
            }
        }

        return false;
    }

    /**
     *
     * set expires at flag
     * used by the force payment function
     *
     * @param bool $reset
     *
     * @return $this
     */
    public function setExpiresFlag($reset = false)
    {
        $settings = $this->getSettings();

        if ($reset) {
            $flag = new Expr('null');
        }
        else {
            $flag = new Expr('now() + interval ' . $settings['force_payment_limit'] . ' minute');
        }

        $this->save(array(
            'expires_at' => $flag,
        ));

        $this->_data['expires_at'] = ($reset) ? null : date('Y-m-d H:i:s', time() + $settings['force_payment_limit'] * 60);

        return $this;
    }

    /**
     *
     * get the insurance amount that will apply to the sale
     *
     * @return float|null
     */
    public function getInsuranceAmount()
    {
        if ($this->getData('apply_insurance')) {
            return $this->getData('insurance_amount');
        }

        return null;
    }

    /**
     *
     * check if postage is available for the selected sale
     * this flag is independent on the enable shipping global setting, to preserve the postage amount in case the
     * global setting was changed
     *
     *
     * @return bool
     */
    public function hasPostage()
    {
        $settings = $this->getSettings();

        return ($this->getData('enable_shipping') ||
            ($settings['enable_shipping'] && $this->getData('pending'))) ? true : false;
    }

    /**
     *
     * get the postage method for the sale
     *
     * @return string
     */
    public function getPostageMethod()
    {
        if (isset($this->_data['postage']['method'])) {
            return $this->_data['postage']['method'];
        }

        return 'N/A';
    }

    /**
     *
     * get payment status description
     *
     * @return string
     */
    public function getPaymentStatusDescription()
    {
        if (array_key_exists($this->_data['flag_payment'], self::$paymentStatuses)) {
            return self::$paymentStatuses[$this->_data['flag_payment']];
        }

        return 'N/A';
    }

    /**
     *
     * get the sale transaction row object or null if no transaction exists
     *
     * @return \Cube\Db\Table\Row|null
     */
    public function getSaleTransaction()
    {
        $select = $this->getTable()->select()
            ->where('paid = ?', 1);
        $transaction = $this->findDependentRowset('\Ppb\Db\Table\Transactions', null, $select)->getRow(0);

        return $transaction;
    }

    /**
     *
     * function that checks if the sale invoice can be viewed
     * only the seller or the buyer can view a sale invoice
     *
     * @return bool
     */
    public function canView()
    {
        if (!count($this) || !$this->isActive()) {
            return false;
        }

        $user = $this->getUser();

        if (in_array($user['id'], array($this->getData('seller_id'), $this->getData('buyer_id')))) {
            return true;
        }

        return false;
    }

    /**
     *
     * checks if the messaging feature is enabled for the sale
     *
     * @return bool
     */
    public function messagingEnabled()
    {
        $settings = $this->getSettings();

        if ($settings['enable_messaging']) {
            return true;
        }

        return false;
    }

    /**
     *
     * create a link to the messaging controller
     * if we have a topic, redirect to the topic, otherwise create a new topic
     *
     * @return array
     */
    public function messagingLink()
    {
        $action = ($this->getData('messaging_topic_id')) ? 'topic' : 'create';
        $array = array(
            'module'     => 'members',
            'controller' => 'messaging',
            'action'     => $action,
        );

        if ($action == 'topic') {
            $array['id'] = $this->getData('messaging_topic_id');
        }
        else {
            $array['sale_id'] = $this->getData('id');
            $array['topic_type'] = Service\Messaging::SALE_TRANSACTION;
        }

        return $array;
    }

    /**
     *
     * check if the buyer can checkout the cart
     * - logged in user needs to be different than the seller
     * - check for available inventory
     * - if shipping is enabled, check if the item shipping to the buyer's selected address
     * - we need to have a shopping cart
     *
     * @return bool|string  true if ok, a message otherwise
     */
    public function canCheckout()
    {
        $translate = $this->getTranslate();
        $user = $this->getUser();

        $userId = (!empty($user['id'])) ? $user['id'] : null;

        if (!$this->getData('pending')) {
            return sprintf($translate->_('Invalid shopping cart selected.'));
        }
        else if ($userId == $this->getData('seller_id')) {
            return sprintf($translate->_('You cannot purchase your own products.'));
        }

        return true;
    }

    /**
     *
     * checks whether the logged in user can pay sale fee or not
     *
     * @return bool
     */
    public function canPayFee()
    {
        if ($this->isActive()) {
            return false;
        }

        $settings = $this->getSettings();
        $user = $this->getUser();

        switch ($settings['sale_fee_payer']) {
            case 'buyer':
                if ($user['id'] == $this->getData('buyer_id')) {
                    return true;
                }
                break;
            case 'seller':
                if ($user['id'] == $this->getData('seller_id')) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     *
     * determine and set the 'active' flag for the sale
     *
     * @param int $active
     *
     * @return $this
     */
    public function updateActive($active = 1)
    {
        $this->save(array(
            'active' => (int)$active,
        ));

        return $this;
    }

    /**
     *
     * update payment and shipping statuses for the sale object
     * also reset expires at flag if payment status is marked as paid - one way action
     *
     * @param array $post
     *
     * @return $this
     */
    public function updateStatus($post)
    {
        $saleData = (array)$this->getData('sale_data');
        $saleData['tracking_link'] = $post['tracking_link'];
//        $saleData['shipping_comments'] = $post['shipping_comments'];

        $this->saveSaleData(array(
            'tracking_link' => $post['tracking_link'],
//            'shipping_comments' => $post['shipping_comments']
        ));

        $data = array(
            'flag_shipping' => $post['flag_shipping'],
            'flag_payment'  => $post['flag_payment'],
        );

        if ($post['flag_payment'] != self::PAYMENT_UNPAID) {
            $this->setExpiresFlag(true);
        }

        parent::save($data);

        return $this;
    }

    /**
     *
     * save sale data serialized field
     *
     * @param array $post
     *
     * @return $this
     */
    public function saveSaleData($post)
    {
        $settings = $this->getSettings();

        $saleData = array_merge((array)\Ppb\Utility::unserialize($this->getData('sale_data')), $post);

        foreach (self::$saleDataKeys as $key) {
            if (!array_key_exists($key, $saleData)) {
                $saleData[$key] = null;
//                throw new \InvalidArgumentException(
//                    sprintf("The key '%s' does not exist in the sale data array.", $key));
            }
        }

        $saleData['enable_shipping'] = $settings['enable_shipping'];

        $data = array(
            'sale_data' => serialize($saleData),
        );

        parent::save($data);

        return $this;
    }

    /**
     *
     * save serialized voucher details
     *
     * @param string|\Ppb\Db\Table\Row\Voucher $voucher voucher code or voucher object
     *
     * @return $this
     */
    public function saveVoucherDetails($voucher)
    {
        if (!$voucher instanceof Voucher) {
            $vouchersService = new Service\Vouchers();
            $voucher = $vouchersService->findBy($voucher, $this->getData('seller_id'));
        }

        if ($voucher instanceof Voucher) {
            $voucher = ($voucher->isValid()) ? serialize($voucher->getData()) : null;
        }

        $this->save(array(
            'voucher_details' => $voucher
        ));

        return $this;
    }

    /**
     *
     * update sales listings quantities
     * doesnt check for available quantity - this needs to be checked in the form
     * it is also checked in the canCheckout method
     *
     * @param array $quantities
     *
     * @return $this
     */
    public function updateQuantities(array $quantities)
    {
        $salesListings = $this->getSalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $key = $saleListing['id'];
            if (array_key_exists($key, $quantities)) {
                $quantity = ($quantities[$key] > 1) ? $quantities[$key] : 1;
                $saleListing->save(array(
                    'quantity' => $quantity,
                ));
            }
        }

        return $this;
    }

    /**
     *
     * revert the sale then delete it
     * the following actions will be taken:
     * - the quantities of the listings will be reset
     * - the sale transaction fee will be refunded (if payer in account mode)
     * - delete related reputation table rows
     * - TODO: if listings were closed ahead of time - reopen them
     *
     * @return $this
     */
    public function revert()
    {
        // revert quantities for each listing in the sale
        $salesListings = $this->getSalesListings();

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            /** @var \Ppb\Db\Table\Row\Listing $listing */
            $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');

            if ($listing instanceof Listing) {
                $listing->updateQuantity($saleListing->getData('quantity'), $saleListing['product_attributes'], Listing::ADD);
            }

            // delete the reputation rows for each sale listing in the sale
            $saleListing->findDependentRowset('\Ppb\Db\Table\Reputation')->delete();
        }

        // refund sale transaction fee(s)
        $accountingRowset = $this->findDependentRowset('\Ppb\Db\Table\Accounting');

        /** @var \Ppb\Db\Table\Row\Accounting $accounting */
        foreach ($accountingRowset as $accounting) {
            $accounting->acceptRefundRequest(true);
        }

        $this->delete(true);


        return $this;
    }

    /**
     *
     * mark deleted if user is buyer or seller, or remove from database if admin
     *
     * @param bool $admin
     *
     * @return int|bool
     */
    public function delete($admin = false)
    {
        if ($admin === true) {
            return parent::delete();
        }

        $user = $this->getUser();

        if ($user['id'] == $this->getData('seller_id')) {
            $this->save(array(
                'seller_deleted' => 1
            ));

            return true;
        }
        else if ($user['id'] == $this->getData('buyer_id')) {
            $this->save(array(
                'buyer_deleted' => 1
            ));

            return true;
        }

        return false;
    }
}
