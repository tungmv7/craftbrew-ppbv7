<?php

/**
 *
 * PHP Pro Bid $Id$ GHB1RKeGVWcWygWgXqeXO1HJ7AnMQjOi15ONzbJ8So4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * paypal sandbox payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class PayPalSandbox extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */
    const NAME = 'PayPalSandbox';

    /**
     * required settings
     */
    const BUSINESS = 'sandbox_business';

    /**
     * form post url (sandbox)
     */
    const POST_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    /**
     * paypal description
     */
    protected $_description = 'Click to pay through PayPal (sandbox mode).';

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
        if (!empty($this->_data[self::BUSINESS])) {
            return true;
        }

        return false;
    }

    /**
     *
     * get paypal setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'     => 'PayPalSandbox',
                'id'          => self::BUSINESS,
                'element'     => 'text',
                'label'       => $this->_('PayPal Sandbox Email Address'),
                'description' => $translate->_('Enter your PayPal Sandbox registered email address<br>'
                        . 'PayPal IPN URL: <br>') . $this->getIpnUrl(),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    public function formElements()
    {
        return array(
            array(
                'id'      => 'cmd',
                'value'   => '_xclick',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'bn',
                'value'   => 'wa_dw_2.0.4',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'business',
                'value'   => $this->_data[self::BUSINESS],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'receiver_email',
                'value'   => $this->_data[self::BUSINESS],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'item_name',
                'value'   => $this->getName(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'amount',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'currency_code',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'custom',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'notify_url',
                'value'   => $this->getIpnUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'return',
                'value'   => $this->getSuccessUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'cancel_return',
                'value'   => $this->getFailureUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'undefined_quantity',
                'value'   => '0',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'no_shipping',
                'value'   => '1',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'no_note',
                'value'   => '1',
                'element' => 'hidden',
            ),
        );
    }

    /**
     *
     * get gateway post url
     *
     * @return string
     */
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

            $fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);


            if (!$fp) {
                $this->setGatewayPaymentStatus($errstr . ' (' . $errno . ')');
            }
            else {
                $content = 'cmd=_notify-validate';

                foreach ($request->getParams() as $key => $value) {
                    $content .= '&' . $key . '=' . urlencode(stripslashes($value));
                }

                $header = "POST /cgi-bin/webscr HTTP/1.1\r\n"
                    . "Content-Type: application/x-www-form-urlencoded\r\n"
                    . "Host: www.paypal.com\r\n"
                    . "Connection: close\r\n"
                    . "Content-Length: " . strlen($content) . "\r\n\r\n";

                fputs($fp, $header . $content);

                $paymentStatus = $_POST['payment_status'];
                $this->setTransactionId($_POST['custom'])
                    ->setAmount($_POST['mc_gross'])
                    ->setCurrency($_POST['mc_currency'])
                    ->setGatewayPaymentStatus($paymentStatus)
                    ->setGatewayTransactionCode($_POST['txn_id']);

                while (!feof($fp)) {
                    $result = trim(fgets($fp, 1024));

                    if (strcmp($result, "VERIFIED") == 0) {
                        if ($paymentStatus == "Completed") {
                            $response = true;
                        }
                    }
                    else if (strcmp($result, "INVALID") == 0) {
                        $this->setGatewayPaymentStatus($result);
                    }
                }

                fclose($fp);
            }
        }

        return $response;
    }

}

