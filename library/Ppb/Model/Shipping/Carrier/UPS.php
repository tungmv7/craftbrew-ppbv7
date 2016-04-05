<?php

/**
 *
 * PHP Pro Bid $Id$ P5wwQhqpC+eq/3WJA2bk/hKYDMnFLkIGzvc5D39O8Yw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.4
 */
/**
 * UPS shipping carrier model class
 */

namespace Ppb\Model\Shipping\Carrier;

use Ppb\Model\Shipping as ShippingModel;

class UPS extends AbstractCarrier
{
    /**
     * shipping carrier name
     */

    const NAME = 'UPS';

    /**
     * shipping carrier specific constants
     */
    const UPS_RATE = 'Regular+Daily+Pickup';
    const CONTAINER_CODE = 'Customer+Counter'; // customer packaging
    const ADDRESS_TYPE = '1';

    /**
     * currency
     */
    const CURRENCY = 'USD';

    /**
     *
     * UPS carrier description
     *
     * @var string
     */
    protected $_description = 'UPS Description';

    /**
     *
     * carrier methods array - defined by each carrier class
     *
     * @var array
     */
    protected $_methods = array(
        self::DOM  => array(
            '1DM' => 'Next Day Air Early AM',
            '1DA' => 'Next Day Air',
            '1DP' => 'Next Day Air Saver',
            '2DM' => '2nd Day Air Early AM',
            '2DA' => '2nd Day Air',
            '3DS' => '3 Day Select',
            'GND' => 'Ground',
        ),
        self::INTL => array(
            'XPR' => 'Worldwide Express',
            'XDM' => 'Worldwide Express Plus',
            'XPD' => 'Worldwide Expedited',
            'WXS' => 'Worldwide Saver',
        ),
    );

    public function __construct()
    {
        parent::__construct(self::NAME, self::CURRENCY, ShippingModel::UOM_LBS);
    }

    /**
     *
     * get UPS setup form elements
     *
     * @return array
     */
    public function getElements()
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
     * @return bool|float|array      returns an array of methods, the price for the specified method or false if the price cannot be calculated
     *                                  or false if the price cannot be calculated
     *                                  if there is an error, the $_error variable will be set
     */
    public function getPrice($methodName = null)
    {
        $result = array();

        $methodsKey = ($this->_sourceCountry == $this->_destCountry) ? self::DOM : self::INTL;

        $methods = $this->_methods[$methodsKey];

        foreach ($methods as $key => $method) {
            $price = $this->_callService($key);
            if ($price > 0) {
                $result[$key] = array(
                    'code'     => $key,
                    'name'     => $method,
                    'price'    => $price,
                    'currency' => self::CURRENCY,
                );
            }
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

        if (count($result) > 0) {
            return $result;
        }

        $this->setError('UPS rate calculator error');

        return false;
    }

    protected function _callService($method)
    {
        $output = null;

//        $weight = round($this->getWeight());

        $url = join('&', array(
            'http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes',
            '10_action=4',
            '13_product=' . urlencode($method),
            '14_origCountry=' . strtoupper($this->getSourceCountry()),
            '15_origPostal=' . $this->getSourceZip(),
            '19_destPostal=' . $this->getDestZip(),
            '22_destCountry=' . strtoupper($this->getDestCountry()),
            '23_weight=' . $this->getWeight(),
            '47_rateChart=' . self::UPS_RATE,
            '48_container=' . self::CONTAINER_CODE,
            '49_residential=' . self::ADDRESS_TYPE
        ));

        $fp = fopen($url, 'r');
        if (is_resource($fp)) {
            while (!feof($fp)) {
                $result = explode("%", fgets($fp, 500));

                $mtd = (!empty($result[1])) ? $result[1] : null;
                if ($mtd == $method) {
                    return doubleval($result[8]);
                }
            }

            fclose($fp);
        }


        return null;
    }

}

