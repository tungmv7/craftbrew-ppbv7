<?php

/**
 *
 * PHP Pro Bid $Id$ ux3rJ/rLuZSTZ8Hhl5M3Ma3N5vItoLU6AiDu5dCqd9M=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * skrill gateway model class
 *
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class Skrill extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'Skrill';

    /**
     * required settings
     */
    const PAY_TO_EMAIL = 'pay_to_email';
    const SECRET_WORD = 'secret_word';
    const TRANSACTION_FIELD = 'TransactionID';

    /**
     * form post url
     */
    const POST_URL = 'https://www.moneybookers.com/app/payment.pl';

    /**
     * form post url (sandbox)
     */
    const SANDBOX_POST_URL = 'http://www.moneybookers.com/app/test_payment.pl';

    /**
     * skrill description
     */
    protected $_description = 'Click to pay through Skrill.';

    protected $_ipnCodes = array(
        2  => 'Processed',
        0  => 'Pending',
        -1 => 'Cancelled',
        -2 => 'Failed',
        -3 => 'Chargeback'
    );

    public function __construct($userId = null)
    {
        parent::__construct(self::NAME, $userId);
    }

    /**
     *
     * check if the gateway is enabled
     *
     * @return bool
     */
    public function enabled()
    {
        if (!empty($this->_data[self::PAY_TO_EMAIL])) {
            return true;
        }

        return false;
    }

    /**
     *
     * get setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'     => 'Skrill',
                'id'          => self::PAY_TO_EMAIL,
                'element'     => 'text',
                'label'       => $this->_('Skrill Email Address'),
                'description' => $this->_('Enter your registered email address'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'Skrill',
                'id'          => self::SECRET_WORD,
                'element'     => 'text',
                'label'       => $this->_('Skrill Secret Word'),
                'description' => $translate->_('(recommended) The secret word submitted in the "Merchant Tools" section of the Merchant\'s online Skrill account. <br>'
                        . 'Skrill IPN URL: <br>') . $this->getIpnUrl(),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function formElements()
    {
        return array(
            array(
                'id'      => self::PAY_TO_EMAIL,
                'value'   => $this->_data[self::PAY_TO_EMAIL],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'language',
                'value'   => 'EN',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'amount',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'currency',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'merchant_fields',
                'value'   => self::TRANSACTION_FIELD,
                'element' => 'hidden',
            ),
            array(
                'id'      => self::TRANSACTION_FIELD,
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'detail1_description',
                'value'   => $this->getName(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'status_url',
                'value'   => $this->getIpnUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'return_url',
                'value'   => $this->getSuccessUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'cancel_url',
                'value'   => $this->getFailureUrl(),
                'element' => 'hidden',
            ),
        );
    }

    public function getPostUrl()
    {
        return self::POST_URL;
    }

    /**
     *
     * process ipn
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool      return true if ipn returns a valid transaction
     */
    public function processIpn(AbstractRequest $request)
    {
        $errno = null;
        $errstr = null;

        $response = false;

        if ($request->isPost()) {
            $paymentStatus = $request->getParam('status');

            $this->setTransactionId($request->getParam(self::TRANSACTION_FIELD))
                ->setAmount($request->getParam('amount'))
                ->setCurrency($request->getParam('currency'))
                ->setGatewayPaymentStatus($this->_ipnCodes[$paymentStatus])
                ->setGatewayTransactionCode($request->getParam('mb_transaction_id'));

            if (!$this->_validateMd5Sig($request)) {
                $this->setGatewayPaymentStatus('Invalid MD5 Signature');
            }
            else if ($paymentStatus == 2) {
                $response = true;
            }
        }

        return $response;
    }

    /**
     *
     * validate md5 signature field
     *
     * @param AbstractRequest $request
     *
     * @return bool
     */
    private function _validateMd5Sig(AbstractRequest $request)
    {
        if (empty($this->_data[self::SECRET_WORD])) {
            return true;
        }

        $string = $request->getParam('merchant_id')
            . $request->getParam('transaction_id')
            . strtoupper(md5($this->_data[self::SECRET_WORD]))
            . $request->getParam('mb_amount')
            . $request->getParam('mb_currency')
            . $request->getParam('status');

        if (strcmp(strtoupper(md5($string)), $request->getParam('md5sig')) === 0) {
            return true;
        }

        return false;
    }
}

