<?php

/**
 *
 * PHP Pro Bid $Id$ TM21QDT9g41wy/4TT4ViUGyFhgwgxFsmVkBKS3tOQ5E=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * 2checkout payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class TCheckout extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'TCheckout';

    /**
     * required settings
     */
    const SID = 'sid';

    /**
     * form post url
     */
    const POST_URL = 'https://www.2checkout.com/checkout/purchase';

    /**
     * 2checkout description
     */
    protected $_description = 'Click to pay through 2Checkout.';

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
        if (!empty($this->_data[self::SID])) {
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
                'form_id'     => 'TCheckout',
                'id'          => self::SID,
                'element'     => 'text',
                'label'       => $this->_('2Checkout Account Number'),
                'description' => $translate->_('Enter your account number <br>'
                        . '2Checkout IPN URL: <br>') . $this->getIpnUrl(),
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
                'id'      => self::SID,
                'value'   => $this->_data[self::SID],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'mode',
                'value'   => '2CO',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'li_0_type',
                'value'   => 'product',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'li_0_name',
                'value'   => $this->_shortenString($this->getName(), 128),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'li_0_price',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'transaction_id',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),

            array(
                'id'      => 'currency_code',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'x_receipt_link_url',
                'value'   => $this->getIpnUrl(),
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
            $paymentStatus = $request->getParam('credit_card_processed');

            $this->setTransactionId($request->getParam('transaction_id'))
                ->setAmount($request->getParam('total'))
                ->setCurrency($request->getParam('currency_code'))
                ->setGatewayPaymentStatus($request->getParam('credit_card_processed'))
                ->setGatewayTransactionCode($request->getParam('order_number'));

            if ($paymentStatus == 'Y') {
                $response = true;
            }
        }

        return $response;
    }

}

