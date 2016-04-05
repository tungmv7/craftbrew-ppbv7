<?php

/**
 *
 * PHP Pro Bid $Id$ uSz9e2Ri1v7WtPrhzWXcxQ7XAVWaamYF3Srj0ZTarsw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * sale transaction fee class
 */

namespace Ppb\Service\Fees;

use Ppb\Service,
    Ppb\Db\Table\Row\Sale as SaleModel;

class SaleTransaction extends Service\Fees
{

    /**
     *
     * fees to be included
     *
     * @var array
     */
    protected $_fees = array(
        self::SALE => 'Sale Transaction Fee',
    );

    /**
     *
     * sale object
     *
     * @var \Ppb\Db\Table\Row\Sale
     */
    protected $_sale;

    /**
     *
     * total amount to be paid after the calculate method is called
     *
     * @var float
     */
    protected $_totalAmount;

    /**
     *
     * payment redirect path
     *
     * @var array
     */
    protected $_redirect = array(
        'module'     => 'members',
        'controller' => 'invoices',
        'action'     => 'browse',
    );

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\Sale                $sale
     * @param integer|string|\Ppb\Db\Table\Row\User $user the user that will be paying
     */
    public function __construct(SaleModel $sale = null, $user = null)
    {
        parent::__construct();

        if ($sale !== null) {
            $this->setSale($sale);
        }

        if ($user !== null) {
            $this->setUser($user);
        }
    }

    /**
     *
     * set sale model
     * also, based on the sale model, set the total amount that will be used to calculate the fees against
     * the amount will be the total amount without tax, postage and insurance
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return $this
     */
    public function setSale(SaleModel $sale)
    {
        $this->_sale = $sale;

        $this->setAmount(
            $sale->calculateTotal(true));

        return $this;
    }

    /**
     *
     * get sale model
     *
     * @return \Ppb\Db\Table\Row\Sale
     */
    public function getSale()
    {
        return $this->_sale;
    }

    /**
     *
     * calculate and return an array containing all fees to be applied for the sale in question
     *
     * @return array
     */
    public function calculate()
    {
        $data = array();
        $this->_totalAmount = 0;

        $settings = $this->getSettings();

        foreach ($this->_fees as $key => $value) {
            $feeAmount = $this->getFeeAmount($key);

            if ($feeAmount > 0 || $settings['display_free_fees']) {
                $data[] = array(
                    'key'      => $key,
                    'name'     => array(
                        'string' => 'Sale Transaction Fee - Sale ID: #%s',
                        'args'   => array($this->_sale['id']),
                    ),
                    'amount'   => $feeAmount,
                    'tax_rate' => $this->getTaxType()->getData('amount'),
                    'currency' => $settings['currency'],
                );
            }

            $this->_totalAmount += $feeAmount;
        }

        return $data;
    }

    /**
     *
     * get redirect array, and attach type and sale_id variables
     *
     * @return array
     */
    public function getRedirect()
    {
        $settings = $this->getSettings();

        $redirect = $this->_redirect;

        $redirect['params']['type'] = ($settings['sale_fee_payer'] == 'buyer') ? 'bought' : 'sold';

        if (!empty($this->_transactionDetails['data']['sale_id'])) {
            $redirect['params']['sale_id'] = $this->_transactionDetails['data']['sale_id'];
        }

        return $redirect;
    }

    /**
     *
     * activate the affected sale
     * the callback will also process the listing in case a payment has been reversed etc
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post array keys: {sale_id}
     *
     * @return \Ppb\Service\Fees\SaleTransaction
     */
    public function callback($ipn, array $post)
    {
        $salesService = new Service\Sales();
        $sale = $salesService->findBy('id', $post['sale_id']);

        $flag = ($ipn) ? 1 : 0;
        $sale->save(array(
            'active' => $flag,
        ));

        return $this;
    }


    /**
     *
     * apply sale transaction related fees. for the sale transaction fee, it will be calculated for each listing so that
     * category specific fees are used properly.
     *
     * @param string $name
     * @param float  $amount
     * @param int    $categoryId
     * @param null   $currency
     *
     * @return float|null       return the fee amount or null if no fee applies for the selected action
     */
    public function getFeeAmount($name = null, $amount = null, $categoryId = null, $currency = null)
    {
        if ($name == self::SALE) {
            $sale = $this->getSale();
            $salesListings = $sale->findDependentRowset('\Ppb\Db\Table\SalesListings');

            $feeAmount = 0;
            $currency = $sale->getData('currency');

            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            foreach ($salesListings as $saleListing) {
                $listing = $saleListing->findParentRow('\Ppb\Db\Table\Listings');
                $amount = $saleListing->calculateTotal(false);
                $categoryId = $listing->getData('category_id');

                $feeAmount += parent::getFeeAmount($name, $amount, $categoryId, $currency);
            }
        }
        else {
            $feeAmount = parent::getFeeAmount($name, $amount, $categoryId, $currency);
        }

        return $feeAmount;
    }

    /**
     *
     * get total amount to be paid resulted from the calculate() method
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->_totalAmount;
    }

    /**
     *
     * only apply preferred sellers reduction if it should apply to sale fees
     *
     * @param float $amount
     *
     * @return float
     */
    protected function _applyPreferredSellersReduction($amount)
    {
        $settings = $this->getSettings();

        if ($settings['preferred_sellers_apply_sale']) {
            $amount = parent::_applyPreferredSellersReduction($amount);
        }

        return $amount;
    }


}

