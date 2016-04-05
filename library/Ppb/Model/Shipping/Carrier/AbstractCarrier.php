<?php

/**
 *
 * PHP Pro Bid $Id$ xlRDR5r2ZMDCzWuPw8uu5hJ+qZbbDwRijHvLSwH/M0E=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * shipping carrier model abstract class
 *
 * IMPORTANT: all methods that extend this class must have the name identical with the
 * 'name' field in the shipping_carriers table.
 *
 * TODO: add currency field as well
 */

namespace Ppb\Model\Shipping\Carrier;

use Cube\Db\Table\Row\AbstractRow,
        Ppb\Model\Shipping as ShippingModel,
        Ppb\Service;

abstract class AbstractCarrier extends AbstractRow
{
    /**
     * methods array constants
     */

    const DOM = 'domestic';
    const INTL = 'international';

    /**
     * dimensions constants;
     */
    const W = 'width';
    const L = 'length';
    const H = 'height';

    /**
     * weight conversion rate - lbs -> kg
     */
    const LBS_TO_KG = 0.4536;

    /**
     *
     * shipping carrier description
     * (to be used in the admin area - functionality description)
     *
     * @var string
     */
    protected $_description;

    /**
     *
     * carrier methods array - defined by each carrier class
     *
     * @var array
     */
    protected $_methods = array(
        self::DOM  => array(),
        self::INTL => array(),
    );

    /**
     *
     * package weight
     * (lbs or kg, depending on shipping carrier)
     *
     * @var int
     */
    protected $_weight;

    /**
     *
     * package weight uom (accepted values: kg, lbs)
     *
     * @var string
     */
    protected $_weightUom;

    /**
     *
     * carrier weight uom
     *
     * @var string
     */
    protected static $_carrierWeightUom = null;

    /**
     *
     * error message resulted from a getPrice operation
     *
     * @var string
     */
    protected $_error = null;

    /**
     *
     * package dimensions
     *
     * @var array
     */
    protected $_dimensions = array(
        self::W => null,
        self::H => null,
        self::L => null,
    );

    /**
     *
     * carrier currency
     *
     * @var string
     */
    protected $_currency;

    /**
     *
     * source zip code
     *
     * @var string
     */
    protected $_sourceZip;

    /**
     *
     * source country
     *
     * @var string
     */
    protected $_sourceCountry;

    /**
     *
     * destination zip code
     *
     * @var string
     */
    protected $_destZip;

    /**
     *
     * destination country
     *
     * @var string
     */
    protected $_destCountry;

    /**
     *
     * class constructor
     *
     * @param string $carrierName carrier name
     * @param string $currency
     * @param string $carrierWeightUom
     * @throws \RuntimeException
     */
    public function __construct($carrierName, $currency, $carrierWeightUom)
    {
        $carriersService = new Service\Table\ShippingCarriers();
        $carrier = $carriersService->findBy('name', $carrierName);

        if (!$carrier['id']) {
            $translate = $this->getTranslate();

            throw new \RuntimeException(
                sprintf($translate->_("The shipping carrier you are trying to use, '%s', does not exist."), $carrierName));
        }

        $data = array(
            'table' => $carriersService->getTable(),
            'data'  => $carriersService->getData($carrier['id']),
        );

        parent::__construct($data);

        $this->setCurrency($currency);

        self::$_carrierWeightUom = $carrierWeightUom;
    }

    /**
     *
     * get carrier description string
     *
     * @return string
     */
    public function getDescription()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_description);
        }

        return $this->_description;
    }

    /**
     *
     * set carrier description string
     *
     * @param string $description
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setDescription($description)
    {
        $this->_description = (string)$description;

        return $this;
    }

    /**
     *
     * get package weight
     *
     * @return float|int
     */
    public function getWeight()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_weight);
        }

        return $this->_weight;
    }

    /**
     *
     * set package weight
     *
     * @param float $weight
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     * @throws \InvalidArgumentException
     */
    public function setWeight($weight)
    {
        if (empty($this->_weightUom)) {
            throw new \InvalidArgumentException("Please set the weight UOM before setting the weight value.");
        }

        if ($this->_weightUom != self::$_carrierWeightUom) {
            $weight = ($this->_weightUom == ShippingModel::UOM_KG) ?
                    ($weight * self::LBS_TO_KG) : ($weight / self::LBS_TO_KG);
        }

        $this->_weight = round($weight, 1);

        return $this;
    }

    /**
     *
     * get weight uom
     *
     * @return string
     */
    public function getWeightUom()
    {
        return $this->_weightUom;
    }

    /**
     *
     * set weight uom
     *
     * @param string $weightUom
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     * @throws \InvalidArgumentException
     */
    public function setWeightUom($weightUom)
    {
        $weightUom = strtolower($weightUom);

        if (!in_array($weightUom, array(ShippingModel::UOM_KG, ShippingModel::UOM_LBS))) {
            throw new \InvalidArgumentException("Invalid weight UOM submitted.");
        }

        $this->_weightUom = $weightUom;

        return $this;
    }

    /**
     *
     * get error message
     *
     * @return string
     */
    public function getError()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_error);
        }

        return $this->_error;
    }

    /**
     *
     * set error message
     *
     * @param string $error
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setError($error)
    {
        $this->_error = (string)$error;

        return $this;
    }

    /**
     *
     * get dimensions array
     *
     * @return array
     */
    public function getDimensions()
    {
        return $this->_dimensions;
    }

    /**
     *
     * set dimensions array
     *
     * @param array $dimensions
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     * @throws \InvalidArgumentException
     */
    public function setDimensions(array $dimensions)
    {
        if (!array_key_exists(self::W, $dimensions) ||
            !array_key_exists(self::H, $dimensions) ||
            !array_key_exists(self::L, $dimensions)
        ) {
            $translate = $this->getTranslate();
            throw new \InvalidArgumentException($translate->_("The weight, length and height keys need to be set."));
        }

        $this->_dimensions = $dimensions;

        return $this;
    }

    /**
     *
     * get carrier currency iso code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     *
     * set carrier currency iso code
     *
     * @param string $currency
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setCurrency($currency)
    {
        $this->_currency = (string)$currency;

        return $this;
    }

    /**
     *
     * get source zip code
     *
     * @return string
     */
    public function getSourceZip()
    {
        return $this->_sourceZip;
    }

    /**
     *
     * set source zip code
     *
     * @param string $sourceZip
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setSourceZip($sourceZip)
    {
        $this->_sourceZip = (string)$sourceZip;

        return $this;
    }

    /**
     *
     * get source country
     *
     * @return string
     */
    public function getSourceCountry()
    {
        return $this->_sourceCountry;
    }

    /**
     *
     * set source country
     *
     * @param string $sourceCountry
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setSourceCountry($sourceCountry)
    {
        $this->_sourceCountry = (string)$sourceCountry;

        return $this;
    }

    /**
     *
     * get destination zip code
     *
     * @return string
     */
    public function getDestZip()
    {
        return $this->_destZip;
    }

    /**
     *
     * set destination zip code
     *
     * @param string $destZip
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setDestZip($destZip)
    {
        $this->_destZip = (string)$destZip;

        return $this;
    }

    /**
     *
     * get destination country
     *
     * @return string
     */
    public function getDestCountry()
    {
        return $this->_destCountry;
    }

    /**
     *
     * set destination country
     *
     * @param string $destCountry
     * @return \Ppb\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setDestCountry($destCountry)
    {
        $this->_destCountry = (string)$destCountry;

        return $this;
    }

    /**
     *
     * get form elements, used to create the form needed to add the shipping carrier settings
     *
     * @return array
     */
    public function getElements()
    {
        return array();
    }

    /**
     *
     * dummy function used as a placeholder for translatable sentences
     *
     * @param $string
     *
     * @return string
     */
    protected function _($string)
    {
        return $string;
    }

    abstract public function getPrice($methodName = null);
}

