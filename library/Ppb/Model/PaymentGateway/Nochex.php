<?php

/**
 *
 * PHP Pro Bid $Id$ KqUryrYOVrT+VDBrJzMRVJFa9FDtFSJCd8zA2pobUUE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * nochex gateway model class
 * accepts only payments in GBP
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Ppb\Service\Table\Currencies as CurrenciesService;

class Nochex extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'Nochex';

    /**
     * required settings
     */
    const MERCHANT_ID = 'merchant_id';

    /**
     * accepted currency
     */
    const ACCEPTED_CURRENCY = 'GBP';

    /**
     * form post url
     */
    const POST_URL = 'https://secure.nochex.com/';

    /**
     * nochex description
     */
    protected $_description = 'Click to pay through Nochex.';

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
                'form_id'     => 'Nochex',
                'id'          => self::MERCHANT_ID,
                'element'     => 'text',
                'label'       => $this->_('Nochex Email Address'),
                'description' => $translate->_('Enter your registered email address (merchant id) <br>'
                        . 'Nochex IPN URL: <br>') . $this->getIpnUrl(),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
        );
    }

    /**
     *
     * set transaction amount
     * convert all amounts to a standard format (eg: 12000.00)
     *
     * @param string $amount
     *
     * @throws \RuntimeException
     * @return $this
     */
    public function setAmount($amount)
    {
        $currency = $this->getCurrency();

        if (empty($currency)) {
            $translate = $this->getTranslate();

            throw new \RuntimeException($translate->_("Please set the currency before setting the amount."));
        }

        if ($currency != self::ACCEPTED_CURRENCY) {
            $currenciesService = new CurrenciesService();
            $amount = $currenciesService->convertAmount($amount, $currency, self::ACCEPTED_CURRENCY);
            $this->setCurrency(self::ACCEPTED_CURRENCY);
        }

        parent::setAmount($amount);

        return $this;
    }

    /**
     * @return array
     */
    public function formElements()
    {
        return array(
            array(
                'id'      => self::MERCHANT_ID,
                'value'   => $this->_data[self::MERCHANT_ID],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'order_id',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'amount',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'description',
                'value'   => $this->getName(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'callback_url',
                'value'   => $this->getIpnUrl(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'success_url',
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

            $params = array();
            foreach ($request->getParams() as $key => $value) {
                $params[] = $key . '=' . urlencode($value);
            }
            $content = implode('&', $params);

            $header = "POST /nochex.dll/apc/apc HTTP/1.0\r\n" .
                "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Content-Length: " . strlen($content) . "\r\n\r\n";

            $fp = fsockopen("www.nochex.com", 80, $errno, $errstr, 10);

            fputs($fp, $header . $content);

            $paymentStatus = $_POST['payment_status'];
            $this->setTransactionId($_POST['custom'])
                ->setAmount($_POST['amount'])
                ->setCurrency('GBP')
                ->setGatewayPaymentStatus($paymentStatus)
                ->setGatewayTransactionCode($_POST['transaction_id']);

            while (!feof($fp)) {
                $result = trim(fgets($fp, 1024));

                if (strstr($result, 'AUTHORISED') && trim($paymentStatus) == 'live') {
                    $response = true;
                }
                else {
                    $this->setGatewayPaymentStatus($result);
                }
            }

            fclose($fp);
        }

        return $response;
    }
}

