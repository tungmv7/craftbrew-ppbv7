<?php

/**
 *
 * PHP Pro Bid $Id$ LVV7AGHt1BcOduXJ7w6u4zi6dS15kNgKOo3MchpM+og=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * sale direct payment class
 */

namespace Ppb\Service\Fees;

use Ppb\Service,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\Sale as SaleModel;

class DirectPayment extends Service\Fees
{

    /**
     *
     * sale object
     *
     * @var \Ppb\Db\Table\Row\Sale
     */
    protected $_sale;

    /**
     *
     * total amount to be paid
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
        'params'     => array(
            'type' => 'bought',
        ),
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
     *
     * @param \Ppb\Db\Table\Row\Sale $sale
     *
     * @return $this
     */
    public function setSale(SaleModel $sale)
    {
        $this->_sale = $sale;
        $this->_totalAmount = $sale->calculateTotal();

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
     * get redirect array, but attach sale_id variable if it is set
     *
     * @return array
     */
    public function getRedirect()
    {
        $redirect = $this->_redirect;
        if (!empty($this->_transactionDetails['data']['sale_id'])) {
            $redirect['params']['sale_id'] = $this->_transactionDetails['data']['sale_id'];
        }

        return $redirect;
    }

    /**
     *
     * mark the sale as paid with direct payment
     * if ipn returns false, mark the listing as unpaid
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post array keys: {sale_id}
     *
     * @return \Ppb\Service\Fees\SaleTransaction
     */
    public function callback($ipn, array $post)
    {
        $salesService = new Service\Sales();
        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $salesService->findBy('id', $post['sale_id']);

        $flag = ($ipn) ? SaleModel::PAYMENT_PAID_DIRECT_PAYMENT : 0;

        $sale->save(array(
            'flag_payment' => $flag,
        ));

        if ($ipn) {
            $sale->setExpiresFlag(true);

            $user = Front::getInstance()->getBootstrap()->getResource('user');

            $type = null;
            if (!empty($user['id'])) {
                $type = ($sale['buyer_id'] == $user['id']) ? 'bought' : 'sold';
            }

            $this->setRedirect(array(
                'module'     => 'members',
                'controller' => 'invoices',
                'action'     => 'browse',
                'params'     => array(
                    'type'    => $type,
                    'sale_id' => $post['sale_id'],
                ),
            ));
        }

        /** @var \Ppb\Db\Table\Row\User $seller */
        $seller = $sale->findParentRow('\Ppb\Db\Table\Users', 'Seller');
        $automaticDigitalDownloads = $seller->getGlobalSettings('automatic_digital_downloads');

        if ($flag == 0 || $automaticDigitalDownloads != -1) {
            $downloadsFlag = ($flag) ? 1 : 0;
            $sale->findDependentRowset('\Ppb\Db\Table\SalesListings')->save(array(
                'downloads_active' => $downloadsFlag,
            ));
        }


        return $this;
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

}

