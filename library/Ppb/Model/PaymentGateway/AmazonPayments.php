<?php

/**
 *
 * PHP Pro Bid $Id$ /UiPQnsFkgbA3Yoqg6vuMmAhkGE0t/WkPA02hbTW2kw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * amazon payments gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Cube\Controller\Front,
    Cube\Config\Xml,
    Ppb\Service\Table\Currencies as CurrenciesService;

class AmazonPayments extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'AmazonPayments';

    /**
     * required settings
     */
    const MERCHANT_ID = 'item_merchant_id_1';
    const ACCESS_KEY = 'aws_access_key_id';
    const SECRET_KEY = 'aws_secret_key_id';
    const REGION = 'region';

    /**
     * regions
     */
    const REGION_US = 'us';
    const REGION_UK = 'uk';
    const REGION_DE = 'de';


    /**
     * sha1 algorithm
     */
    const HMAC_SHA1_ALGORITHM = 'sha1';

    /**
     * form post url
     */
    const POST_URL = '';

    /**
     *
     * regions selector
     *
     * @var array
     */
    protected $_regions = array(
        self::REGION_US => 'United States',
        self::REGION_UK => 'United Kingdom',
        self::REGION_DE => 'Germany',
    );

    /**
     *
     * accepted currencies for each region
     *
     * @var array
     */
    protected $_currencies = array(
        self::REGION_US => 'USD',
        self::REGION_UK => 'GBP',
        self::REGION_DE => 'EUR',
    );

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view;

    /**
     * amazon payments description
     */
    protected $_description = 'Click to pay using Amazon Payments Checkout.';

    /**
     *
     * class constructor
     *
     * @param int $userId
     */
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
        if (!empty($this->_data[self::MERCHANT_ID]) && !empty($this->_data[self::ACCESS_KEY]) && !empty($this->_data[self::SECRET_KEY])) {
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
                'form_id'     => 'AmazonPayments',
                'id'          => self::MERCHANT_ID,
                'element'     => 'text',
                'label'       => $this->_('Merchant ID'),
                'description' => $this->_('Found in - Amazon Payments > Settings > Account Info > Checkout Pipeline Settings'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'AmazonPayments',
                'id'          => self::ACCESS_KEY,
                'element'     => 'text',
                'label'       => $this->_('AWS Access Key ID'),
                'description' => $this->_('Enter your access key id (public)'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'AmazonPayments',
                'id'          => self::SECRET_KEY,
                'element'     => 'text',
                'label'       => $this->_('AWS Secret Access Key'),
                'description' => $translate->_('Enter your secret access key (private)<br>Amazon Payments IPN URL:<br>') . $this->getIpnUrl(),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => 'AmazonPayments',
                'id'           => self::REGION,
                'element'      => 'select',
                'label'        => $this->_('Region'),
                'description'  => $this->_('Select the region that applies to your Amazon Payments account.'),
                'multiOptions' => $this->_regions,
                'attributes'   => array(
                    'class' => 'form-control input-medium',
                ),
            )
        );
    }

    /**
     *
     * set transaction amount
     * convert all amounts to USD before going to the payment page
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

        $acceptedCurrency = $this->_currencies[$this->_getActiveRegion()];

        if ($currency != $acceptedCurrency) {
            $currenciesService = new CurrenciesService();
            $amount = $currenciesService->convertAmount($amount, $currency, $acceptedCurrency);
            $this->setCurrency($acceptedCurrency);
        }

        parent::setAmount($amount);

        return $this;
    }

    /**
     *
     * @return array
     */
    public function formElements()
    {
        $translate = $this->getTranslate();

        $view = Front::getInstance()->getBootstrap()->getResource('view');

        /** @var \Cube\View\Helper\Script $scriptHelper */
        $scriptHelper = $view->getHelper('script');

        switch ($this->_getActiveRegion()) {
            case self::REGION_US:
                $scriptHelper->addBodyCode("<script type='text/javascript' src='https://static-na.payments-amazon.com/cba/js/us/PaymentWidgets.js'></script>");
                break;
            case self::REGION_UK:
                $scriptHelper->addBodyCode('<script language=javascript src="https://static-eu.payments-amazon.com/cba/js/gb/PaymentWidgets.js"></script>');
                // UK sandbox.
                // $scriptHelper->addBodyCode('<script language=javascript src="https://static-eu.payments-amazon.com/cba/js/gb/sandbox/PaymentWidgets.js"></script>');
                break;
            case self::REGION_DE:
                $scriptHelper->addBodyCode('<script language=javascript src="https://static-eu.payments-amazon.com/cba/js/de/PaymentWidgets.js"></script>');
                break;
        }


        return array(
            array(
                'id'       => self::MERCHANT_ID,
                'value'    => $this->_data[self::MERCHANT_ID],
                'element'  => 'hidden',
                'bodyCode' => "<script type=\"text/javascript\">
                        $('#" . self::NAME . "').find('.payment-btn').html('').attr('id', 'amazonPaymentsButton');

                        new CBA.Widgets.StandardCheckoutWidget({
                            merchantId:'" . $this->_data[self::MERCHANT_ID] . "',
                            orderInput: {
                                format: 'HTML',
                                value: '" . self::NAME . "'
                            },
                            buttonSettings: {
                                size:'large',
                                color:'orange',
                                background:'white'
                            }
                            }).render('amazonPaymentsButton');
                    </script>",
            ),
            array(
                'id'      => 'item_sku_1',
                'value'   => $this->getTransactionId(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'item_title_1',
                'value'   => $this->_shortenString($this->getName(), 80),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'item_price_1',
                'value'   => $this->getAmount(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'item_quantity_1',
                'value'   => '1',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'currency_code',
                'value'   => $this->getCurrency(),
                'element' => 'hidden',
            ),
            array(
                'id'      => 'merchant_signature', // cart hash for signed carts
                'value'   => $this->_encryptAndEncode(
                    $this->_createHash()),
                'element' => 'hidden',
            ),
            array(
                'id'      => self::ACCESS_KEY,
                'value'   => $this->_data[self::ACCESS_KEY],
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
            $UUID = $request->getParam('UUID');
            $Signature = $request->getParam('Signature'); // if empty, we have an unsigned cart, but our carts must all be signed

            $Timestamp = $request->getParam('Timestamp');
            $NotificationData = stripslashes($request->getParam('NotificationData')); // need to parse it as xml or something.
            $NotificationType = $request->getParam('NotificationType');

            $xmlObject = new Xml();
            $xmlObject->setData(
                urldecode($NotificationData));

            $orderDetails = $xmlObject->getData('ProcessedOrder');

            $this->setGatewayPaymentStatus($NotificationType)
                ->setGatewayTransactionCode($UUID);

            $generatedSignature = $this->_encryptAndEncode($UUID . $Timestamp);

            if (strcmp($generatedSignature, $Signature) === 0 || empty($Signature)) {
                $this->setTransactionId($orderDetails['ProcessedOrderItems']['ProcessedOrderItem'][0]['SKU'])
                    ->setAmount($orderDetails['ProcessedOrderItems']['ProcessedOrderItem'][0]['Price']['Amount'])
                    ->setCurrency($orderDetails['ProcessedOrderItems']['ProcessedOrderItem'][0]['Price']['CurrencyCode']);


                if ($NotificationType == 'OrderReadyToShipNotification') {
                    $response = true;
                }
            }
            else {
                $this->setGatewayPaymentStatus('Invalid Signature');
            }
        }

        return $response;
    }

    /**
     *
     * generates the required hash -- it gets generated exactly list on amazon's signed cart demo
     *
     * @return string
     */
    private function _createHash()
    {
        $data = array(
            self::MERCHANT_ID => $this->_data[self::MERCHANT_ID],
            'item_sku_1'      => $this->getTransactionId(),
            'item_title_1'    => $this->getName(),
            'item_price_1'    => $this->getAmount(),
            'item_quantity_1' => '1',
            'currency_code'   => $this->getCurrency(),
            self::ACCESS_KEY  => $this->_data[self::ACCESS_KEY],
        );

        ksort($data);

        $crypt = null;
        foreach ($data as $key => $value) {
            $crypt .= $key . '=' . rawurlencode($value) . '&';
        }

        return $crypt;
    }

    /**
     *
     * encrypt and encode string for signed carts
     *
     * @param string $string
     *
     * @return string
     */
    private function _encryptAndEncode($string)
    {
        return base64_encode(hash_hmac(self::HMAC_SHA1_ALGORITHM, $string, $this->_data[self::SECRET_KEY], true));
    }

    /**
     *
     * get active region
     *
     * @return string
     */
    protected function _getActiveRegion()
    {
        if (isset($this->_data[self::REGION])) {
            $region = $this->_data[self::REGION];

            if (in_array($region, array_keys($this->_regions))) {
                return $region;
            }
        }

        return self::REGION_US;
    }

}

