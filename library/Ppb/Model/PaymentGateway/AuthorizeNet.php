<?php

/**
 *
 * PHP Pro Bid $Id$ CY9SnGAqRdwQxLwQY6kMQfDdiQfI5AQ6jPB/LXrZkQU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * authorize.net payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class AuthorizeNet extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'AuthorizeNet';

    /**
     * required settings
     */
    const MERCHANT_ID = 'x_login';
    const TRANSACTION_KEY = 'authnet_transaction_key';
    const MD5_HASH = 'x_MD5_Hash';
    const SANDBOX_MODE = 'sandbox_mode';

    /**
     * form post url
     */
    const POST_URL = 'https://secure.authorize.net/gateway/transact.dll';

    /**
     * form post url (sandbox)
     */
    const SANDBOX_POST_URL = 'https://test.authorize.net/gateway/transact.dll';

    /**
     * 2checkout description
     */
    protected $_description = 'Click to pay through Authorize.net.';

    protected $_ipnCodes = array(
        1 => 'Approved',
        2 => 'Declined',
        3 => 'Error',
        4 => 'Held for Review',
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
        if (!empty($this->_data[self::MERCHANT_ID]) && !empty($this->_data[self::TRANSACTION_KEY])) {
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
                'form_id'     => 'AuthorizeNet',
                'id'          => self::MERCHANT_ID,
                'element'     => 'text',
                'label'       => $this->_('Authorize.net Merchant ID'),
                'description' => $this->_('Enter your merchant ID'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'AuthorizeNet',
                'id'          => self::TRANSACTION_KEY,
                'element'     => 'text',
                'label'       => $this->_('Authorize.net Transaction Key'),
                'description' => $this->_('Enter your assigned transaction key'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'AuthorizeNet',
                'id'          => self::MD5_HASH,
                'element'     => 'text',
                'label'       => $this->_('Authorize.net MD5 Hash'),
                'description' => $this->_('(recommended) enter your set md5 hash value if you wish for the ipn requests to be encrypted <br>'
                        . 'Authorize.net Relay Response URL: <br>') . $this->getIpnUrl(),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'AuthorizeNet',
                'id'           => self::SANDBOX_MODE,
                'element'      => 'checkbox',
                'label'        => $this->_('Sandbox Mode'),
                'description'  => $this->_('Check the above checkbox to activate the sandbox mode.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
        );
    }

    public function formElements()
    {
        $timestamp = time();


        return array(
            array(
                'id'      => 'x_version',
                'value'   => '3.1',
                'element' => 'hidden',
            ),
            array(
                'id'      => self::MERCHANT_ID,
                'value'   => $this->_data[self::MERCHANT_ID],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_type',
                'value'   => 'AUTH_CAPTURE',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_method',
                'value'   => 'CC',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_amount',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_show_form',
                'value'   => 'PAYMENT_FORM',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_relay_response',
                'value'   => 'TRUE',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_test_request',
                'value'   => ($this->_isSandboxMode()) ? 'TRUE' : 'false',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_description',
                'value'   => $this->_shortenString($this->getName(), 255),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_invoice_num',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_currency_code',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_fp_hash',
                'value'   => $this->_createHash($timestamp),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_fp_sequence',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_fp_timestamp',
                'value'   => $timestamp,
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_cancel_url',
                'value'   => $this->getFailureUrl(),
                'element' => 'hidden',
            ),
        );
    }

    /**
     *
     * get the form post url (live or sandbox)
     *
     * @return string
     */
    public function getPostUrl()
    {
        return ($this->_isSandboxMode()) ?
            self::SANDBOX_POST_URL : self::POST_URL;
    }

    /**
     *
     * process ipn
     *
     * @param \Cube\Controller\Request\AbstractRequest $request
     *
     * @return bool
     */
    public function processIpn(AbstractRequest $request)
    {
        $response = false;

        if ($request->isPost()) {
            $paymentStatus = $request->getParam('x_response_code');

            $this->setTransactionId($request->getParam('x_invoice_num'))
                ->setAmount($request->getParam('x_amount'))
                ->setCurrency($request->getParam('x_currency_code'))
                ->setGatewayPaymentStatus($this->_ipnCodes[$paymentStatus])
                ->setGatewayTransactionCode($request->getParam('x_trans_id'));

            if (!$this->_validateMd5Sig($request)) {
                $this->setGatewayPaymentStatus('Invalid MD5 Hash');
            }
            else if ($paymentStatus == 1) {
                $response = true;
            }
        }

        return $response;
    }


    /**
     *
     * method that checks if the amount and currency submitted through an ipn is the
     * coincides with the row in the transactions table
     *
     * @param float  $amount
     * @param string $currency
     *
     * @return bool
     */
    public function checkIpnAmount($amount, $currency)
    {
        if ($this->_amount == $amount && in_array($currency, array('USD', 'CAD', 'GBP'))) {
            return true;
        }

        return false;
    }

    /**
     *
     * generates the required x_fp_hash variable, based on merchant id, transaction id (x_fp_sequence), timestamp and payment amount
     * and hashed using the merchant's transaction key
     *
     * @param int $timestamp
     *
     * @return string
     */
    private function _createHash($timestamp)
    {
        return $this->_hmac($this->_data[self::TRANSACTION_KEY],
            $this->_data[self::MERCHANT_ID] . '^' . $this->getTransactionId() . '^' . $timestamp . '^' . $this->getAmount() . '^' . $this->getCurrency());
    }

    /**
     *
     * RFC 2104 HMAC implementation for php.
     * Creates an md5 HMAC.
     * Eliminates the need to install mhash to compute a HMAC
     * Hacked by Lance Rushing
     *
     * @param string $key
     * @param string $data
     *
     * @return string
     */
    private function _hmac($key, $data)
    {

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $iPad = str_pad('', $b, chr(0x36));
        $oPad = str_pad('', $b, chr(0x5c));
        $kIPad = $key ^ $iPad;
        $kOPad = $key ^ $oPad;

        return md5($kOPad . pack("H*", md5($kIPad . $data)));
    }


    /**
     *
     * validate ipn md5 hash
     *
     * @param AbstractRequest $request
     *
     * @return bool
     */
    private function _validateMd5Sig(AbstractRequest $request)
    {
        if (empty($this->_data[self::MD5_HASH])) {
            return true;
        }

        $string = $this->_data[self::MD5_HASH]
            . $this->_data[self::MERCHANT_ID]
            . $request->getParam('x_trans_id')
            . $request->getParam('x_amount');

        if (strcasecmp(md5($string), $request->getParam(self::MD5_HASH)) === 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if sandbox mode is enabled
     *
     * @return bool
     */
    protected function _isSandboxMode()
    {
        $sandbox = (isset($this->_data[self::SANDBOX_MODE])) ? $this->_data[self::SANDBOX_MODE] : false;

        return (bool)$sandbox;
    }

}

