<?php

/**
 *
 * PHP Pro Bid $Id$ KmPinqhjuBFBdhHlIT4nBXZyeEX9SXumEOpK/rmhXU4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * Australia Post shipping carrier model class
 */

namespace Ppb\Model\Shipping\Carrier;

use Ppb\Model\Shipping as ShippingModel;

class AustraliaPost extends AbstractCarrier
{
    /**
     * shipping carrier name
     */

    const NAME = 'AustraliaPost';

    /**
     * form elements (carrier module settings)
     */
    const API_KEY = 'api_key';
    const PRODUCTION_MODE = 'production_mode';

    /**
     * shipping carrier specific constants
     */
    const PROD_SERVER = 'https://auspost.com.au/api/';
    const TEST_SERVER = 'https://test.npe.auspost.com.au/api/';
    const TEST_API_KEY = '28744ed5982391881611cca6cf5c2409';

    /**
     * api urls
     */
    const URL_DOM_PARCEL_SERVICE_LIST = 'postage/parcel/domestic/service.json';
    const URL_INTL_PARCEL_SERVICE_LIST = 'postage/parcel/international/service.json';
    const URL_DOM_PARCEL_CALCULATE = 'postage/parcel/domestic/calculate.json';
    const URL_INTL_PARCEL_CALCULATE = 'postage/parcel/international/calculate.json';

    /**
     * currency
     */
    const CURRENCY = 'AUD';

    /**
     *
     * shipping carrier description
     *
     * @var string
     */
    protected $_description = 'Australia Post Description';
    protected static $_carrierWeightUom = 'kg';

    /**
     *
     * api key
     *
     * @var string
     */
    private $_apiKey = self::TEST_API_KEY;

    /**
     *
     * server url
     *
     * @var string
     */
    private $_server = self::TEST_SERVER;

    /**
     *
     * package dimensions (in cm)
     *
     * @var array
     */
    protected $_dimensions = array(
        self::W => 10,
        self::H => 10,
        self::L => 10,
    );

    public function __construct()
    {
        parent::__construct(self::NAME, self::CURRENCY, ShippingModel::UOM_KG);

        if ($this->_data[self::PRODUCTION_MODE]) {
            $this->_apiKey = $this->_data[self::API_KEY];
            $this->_server = self::PROD_SERVER;
        }
    }

    /**
     *
     * get USPS setup form elements
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'form_id'     => self::NAME,
                'id'          => self::API_KEY,
                'element'     => 'text',
                'label'       => $this->_('API Key'),
                'description' => $this->_('Enter your Australia Post API Key.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'      => self::NAME,
                'id'           => self::PRODUCTION_MODE,
                'element'      => 'checkbox',
                'label'        => $this->_('Production Mode'),
                'multiOptions' => array(
                    1 => null,
                ),
                'description'  => $this->_('Check this checkbox to switch the module to production mode.'),
            ),
        );
    }

    public function getMethods()
    {
        return array();
    }

    /**
     *
     * get price method - gets the price of a selected method,
     * or outputs a list of available methods for the selected input data
     *
     * @param string $methodName        (optional) method name
     * @return bool|float|array      returns an array of methods, the price for the specified method
     *                                  or false if the price cannot be calculated
     *                                  if there is an error, the $_error variable will be set
     */
    public function getPrice($methodName = null)
    {
        $result = array();

        $sourceCountry = strtoupper($this->getSourceCountry());
        $destCountry = strtoupper($this->getDestCountry());

        if ($sourceCountry != 'AU') {
            $this->setError('The service can only be used when shipping from Australia.');

            return false; // will only send from Australia
        }

        if ($destCountry == 'AU') {
            if ($methodName !== null) {
                $url = self::URL_DOM_PARCEL_CALCULATE . '?' .
                       join('&', array(
                           'from_postcode=' . $this->getSourceZip(),
                           'to_postcode=' . $this->getDestZip(),
                           'length=' . $this->_dimensions[self::L],
                           'width=' . $this->_dimensions[self::W],
                           'height=' . $this->_dimensions[self::H],
                           'weight=' . $this->getWeight(),
                           'service_code=' . $methodName,
                       ));
            }
            else {
                $url = self::URL_DOM_PARCEL_SERVICE_LIST . '?' .
                       join('&', array(
                           'from_postcode=' . $this->getSourceZip(),
                           'to_postcode=' . $this->getDestZip(),
                           'length=' . $this->_dimensions[self::L],
                           'width=' . $this->_dimensions[self::W],
                           'height=' . $this->_dimensions[self::H],
                           'weight=' . $this->getWeight(),
                       ));
            }
        }
        else {
            if ($methodName !== null) {
                $url = self::URL_INTL_PARCEL_CALCULATE . '?' .
                       join('&', array(
                           'country_code=' . $destCountry,
                           'weight=' . $this->getWeight(),
                           'service_code=' . $methodName,
                       ));
            }
            else {
                $url = self::URL_INTL_PARCEL_SERVICE_LIST . '?' .
                       join('&', array(
                           'country_code=' . $destCountry,
                           'weight=' . $this->getWeight(),
                       ));
            }
        }

        $request = $this->_makeRequest($url);

        if (isset($request['error']['errorMessage'])) {
            $this->setError($request['error']['errorMessage']);

            return false;
        }

        if (isset($request['services']['service'])) {
            foreach ($request['services']['service'] as $row) {
                $result[] = array(
                    'code'     => $row['code'],
                    'name'     => $row['name'],
                    'price'    => $row['price'],
                    'currency' => self::CURRENCY,
                );
            }
        }
        else if (isset($request['postage_result']['total_cost'])) {
            $result = doubleval($request['postage_result']['total_cost']);
        }
        else {
            $this->setError('No rate(s) available for the requested input.');
//            $request = false;
        }

        return $result;
    }

    private function _makeRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_server . $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Auth-Key: ' . $this->_apiKey,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec($ch);

        curl_close($ch);

        return json_decode($contents, true);
    }

}

