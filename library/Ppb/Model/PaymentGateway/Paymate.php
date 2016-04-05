<?php

/**
 *
 * PHP Pro Bid $Id$ g55DsDxcoHinWn+ncjmtl0kJ8+1t2mCEMyehbziQ9Ic=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * paymate payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class Paymate extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'Paymate';

    /**
     * required settings
     */
    const MERCHANT_ID = 'mid';

    /**
     * form post url
     */
    const POST_URL = 'https://www.paymate.com/PayMate/ExpressPayment';

    /**
     * paymate description
     */
    protected $_description = 'Click to pay using Paymate Express Payments.';

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
        if (!empty($this->_data[self::MERCHANT_ID])) {
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
                'form_id'     => 'Paymate',
                'id'          => self::MERCHANT_ID,
                'element'     => 'text',
                'label'       => $this->_('Paymate Username'),
                'description' => $translate->_('Enter the username you use to log into the Paymate website <br>'
                        . 'Paymate IPN URL: <br>') . $this->getIpnUrl(),
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
                'id'      => self::MERCHANT_ID,
                'value'   => $this->_data[self::MERCHANT_ID],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'amt',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'amt_editable',
                'value'   => 'N',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'currency',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'return',
                'value'   => $this->getIpnUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'ref',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'popup',
                'value'   => 'false',
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
     * @return bool
     */
    public function processIpn(AbstractRequest $request)
    {
        $response = false;

        if ($request->isPost()) {
            $paymentStatus = $request->getParam('responseCode');

            $this->setTransactionId($request->getParam('ref'))
                ->setAmount($request->getParam('paymentAmount'))
                ->setCurrency($request->getParam('currency'))
                ->setGatewayPaymentStatus($request->getParam('responseCode'))
                ->setGatewayTransactionCode($request->getParam('transactionID'));

            if ($paymentStatus == 'PA') {
                $response = true;
            }
        }

        return $response;
    }

}

