<?php

/**
 *
 * PHP Pro Bid $Id$ ddGQGqaXRzN/3Nfmu8BSddU45A/0nAIon/DWjbQ26Fc=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * payment forms controller
 */

namespace App\Controller;

use Ppb\Controller\Action\AbstractAction,
    Cube\Controller\Front,
    Ppb\Service,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Db\Table\Row\Transaction as TransactionModel,
    App\Form;

class Payment extends AbstractAction
{

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     *
     * transactions service
     *
     * @var \Ppb\Service\Transactions
     */
    protected $_transactions;

    /**
     *
     * payment gateways service
     *
     * @var \Ppb\Service\Table\PaymentGateways
     */
    protected $_paymentGateways;

    public function init()
    {
        $bootstrap = Front::getInstance()->getBootstrap();

        $this->_view = $bootstrap->getResource('view');
        $this->_view->setViewFileName('generic-page.phtml');

        $this->_transactions = new Service\Transactions();
        $this->_paymentGateways = new Service\Table\PaymentGateways();
    }

    public function Index()
    {
        return array();
    }

    /**
     *
     * user registration signup fee action
     *
     * @return array
     */
    public function UserSignup()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (!empty($this->_user['id'])) ?
            $this->_user['id'] : $this->getRequest()->getParam('id');
        $usersService = new Service\Users();

        $user = $usersService->findBy('id', (int)$id);

        if (count($user) > 0) {
            if ($user->canPaySignupFee()) {
                $feesService = new Service\Fees\UserSignup();
                $feesService->setUser($user);

                $totalAmount = $feesService->getTotalAmount();

                if ($totalAmount > 0) {
                    $paymentDescription = sprintf(
                        $translate->_('Please use one of the payment gateways listed below to pay the user signup fee of %s'),
                        $this->_view->amount($totalAmount));

                    $transaction = array(
                        'name'                => array(
                            'string' => 'User Signup Fee - User ID: #%s',
                            'args'   => array($id),
                        ),
                        'amount'              => $totalAmount,
                        'tax_rate'            => $feesService->getTaxType()->getData('amount'),
                        'currency'            => $this->_settings['currency'],
                        'user_id'             => $id,
                        'transaction_details' => serialize(array(
                            'class' => '\\Ppb\\Service\\Fees\\UserSignup',
                            'data'  => array(
                                'user_id' => $id,
                            ),
                        )),
                    );

                    $transactionId = $this->_transactions->save($transaction);

                    $gatewayForms = $this->_prepareForms($transactionId, null, null, $user);
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("User #%s - '%s', has been activated. There were no fees to be paid."),
                            $user['id'], $user['username']),
                        'class' => 'alert-success',
                    ));

                    $user->save(array(
                        'active'         => 1,
                        'payment_status' => 'confirmed',
                    ));

                    $gatewayForms = array();
                }
            }
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the user does not exist or there is no signup fee to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => sprintf($translate->_('Payment - User Signup - ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    /**
     *
     * listing setup fee action
     *
     * @return array
     */
    public function ListingSetup()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $listingsService = new Service\Listings();

        $listing = $listingsService->findBy('id', (int)$id, false, true);

        if ($listing->isOwner() && $listing['active'] == 0) {
            $listingSetupService = new Service\Fees\ListingSetup(
                $listing, $this->_user);

            $savedListing = \Ppb\Utility::unserialize($listing['rollback_data']);
            if ($savedListing instanceof ListingModel) {
                $listingSetupService->setSavedListing($savedListing);
            }

            $listingSetupService->setVoucher(
                $listing->getData('voucher_code'));

            $listingFees = $listingSetupService->calculate();

            $totalAmount = $listingSetupService->getTotalAmount();
            $userPaymentMode = $this->_user->userPaymentMode();

            if ($userPaymentMode == 'live' && $totalAmount > 0) {
                $paymentDescription = sprintf(
                    $translate->_('Please use one of the payment gateways listed below to pay the listing fee of %s'),
                    $this->_view->amount($totalAmount));

                $transaction = array(
                    'name'                => array(
                        'string' => 'Listing Setup Fee - Listing ID: #%s',
                        'args'   => array($id),
                    ),
                    'amount'              => $totalAmount,
                    'tax_rate'            => $listingSetupService->getTaxType()->getData('amount'),
                    'currency'            => $this->_settings['currency'],
                    'user_id'             => $listing['user_id'],
                    'transaction_details' => serialize(array(
                        'class' => '\\Ppb\\Service\\Fees\\ListingSetup',
                        'data'  => array(
                            'listing_id' => $listing['id'],
                        ),
                    )),
                );

                $transactionId = $this->_transactions->save($transaction);

                $gatewayForms = $this->_prepareForms($transactionId);
            }
            else {
                $message = $listing->processPostSetupActions();

                if ($message) {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => $message,
                        'class' => 'alert-info',
                    ));
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("Listing #%s - '%s', has been activated. There were no fees to be paid."),
                            $listing['id'], $listing['name']),
                        'class' => 'alert-success',
                    ));
                }

                $gatewayForms = array();
            }
        }


        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the listing does not exist or there are no fees to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => sprintf($translate->_('Payment - Listing Setup - ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function SaleTransaction()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $salesService = new Service\Sales();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $sale = $salesService->findBy('id', (int)$id);

        if ($sale->canPayFee()) {
            $saleTransactionService = new Service\Fees\SaleTransaction(
                $sale, $this->_user);

            $saleFees = $saleTransactionService->calculate();

            $totalAmount = $saleTransactionService->getTotalAmount();
            $userPaymentMode = $this->_user->userPaymentMode();

            if ($userPaymentMode == 'live') {
                if ($totalAmount > 0) {
                    $paymentDescription = sprintf(
                        $translate->_('Please use one of the payment gateways listed below to pay the sale transaction fee of %s'),
                        $this->_view->amount($totalAmount));

                    $transaction = array(
                        'name'                => array(
                            'string' => 'Sale Transaction Fee - Sale ID: #%s',
                            'args'   => array($id),
                        ),
                        'amount'              => $totalAmount,
                        'tax_rate'            => $saleTransactionService->getTaxType()->getData('amount'),
                        'currency'            => $this->_settings['currency'],
                        'user_id'             => $this->_user['id'],
                        'transaction_details' => serialize(array(
                            'class' => '\\Ppb\\Service\\Fees\\SaleTransaction',
                            'data'  => array(
                                'sale_id' => $sale['id'],
                            ),
                        )),
                    );

                    $transactionId = $this->_transactions->save($transaction);

                    $gatewayForms = $this->_prepareForms($transactionId);
                }
                else {
                    $this->_flashMessenger->setMessage(array(
                        'msg'   => sprintf(
                            $translate->_("The sale transaction #%s has been activated. There were no fees to be paid."),
                            $sale['id']),
                        'class' => 'alert-success',
                    ));

                    $sale->updateActive();

                    $gatewayForms = array();
                }
            }
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the sale does not exist or there are no fees to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => sprintf($translate->_('Payment - Sale Fee - Sale ID: #%s'), $id),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    /**
     *
     * sale direct payment action
     *
     * @return array
     */
    public function DirectPayment()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (int)$this->getRequest()->getParam('id');
        $salesService = new Service\Sales();

        /** @var \Ppb\Db\Table\Row\Sale $sale */
        $select = $salesService->getTable()->select()
            ->where('id = ?', $id)
            ->where('buyer_id = ?', $this->_user['id']);
        $sale = $salesService->getTable()->fetchRow($select);

        if (!$sale) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the sale does not exist, or you are not the buyer.'),
                'class' => 'alert-danger',
            ));
        }
        else {
            $gatewayContent = $this->_view->partial('partials/sale.phtml',
                array('sale'        => $sale,
                      'type'        => 'bought',
                      'caption'     => true,
                      'postageDesc' => true));

            if (($paymentMethods = $sale->canPayDirectPayment()) !== false) {
                $totalAmount = $sale->calculateTotal();

                $paymentDescription = sprintf(
                    $translate->_('Please use one of the payment gateways listed below to pay the seller the amount of %s'),
                    $this->_view->amount($totalAmount, $sale['currency']));

                $transaction = array(
                    'name'                => array(
                        'string' => 'Direct Payment Purchase - Invoice ID: #%s',
                        'args'   => array($id),
                    ),
                    'amount'              => $totalAmount,
                    'currency'            => $sale['currency'],
                    'user_id'             => $this->_user['id'],
                    'sale_id'             => $sale['id'],
                    'transaction_details' => serialize(array(
                        'class' => '\\Ppb\\Service\\Fees\\DirectPayment',
                        'data'  => array(
                            'sale_id' => $sale['id'],
                        ),
                    )),
                );

                $transactionId = $this->_transactions->save($transaction);

                $gatewayIds = array();
                $paymentMethods = $sale->getPaymentMethods('direct');
                foreach ($paymentMethods as $paymentMethod) {
                    $gatewayIds[] = $paymentMethod['id'];
                }
                $gatewayForms = $this->_prepareForms($transactionId, $sale['seller_id'], $gatewayIds);
            }
        }

        return array(
            'headline'           => $this->_('Purchase Details'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'gatewayContent'     => $gatewayContent,
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function CreditBalance()
    {
        $totalAmount = 0;
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        if (isset($this->_user['id'])) {
            if ($this->_user->userPaymentMode() == 'account') {

                $amount = $this->getRequest()->getParam('amount');
                if (!$amount || $this->_user['balance'] > 0) {
                    $amount = $this->_user['balance'];
                }

                $totalAmount = ($amount > 0) ?
                    max(array($amount, $this->_settings['min_invoice_value'])) :
                    max(array($this->_user['balance'], $this->_settings['min_invoice_value']));
            }
        }

        if ($totalAmount > 0) {
            $paymentDescription = sprintf(
                $translate->_('Please use one of the payment gateways listed below to credit your account with the amount of %s'),
                $this->_view->amount($totalAmount));


            $transaction = array(
                'name'                => array(
                    'string' => 'Credit Account - User ID: #%s',
                    'args'   => array($this->_user['id']),
                ),
                'amount'              => $totalAmount,
                'currency'            => $this->_settings['currency'],
                'user_id'             => $this->_user['id'],
                'transaction_details' => serialize(array(
                    'class' => '\\Ppb\\Service\\Fees\\AccountBalance',
                    'data'  => array(
                        'user_id' => $this->_user['id'],
                        'amount'  => $totalAmount,
                    ),
                )),
            );

            $transactionId = $this->_transactions->save($transaction);

            $gatewayForms = $this->_prepareForms($transactionId);
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => $this->_('Payment - Credit Account'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function StoreSubscription()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (!empty($this->_user['id'])) ?
            $this->_user['id'] : $this->getRequest()->getParam('id');

        $usersService = new Service\Users();

        $user = $usersService->findBy('id', (int)$id);

        $feesService = new Service\Fees\StoreSubscription($user);

        $totalAmount = $feesService->getTotalAmount();

        if ($totalAmount > 0) {
            $storeSubscription = $feesService->getSubscription();

            $paymentDescription = sprintf(
                $translate->_('Please use one of the payment gateways listed below to pay the store subscription fee of %s'),
                $this->_view->amount($totalAmount));


            $transaction = array(
                'name'                => array(
                    'string' => 'Store Subscription Fee - %s Store - User ID: #%s',
                    'args'   => array($storeSubscription['name'], $id),
                ),
                'amount'              => $totalAmount,
                'tax_rate'            => $feesService->getTaxType()->getData('amount'),
                'currency'            => $this->_settings['currency'],
                'user_id'             => $id,
                'transaction_details' => serialize(array(
                    'class' => '\\Ppb\\Service\\Fees\\StoreSubscription',
                    'data'  => array(
                        'user_id'         => $id,
                        'subscription_id' => $storeSubscription['id'],
                    ),
                )),
            );

            $transactionId = $this->_transactions->save($transaction);

            $gatewayForms = $this->_prepareForms($transactionId);
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf(
                    $translate->_("The store for user ID #%s - '%s', has been activated. There were no fees to be paid."),
                    $user['id'], $user['username']),
                'class' => 'alert-success',
            ));

            $user->updateStoreSubscription(1, false, false);

            $gatewayForms = array();
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the user does not exist or there is no store subscription fee to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => $this->_('Payment - Store Subscription'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    public function UserVerification()
    {
        $paymentDescription = null;
        $gatewayForms = null;

        $translate = $this->getTranslate();

        $id = (!empty($this->_user['id'])) ?
            $this->_user['id'] : $this->getRequest()->getParam('id');

        $usersService = new Service\Users();

        $user = $usersService->findBy('id', (int)$id);

        $feesService = new Service\Fees\UserVerification();

        $totalAmount = $feesService->getTotalAmount();

        if ($totalAmount > 0) {
            $paymentDescription = sprintf(
                $translate->_('Please use one of the payment gateways listed below to pay the user verification fee of %s'),
                $this->_view->amount($totalAmount));


            $transaction = array(
                'name'                => array(
                    'string' => 'User Verification Fee - User ID: #%s',
                    'args'   => array($this->_user['id']),
                ),
                'amount'              => $totalAmount,
                'tax_rate'            => $feesService->getTaxType()->getData('amount'),
                'currency'            => $this->_settings['currency'],
                'user_id'             => $id,
                'transaction_details' => serialize(array(
                    'class' => '\\Ppb\\Service\\Fees\\UserVerification',
                    'data'  => array(
                        'user_id'   => $id,
                        'recurring' => $this->_settings['user_verification_recurring'],
                        'refund'    => $this->_settings['user_verification_refund'],
                    ),
                )),
            );

            $transactionId = $this->_transactions->save($transaction);

            $gatewayForms = $this->_prepareForms($transactionId);
        }
        else {
            $this->_flashMessenger->setMessage(array(
                'msg'   => sprintf(
                    $translate->_("The account for user ID #%s - '%s', has been verified. There were no fees to be paid."),
                    $user['id'], $user['username']),
                'class' => 'alert-success',
            ));

            $user->updateUserVerification(1, false);

            $gatewayForms = array();
        }

        if ($gatewayForms === null) {
            $this->_flashMessenger->setMessage(array(
                'msg'   => $this->_('Error: Could not generate the payment form - the user does not exist or there is no user verification fee to be paid.'),
                'class' => 'alert-danger',
            ));
        }

        return array(
            'headline'           => $this->_('Payment - User Verification'),
            'messages'           => $this->_flashMessenger->getMessages(),
            'paymentDescription' => $paymentDescription,
            'gatewayForms'       => $gatewayForms,
        );
    }

    /**
     *
     * instant payment notification action - called by payment gateways
     *
     * @throws \RuntimeException
     * @return array
     */
    public function Ipn()
    {
        $ipnResult = false;
        $feesService = null;
        $transactionId = null;


        $gateway = $this->_paymentGateways->findBy('name', $this->getRequest()->getParam('gateway'));

        if ($gateway['id']) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

            if (class_exists($className)) {
                /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className();
                $ipnResult = $gatewayModel->processIpn(
                    $this->getRequest());

                $transactionId = $gatewayModel->getTransactionId();

                $transaction = $this->_transactions->findBy('id', $transactionId);

                if ($transactionId && $gatewayModel->checkIpnAmount($transaction['amount'], $transaction['currency'])) {
                    // update transaction row
                    $this->_transactions->save(array(
                        'id'                       => $transactionId,
                        'gateway_id'               => $gateway['id'],
                        'gateway_transaction_code' => $gatewayModel->getGatewayTransactionCode(),
                        'status'                   => $gatewayModel->getGatewayPaymentStatus(),
                        'paid'                     => (int)$ipnResult,
                    ));

                    // run callback process
                    $transactionDetails = \Ppb\Utility::unserialize($transaction['transaction_details']);

                    $className = $transactionDetails['class'];
                    $feesService = new $className();

                    if ($feesService instanceof Service\Fees) {
                        $feesService->callback($ipnResult, $transactionDetails['data']);
                    }
                    else {
                        throw new \RuntimeException(
                            sprintf("IPN error - invalid fees service class called (%s).", $feesService));
                    }
                }
            }
        }

        $redirectAction = ($ipnResult === true) ? 'Completed' : 'Failed';

        $this->_postPaymentRedirect($transactionId, $redirectAction);

        $this->_helper->redirector()->redirect(
            $redirectAction);
    }

    /**
     *
     * payment completed action (generic)
     *
     * @return array
     */
    public function Completed()
    {
        $this->_postPaymentRedirect(
            $this->getRequest()->getParam('transaction_id'), 'Completed');

        $this->_view->setViewFileName(null);

        return array(
            'headline' => $this->_('Payment Completed'),
        );
    }

    /**
     *
     * payment failed action
     * TODO: also display the failure code/message if provided by the payment gateway
     *
     * @return array
     */
    public function Failed()
    {
        $this->_postPaymentRedirect(
            $this->getRequest()->getParam('transaction_id'), 'Failed');

        $this->_view->setViewFileName(null);

        return array(
            'headline' => $this->_('Payment Failed'),
        );
    }

    /**
     *
     * generate payment gateway form objects
     *
     * @param string                 $transactionId transaction id (generated by each action)
     * @param int                    $userId        seller id (when fetching direct payment gateways)
     * @param int                    $gatewayIds    gateway ids (to fetch data for specific gateways)
     * @param \Ppb\Db\Table\Row\User $user          payer user model
     *
     * @return array                array of \App\Form\Payment objects
     */
    protected function _prepareForms($transactionId, $userId = null, $gatewayIds = null, $user = null)
    {
        $formElements = array();

        $gateways = $this->_paymentGateways->getData($userId, $gatewayIds, true);

        foreach ($gateways as $gateway) {
            $className = '\\Ppb\\Model\\PaymentGateway\\' . $gateway['name'];

            if (class_exists($className)) {
                /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                $gatewayModel = new $className($userId);

                if ($gatewayModel->enabled()) {
                    $transaction = $this->_transactions->findBy('id', $transactionId);

                    $transactionName = \Ppb\Utility::unserialize($transaction['name']);

                    if ($user !== null) {
                        $gatewayModel->setUser($user);
                    }

                    $gatewayModel->setTransactionId($transaction['id'])
                        ->setName($transactionName)
                        ->setCurrency($transaction['currency'])
                        ->setAmount($transaction['amount']);

                    $formElements[] = new Form\Payment($gatewayModel);
                }
            }
        }

        return $formElements;
    }

    /**
     *
     * post payment redirect process
     *
     * @param int    $transactionId
     * @param string $action
     */
    protected function _postPaymentRedirect($transactionId, $action = 'Completed')
    {
        $transaction = $this->_transactions->findBy('id', $transactionId);

        if ($transaction instanceof TransactionModel) {
            // run callback process
            $transactionDetails = \Ppb\Utility::unserialize($transaction['transaction_details']);

            $className = $transactionDetails['class'];
            if (class_exists($className)) {
                $feesService = new $className();

                if ($feesService instanceof Service\Fees) {
                    $feesService->setTransactionDetails($transactionDetails);

                    if ($redirect = $feesService->getRedirect()) {
                        if ($action == 'Completed') {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('Your payment has been confirmed. Thank you.'),
                                'class' => 'alert-success',
                            ));
                        }
                        else if ($action == 'Failed') {
                            $this->_flashMessenger->setMessage(array(
                                'msg'   => $this->_('The payment has failed. Sorry for the inconvenience.'),
                                'class' => 'alert-danger',
                            ));
                        }

                        $redirectAction = (isset($redirect['action'])) ? $redirect['action'] : null;
                        $redirectController = (isset($redirect['controller'])) ? $redirect['controller'] : null;
                        $redirectModule = (isset($redirect['module'])) ? $redirect['module'] : null;
                        $redirectParams = (isset($redirect['params'])) ? $redirect['params'] : array();

                        $this->_helper->redirector()->redirect(
                            $redirectAction, $redirectController, $redirectModule, $redirectParams);
                    }
                }
            }
        }
    }

}

