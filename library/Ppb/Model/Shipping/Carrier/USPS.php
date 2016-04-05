<?php

/**
 *
 * PHP Pro Bid $Id$ msjBcQldHkXjlNhsIO5ktMk5MBqub4keOmIsQucGObc=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * USPS shipping carrier model class
 */

namespace Ppb\Model\Shipping\Carrier;

use Cube\Config\Xml,
    Ppb\Service\Table\Relational\Locations as LocationsService,
    Ppb\Model\Shipping as ShippingModel;

class USPS extends AbstractCarrier
{
    /**
     * shipping carrier name
     */

    const NAME = 'USPS';

    /**
     * form elements (carrier module settings)
     */
    const USERNAME = 'username';
    const PASSWORD = 'password';

    /**
     * shipping carrier specific constants
     */
    const SERVER = 'http://Production.ShippingAPIs.com/ShippingAPI.dll';
    const CONTAINER = "Variable";
    const SIZE = "Regular";
    const MACHINABLE = "True";
    const WEIGHT_UOM = 'lbs';
    const DIMENSIONS_UOM = 'in';

    /**
     * currency
     */
    const CURRENCY = 'USD';

    /**
     *
     * shipping carrier description
     *
     * @var string
     */
    protected $_description = 'USPS Description';

    /**
     *
     * service type
     *
     * @var string
     */
    private $_service = 'ALL';

    public function __construct()
    {
        parent::__construct(self::NAME, self::CURRENCY, ShippingModel::UOM_LBS);
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
                'id'          => self::USERNAME,
                'element'     => 'text',
                'label'       => $this->_('Username'),
                'description' => $this->_('Enter your USPS account username.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => self::NAME,
                'id'          => self::PASSWORD,
                'element'     => 'text',
                'label'       => $this->_('Password'),
                'description' => $this->_('Enter your USPS account password (optional).'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            )
        );
    }

    public function getService()
    {
        return $this->_service;
    }

    public function setService($service)
    {

        if ($service == "USPSBPM") {
            $service = "BPM";
        }
        else if ($service == "USPSFCM") {
            $service = "First Class";
        }
        else if ($service == "USPSMM") {
            $service = "Media";
        }
        else {
            $service = 'ALL';
        }

        $this->_service = $service;

        return $this;
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
     * @param string $methodName (optional) method name
     *
     * @return bool|float|array      returns an array of methods, the price for the specified method
     *                                  or false if the price cannot be calculated
     *                                  if there is an error, the $_error variable will be set
     */
    public function getPrice($methodName = null)
    {
        $sourceCountry = strtoupper($this->getSourceCountry());
        $destCountry = strtoupper($this->getDestCountry());

        if ($sourceCountry != 'US') {
            $this->setError('The service can only be used when shipping from the US.');

            return false; // will only send from the USA
        }

        $weight = $this->getWeight();
        $pounds = strtok($weight, '.');
        $ounces = intval(intval(substr($weight, strrpos($weight, '.') + 1)) * 16 / 10);

        $dimensions = $this->getDimensions();

        if ($destCountry == 'US') {
            // may need to urlencode xml portion
            $str = self::SERVER . "?API=RateV4&XML="
                . "<RateV4Request%20USERID=\"" . urlencode($this->_data[self::USERNAME]) . "\"%20PASSWORD=\"" . urlencode($this->_data[self::PASSWORD]) . "\">"
                . "<Revision/>"
                . "<Package%20ID=\"0\">"
                . "<Service>" . urlencode($this->getService()) . "</Service>"
                . "<ZipOrigination>" . urlencode($this->getSourceZip()) . "</ZipOrigination>"
                . "<ZipDestination>" . urlencode($this->getDestZip()) . "</ZipDestination>"
                . "<Pounds>" . urlencode($pounds) . "</Pounds><Ounces>" . urlencode($ounces) . "</Ounces>"
                . "<Container>" . self::CONTAINER . "</Container>"
                . "<Size>" . self::SIZE . "</Size>"
                . "<Width>" . urlencode($dimensions[self::W]) . "</Width>"
                . "<Length>" . urlencode($dimensions[self::L]) . "</Length>"
                . "<Height>" . urlencode($dimensions[self::H]) . "</Height>"
                . "<Machinable>" . self::MACHINABLE . "</Machinable>"
                . "</Package></RateV4Request>";
        }
        else {
            $locationsService = new LocationsService();
            $country = $locationsService->findBy('iso_code', $destCountry);

            $destCountryName = (!empty($country['name'])) ? $country['name'] : $destCountry;

            $str = self::SERVER . "?API=IntlRate&XML="
                . "<IntlRateRequest%20USERID=\"" . urlencode($this->_data[self::USERNAME]) . "\"%20PASSWORD=\"" . urlencode($this->_data[self::PASSWORD]) . "\">"
                . "<Package%20ID=\"0\">"
                . "<Pounds>" . urlencode($pounds) . "</Pounds><Ounces>" . urlencode($ounces) . "</Ounces>"
                . "<MailType>Package</MailType>"
                . "<Country>" . urlencode($destCountryName) . "</Country>"
                . "</Package></IntlRateRequest>";
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $str);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $res = curl_exec($ch);


        curl_close($ch);


        $xmlObject = new Xml();
        $xmlObject->setData($res);

        $fatalError = $xmlObject->getData();
        $error = $xmlObject->getData('Error');
        $package = $xmlObject->getData('Package');

        if (isset($fatalError['Description'])) {
            $this->setError($fatalError['Description']);

            return false;
        }
        else if (isset($error['Description'])) {
            $this->setError($error['Description']);

            return false;
        }
        else if (isset($package['Error']['Description'])) {
            $this->setError($package['Error']['Description']);

            return false;
        }

        $result = array();
        if ($destCountry == 'US') {
            $methods = $xmlObject->getData('Postage');
            $nameKey = 'MailService';
            $rateKey = 'Rate';
        }
        else {
            $methods = $xmlObject->getData('Service');
            $nameKey = 'SvcDescription';
            $rateKey = 'Postage';
        }

        foreach ($methods as $method) {
            $result[] = array(
                'code'     => $method[$nameKey],
                'name'     => $this->_formatServiceName($method[$nameKey]),
                'price'    => $method[$rateKey],
                'currency' => self::CURRENCY,
            );
        }

        if ($methodName !== null) {
            if (!array_key_exists($methodName, $result)) {
                $translate = $this->getTranslate();

                $this->setError(
                    sprintf($translate->_('The "%s" shipping method does not exist.'), $methodName));

                return false;
            }
            else {
                return doubleval($result[$methodName]);
            }
        }

        return $result;
    }

    /**
     *
     * format the name of the service to remove any html tags
     *
     * @param string $name
     *
     * @return string
     */
    protected function _formatServiceName($name)
    {
        return strip_tags(
            str_ireplace(array('&lt;', '&gt;'), array('<', '>'), $name));
    }
}

