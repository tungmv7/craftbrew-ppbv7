<?php

/**
 *
 * PHP Pro Bid $Id$ cSDqdrY/Sodv21Butsa2hIFHxA4Uqug1q1FhzHEbXNE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * worldpay payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest;

class WorldPay extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'WorldPay';

    /**
     * required settings
     */
    const INSTID = 'instId';

    /**
     * form post url
     */
    const POST_URL = 'https://select.worldpay.com/wcc/purchase';

    /**
     * worldpay description
     */
    protected $_description = 'Click to pay though WorldPay.';

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
        if (!empty($this->_data[self::INSTID])) {
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
                'form_id'     => 'WorldPay',
                'id'          => self::INSTID,
                'element'     => 'text',
                'label'       => $this->_('WorldPay ID'),
                'description' => $translate->_('Enter your merchant installation Id <br>'
                        . 'WorldPay IPN URL: <br>') . $this->getIpnUrl(),
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
                'id'      => self::INSTID,
                'value'   => $this->_data[self::INSTID],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'cartId',
                'value'   => $this->getTransactionId(),
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
                'id'      => 'desc',
                'value'   => $this->getName(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'MC_callback',
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
            $paymentStatus = $request->getParam('transStatus');

            $this->setTransactionId($request->getParam('cartId'))
                ->setAmount($request->getParam('amount'))
                ->setCurrency($request->getParam('currency'))
                ->setGatewayPaymentStatus($request->getParam('rawAuthMessage'))
                ->setGatewayTransactionCode($request->getParam('transId'));

            if ($paymentStatus == 'Y') {
                $response = true;
            }
        }

        return $response;
    }

}

