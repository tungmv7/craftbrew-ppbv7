<?php

/**
 *
 * PHP Pro Bid $Id$ JDaadX+MNOkPeQ8o93gQsEoYEMFUHNZIArZxlx2oIPk=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */

namespace Listings\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Listings\Form,
    Ppb\Service,
    Ppb\Model\Elements\User\CartCheckout,
    Cube\Authentication\Authentication,
    Ppb\Authentication\Adapter,
    Ppb\Db\Table\Row\User as UserModel;

class Cart extends AbstractAction
{

    /**
     *
     * sales/cart service
     *
     * @var \Ppb\Service\Sales
     */
    protected $_sales;

    /**
     *
     * user token
     *
     * @var string
     */
    protected $_userToken = null;

    public function init()
    {
        $this->_sales = new Service\Sales();

        $bootstrap = Front::getInstance()->getBootstrap();

        $this->_userToken = strval($bootstrap->getResource('session')->getCookie(UserModel::USER_TOKEN));
    }

    /**
     *
     * view and manage a shopping cart
     * will allow the removal of products, editing of quantities, selection of shipping address and selection of shipping method
     *
     * @return array
     */
    public function Index()
    {
        $id = $this->getRequest()->getParam('id');

        $multiOptions = $this->_sales->getMultiOptions($this->_userToken);

        $saleId = (in_array($id, array_keys($multiOptions))) ? $id : current(array_keys($multiOptions));

        $form = null;
        if ($saleId) {
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->_sales->findBy('id', $saleId);
            $form = new Form\Cart($sale);

            $form->setData(
                $this->getRequest()->getParams());

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid()) {
                    if ($this->getRequest()->getParam(Form\Cart::BTN_UPDATE_CART)) {
                        $this->_flashMessenger->setMessage(array(
                            'msg'   => $this->_('The shopping cart has been updated successfully.'),
                            'class' => 'alert-success',
                        ));

                        $sale->updateQuantities(
                            $this->getRequest()->getParam('quantity'));

                        $this->_helper->redirector()->redirect('index', null, null, array('id' => $saleId));
                    }
                    else if ($this->getRequest()->getParam(Form\Cart::BTN_CHECKOUT)) {
                        $this->_helper->redirector()->redirect('checkout', null, null, array('id' => $saleId));
                    }
                }

                if (count($form->getMessages())) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }


        return array(
            'headline'     => $this->_('Shopping Cart'),
            'id'           => $saleId,
            'multiOptions' => $multiOptions,
            'form'         => $form,
            'messages'     => $this->_flashMessenger->getMessages(),
        );
    }

    public function Checkout()
    {
        $id = $this->getRequest()->getParam('id');

        $multiOptions = $this->_sales->getMultiOptions($this->_userToken);


        if (in_array($id, array_keys($multiOptions))) {
            /** @var \Ppb\Db\Table\Row\Sale $sale */
            $sale = $this->_sales->findBy('id', $id);
            $canCheckout = $sale->canCheckout();

            if ($canCheckout !== true) {
                $this->_flashMessenger->setMessage(array(
                    'msg'   => $canCheckout,
                    'class' => 'alert-danger',
                ));

                $this->_helper->redirector()->redirect('index', null, null, array('id' => $id));
            }

            $form = new Form\Checkout($sale, null);

            $params = $this->getRequest()->getParams();
            $form->setData($params);


            if ($this->getRequest()->getParam('voucher_add')) {
                $sale->saveVoucherDetails($this->getRequest()->getParam('voucher_code'));
            }

            if ($form->isPost(
                $this->getRequest())
            ) {
                if ($form->isValid() === true) {
                    $usersService = new Service\Users();

                    if (!$this->_user) {
                        // create new user if the user is not registered
                        $userId = $usersService->save(array_merge($params, array(
                            'payment_status' => 'confirmed',
                            'active'         => 1,
                            'approved'       => 1,
                            'mail_activated' => 1,
                        )));

                        // send new account notification email to the newly registered user.
                        $mail = new \Members\Model\Mail\Register($params);
                        $mail->registerDefault()->send();

                        // log user in
                        Authentication::getInstance()->authenticate(
                            new Adapter(array(), $userId));
                    }
                    else {
                        $userId = $this->_user['id'];
                    }

                    // save shipping/billing addresses if new
                    $billingAddressId = $this->getRequest()->getParam(CartCheckout::PRF_BLG . 'address_id');
                    if (!$billingAddressId) {
                        $billingAddressId = $usersService->getUsersAddressBook()->save($params, $userId,
                            CartCheckout::PRF_BLG);
                    }

                    if ($this->getRequest()->getParam('alt_ship')) {
                        $shippingAddressId = $this->getRequest()->getParam(CartCheckout::PRF_SHP . 'address_id');
                        if (!$shippingAddressId) {
                            $shippingAddressId = $usersService->getUsersAddressBook()->save($params, $userId,
                                CartCheckout::PRF_SHP);
                        }
                    }
                    else {
                        $shippingAddressId = $billingAddressId;
                    }

                    // TODO: save the shipping addresses, postage data etc in the sale

                    $params = array_merge($params, array(
                        'buyer_id'            => $userId,
                        'seller_id'           => $sale['seller_id'],
                        'pending'             => 0,
                        'billing_address_id'  => $billingAddressId,
                        'shipping_address_id' => $shippingAddressId,
                        'checkout'            => true,
                    ));

                    $this->_sales->save($params);

                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $this->_('Thank you for your purchase.'),
                        'class' => 'alert-success',
                    ));

                    // get the sale object again, we need that to be able to use all updated fields
                    $sale = $this->_sales->findBy('id', $id);

                    if ($sale->isActive()) {
                        $this->_helper->redirector()->redirect('direct-payment', 'payment', 'app',
                            array('id' => $sale['id']));
                    }
                    else {
                        $this->_helper->redirector()->redirect('browse', 'invoices', 'members',
                            array('type' => 'bought', 'sale_id' => $sale['id']));
                    }

                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $form->getMessages(),
                        'class' => 'alert-danger',
                    ));
                }
            }
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The shopping cart you have selected doesnt exist.'),
                'class' => 'alert-danger',
            ));
            $this->_helper->redirector()->redirect('index');
        }

        return array(
            'headline' => $this->_('Checkout'),
            'form'     => $form,
            'messages' => $this->_flashMessenger->getMessages(),
        );
    }

    /**
     *
     * add a product into a shopping cart
     * using select for update so that the listing can be altered by a single transaction at the same time.
     *
     * @return array
     */
    public function Add()
    {
        $quantity = (($quantity = $this->getRequest()->getParam('quantity')) < 1) ? 1 : $quantity;

        $listingsService = new Service\Listings();
        /** @var \Ppb\Db\Table\Row\Listing $listing */
        $listing = $listingsService->fetchAll(
            $listingsService->getTable()->select()
                ->forUpdate()
                ->where('id = ?', (int)$this->getRequest()->getParam('id')))
            ->getRow(0);

        $canAddToCart = $listing->canAddToCart($quantity, $this->getRequest()->getParam('product_attributes'));

        if ($canAddToCart !== true) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $canAddToCart,
                'class' => 'alert-danger',
            ));

            $this->_helper->redirector()->redirect('details', 'listing', null, $this->getRequest()->getParams());
        }
        else {
            $listing->addToCart(
                $quantity, $this->getRequest()->getParam('product_attributes'));

            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The product has been added to the shopping cart.'),
                'class' => 'alert-success',
            ));

            $this->_helper->redirector()->redirect('index');
        }


    }

    public function Delete()
    {
        $id = $this->getRequest()->getParam('item_id');

        $salesListings = new Service\Table\SalesListings();

        $result = $salesListings->deleteOne($id, $this->_userToken);

        if ($result === false) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Could not remove the selected product.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('The product has been removed from the shopping cart.'),
                'class' => 'alert-success',
            ));
        }
        $this->_helper->redirector()->redirect('index');
    }

}

