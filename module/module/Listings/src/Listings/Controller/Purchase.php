<?php

/**
 *
 * PHP Pro Bid $Id$ YUN5gA3I4YBHPdQU1wRozcxHjkkBiNVz3bIhfzWk9+M=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Listings\Form,
    Ppb\Service,
    Ppb\Db\Table\Row\Voucher as VoucherModel;

class Purchase extends AbstractAction
{

    /**
     *
     * listing model
     * using select for update so that the listing can be altered by a single transaction at the same time.
     *
     * @var \Ppb\Db\Table\Row\Listing
     */
    protected $_listing;

    /**
     *
     * form type to generate
     * (bid|buy|offer)
     *
     * @var string
     */
    protected $_type;

    /**
     *
     * allowed form types
     *
     * @var array
     */
    protected $_allowedTypes = array('bid', 'buy', 'offer');

    public function init()
    {
        $listingsService = new Service\Listings();
        $this->_listing = $listingsService->fetchAll(
            $listingsService->getTable()->select()
                ->forUpdate()
                ->where('id = ?', (int)$this->getRequest()->getParam('id')))
            ->getRow(0);

        $this->_type = $this->getRequest()->getParam('type');
        if (!in_array($this->_type, $this->_allowedTypes)) {
            $this->_type = $this->_allowedTypes[0];
        }
    }

    public function Confirm()
    {
        $canPurchase = $this->_listing->canPurchase($this->_type);

        if ($canPurchase !== true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $canPurchase,
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('details', 'listing', null, $this->getRequest()->getParams());
        }

        /** @var \Ppb\Db\Table\Row\User $buyer */
        $buyer = Front::getInstance()->getBootstrap()->getResource('user');
        $buyer->setAddress(
            $this->getRequest()->getParam('shipping_address_id'));

        $form = new Form\Purchase($this->_listing, $buyer, $this->_type);
        $headline = $form->getTitle();


        $params = $this->getRequest()->getParams();
        $quantity = $this->getRequest()->getParam('quantity');

        $voucherDetails = null;
        $price = $this->_listing->getData('buyout_price');
        if ($voucherCode = $this->getRequest()->getParam('voucher_code')) {
            $vouchersService = new Service\Vouchers();
            $voucher = $vouchersService->findBy($voucherCode, $this->_listing->getData('user_id'));

            if ($voucher instanceof VoucherModel) {
                if ($voucher->isValid()) {
                    $voucherDetails = serialize($voucher->getData());
                    $price = $voucher->apply($price, $this->_listing->getData('currency'), $this->_listing->getData('id'));
                }
            }
        }

        $form->setData($params);

        if ($form->isPost(
            $this->getRequest())
        ) {

            if ($form->isValid() === true) {
                // save product attributes
                $productAttributes = $this->getRequest()->getParam('product_attributes');

                $data = array(
                    'quantity'            => $quantity,
                    'amount'              => $this->getRequest()->getParam('amount'),
                    'shipping_address_id' => $this->getRequest()->getParam('shipping_address_id'),
                    'postage_id'          => $this->getRequest()->getParam('postage_id'),
                    'apply_insurance'     => $this->getRequest()->getParam('apply_insurance'),
                    'voucher_details'     => $voucherDetails,
                    'product_attributes'  => (count($productAttributes) > 0) ? serialize($productAttributes) : null
            );

                $message = $this->_listing->placeBid($data, $this->_type);

                $this->_helper->redirector()->redirect('success', null, null, array(
                    'id'       => $this->_listing['id'],
                    'sale_id'  => $this->_listing->getSaleId(),
                    'type'     => $this->_type,
                    'quantity' => $quantity,
                    'message'  => urlencode($message),
                ));
            }
            else {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $form->getMessages(),
                    'class' => 'alert-danger',
                ));
            }
        }

        return array(
            'form'     => $form,
            'type'     => $this->_type,
            'headline' => $headline,
            'listing'  => $this->_listing,
            'price'    => $price,
            'quantity' => $quantity,
            'user'     => $buyer, // buyer
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    public function Success()
    {
        $headline = null;

        $buyer = Front::getInstance()->getBootstrap()->getResource('user');

        switch ($this->_type) {
            case 'bid':
                $headline = $this->_('Bidding Successful');
                break;
            case 'buy':
                // redirect to the purchase success page, just like the shopping cart checkout action
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $this->_('Thank you for your purchase.'),
                    'class' => 'alert-success',
                ));

                $salesService = new Service\Sales();
                /** @var \Ppb\Db\Table\Row\Sale $sale */
                $sale = $salesService->findBy('id', $this->getRequest()->getParam('sale_id'));

                if ($sale->isActive()) {
                    $this->_helper->redirector()->redirect('direct-payment', 'payment', 'app',
                        array('id' => $sale['id']));
                }
                else {
                    $this->_helper->redirector()->redirect('browse', 'invoices', 'members',
                        array('type' => 'bought', 'sale_id' => $sale['id']));
                }

                break;
            case 'offer':
                $headline = $this->_('Offer Posted Successfully');
                break;
        }


        return array(
            'headline' => $headline,
            'listing'  => $this->_listing,
            'user'     => $buyer,
            'quantity' => $this->getRequest()->getParam('quantity', 1),
            'message'  => $this->getRequest()->getParam('message'),
        );
    }

}

