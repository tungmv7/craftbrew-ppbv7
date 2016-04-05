<?php

/**
 *
 * PHP Pro Bid $Id$ IyYAR2G9ExAiIFSvVNoPlA0pnmjIgk184FBXyMEZSlg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.2
 */
/**
 * SagePay payment gateway model class
 */

namespace Ppb\Model\PaymentGateway;

use Cube\Controller\Request\AbstractRequest,
    Ppb\Service;

class SagePay extends AbstractPaymentGateway
{
    /**
     * payment gateway name
     */

    const NAME = 'SagePay';

    /**
     * required settings
     */
    const VENDOR = 'Vendor';
    const PASSWORD = 'Password';

    /**
     * form post url
     */
    const POST_URL = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';

    /**
     * form post url (sandbox)
     */
    const SANDBOX_POST_URL = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';

    /**
     * sagepay description
     */
    protected $_description = 'Click to pay through SagePay.';

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
        if (!empty($this->_data[self::VENDOR]) && !empty($this->_data[self::PASSWORD])) {
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
                'form_id'     => 'SagePay',
                'id'          => self::VENDOR,
                'element'     => 'text',
                'label'       => $this->_('SagePay Vendor Name'),
                'description' => $this->_('Enter your SagePay vendor name'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'SagePay',
                'id'          => self::PASSWORD,
                'element'     => 'text',
                'label'       => $this->_('SagePay Password'),
                'description' => $translate->_('Enter your SagePay integration password <br>'
                        . 'SagePay Success & Failure URL: <br>') . $this->getIpnUrl(),
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
                'id'      => 'VPSProtocol',
                'value'   => '3.00',
                'element' => 'hidden',
            ),
            array(
                'id'      => 'TxType',
                'value'   => 'PAYMENT',
                'element' => 'hidden',
            ),
            array(
                'id'      => self::VENDOR,
                'value'   => $this->_data[self::VENDOR],
                'element' => 'hidden',
            ),
            array(
                'id'      => 'Crypt',
                'value'   => $this->_encryptAndEncode(
                        $this->_getCryptFields()),
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
        $data = array();

        $result = explode('&', $this->_decodeAndDecrypt($request->getParam('crypt')));

        foreach ($result as $row) {
            list($key, $value) = explode('=', $row);
            $data[$key] = $value;
        }

        $this->setTransactionId($data['VendorTxCode'])
            ->setAmount($data['Amount'])
            ->setCurrency($data['Currency'])
            ->setGatewayPaymentStatus($data['Status'])
            ->setGatewayTransactionCode($data['VPSTxId']);

        if ($data['Status'] == 'OK') {
            $response = true;
        }

        return $response;
    }


    /**
     *
     * generate crypt form variable needed by the sagepay form
     *
     * @return string
     */
    private function _getCryptFields()
    {
        $transactionsService = new Service\Transactions();

        /** @var \Ppb\Db\Table\Row\User $user */
        $user = $transactionsService->findBy('id', $this->getTransactionId())
            ->findParentRow('\Ppb\Db\Table\Users');

        $user->setAddress();

        $locationsService = new Service\Table\Relational\Locations();

        $country = $locationsService->findBy('id', (int)$user->getData('country'));

        $state = null;

        if (strcasecmp($country['iso_code'], 'us') === 0) {
            $state = $user['state'];
            if (is_numeric($state)) {
                $locations = new Service\Table\Relational\Locations();
                $state = strtoupper(
                    $locations->findBy('id', (int)$state)->getData('iso_code'));
            }
        }

        $data = array(
            'VendorTxCode'       => $this->getTransactionId(),
            'Amount'             => $this->_getAmount(),
            'Currency'           => $this->getCurrency(),
            'Description'        => $this->_shortenString($this->getName(), 100),
            'SuccessURL'         => $this->getSuccessUrl(),
            'FailureURL'         => $this->getFailureUrl(),
            'BillingSurname'     => $this->_shortenString($user['last_name'], 20),
            'BillingFirstnames'  => $this->_shortenString($user['first_name'], 20),
            'BillingAddress1'    => $this->_shortenString($user['address'], 100),
            'BillingCity'        => $this->_shortenString($user['city'], 40),
            'BillingPostCode'    => $this->_shortenString($user['zip_code'], 10),
            'BillingCountry'     => $country['iso_code'],
            'BillingState'       => $state,
            'DeliverySurname'    => $this->_shortenString($user['last_name'], 20),
            'DeliveryFirstnames' => $this->_shortenString($user['first_name'], 20),
            'DeliveryAddress1'   => $this->_shortenString($user['address'], 100),
            'DeliveryCity'       => $this->_shortenString($user['city'], 40),
            'DeliveryPostCode'   => $this->_shortenString($user['zip_code'], 10),
            'DeliveryCountry'    => $country['iso_code'],
            'DeliveryState'      => $state,
        );

        $crypt = array();
        foreach ($data as $key => $value) {
            $crypt[] = $key . '=' . $value;
        }

        return implode('&', $crypt);
    }

    /**
     *
     * amount format required by sagepay, with commas to separate thousands
     *
     * @return string
     */
    private function _getAmount()
    {
        return number_format(
            $this->getAmount(), 2, '.', ',');
    }

    /**
     *
     * encrypt the crypt field
     *
     * @param $string
     *
     * @return string
     */
    private function _encryptAndEncode($string)
    {
        //** AES encryption, CBC blocking with PKCS5 padding then HEX encoding - DEFAULT **
        //** use initialization vector (IV) set from the account password
        $strIV = $this->_data[self::PASSWORD];

        //** add PKCS5 padding to the text to be encrypted
        $string = $this->_addPKCS5Padding($string);

        //** perform encryption with PHP's MCRYPT module
        $strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_data[self::PASSWORD], $string, MCRYPT_MODE_CBC, $strIV);

        //** perform hex encoding and return
        return "@" . bin2hex($strCrypt);
    }

    /**
     *
     * decode then decrypt based on header of the encrypted field
     *
     * @param $string
     *
     * @return string
     */
    private function _decodeAndDecrypt($string)
    {
        //** HEX decoding then AES decryption, CBC blocking with PKCS5 padding - DEFAULT **
        //** use initialization vector (IV) set from $strEncryptionPassword
        $strIV = $this->_data[self::PASSWORD];

        //** remove the first char which is @ to flag this is AES encrypted
        $string = substr($string, 1);

        //** HEX decoding
        $string = pack('H*', $string);

        //** perform decryption with PHP's MCRYPT module
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_data[self::PASSWORD], $string, MCRYPT_MODE_CBC, $strIV);
    }

    /**
     *
     * PHP's mcrypt does not have built in PKCS5 Padding, so we use this
     *
     * @param $input
     *
     * @return string
     */
    private function _addPKCS5Padding($input)
    {
        $blockSize = 16;
        $padding = "";

        // Pad input to an even block size boundary
        $padLength = $blockSize - (strlen($input) % $blockSize);
        for ($i = 1; $i <= $padLength; $i++) {
            $padding .= chr($padLength);
        }

        return $input . $padding;
    }


}

