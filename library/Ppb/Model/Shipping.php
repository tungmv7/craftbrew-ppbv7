<?php

/**
 *
 * PHP Pro Bid $Id$ pzlAJaAucPBaOCqGPyW5/LfewA0QKA5ak3NQc8xHyy8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * shipping model
 *
 * the model will require a user object in order to be initialized, and to be able
 * to calculate postage, it will require either a listing owned by the user, or
 * a sale made by the user
 *
 * Important: the calculation will always take the location of the item(s) as the source location,
 * and not the location of the owner
 */

namespace Ppb\Model;

use Cube\Controller\Front,
    Cube\Translate,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Ppb\Db\Table\Row\User as UserModel,
    Ppb\Db\Table\Row\Listing as ListingModel,
    Ppb\Form\Element\FlatRatesLocationGroups,
    Ppb\Service;

class Shipping
{

    /**
     * weight uom
     */
    const UOM_LBS = 'lbs';
    const UOM_KG = 'kg';

    /**
     * min weight value
     */
    const MIN_WEIGHT = 0.1;

    /**
     * pickup options
     */
    const NO_PICKUPS = 'no_pickups';
    const CAN_PICKUP = 'can_pickup';
    const MUST_PICKUP = 'must_pickup';

    /**
     * key and value for drop downs for the pick-up option
     */
    const KEY_PICK_UP = -1;
    const VALUE_PICK_UP = 'Pick-up';

    /**
     * seller postage setup page fields
     */
    const SETUP_FREE_POSTAGE = 'free_postage';
    const SETUP_FREE_POSTAGE_AMOUNT = 'free_postage_amount';
    const SETUP_POSTAGE_TYPE = 'postage_type';
    const SETUP_POSTAGE_FLAT_FIRST = 'postage_flat_first';
    const SETUP_POSTAGE_FLAT_ADDL = 'postage_flat_addl';
    const SETUP_SHIPPING_CARRIERS = 'shipping_carriers';
    const SETUP_WEIGHT_UOM = 'weight_uom';
    const SETUP_SHIPPING_LOCATIONS = 'shipping_locations';
    const SETUP_LOCATION_GROUPS = 'location_groups';

    /**
     * postage calculation types
     */
    const POSTAGE_TYPE_ITEM = 'item';
    const POSTAGE_TYPE_FLAT = 'flat';
    const POSTAGE_TYPE_CARRIERS = 'carriers';

    /**
     * shipping locations options
     */
    const POSTAGE_LOCATION_DOMESTIC = 'domestic';
    const POSTAGE_LOCATION_WORLDWIDE = 'worldwide';
    const POSTAGE_LOCATION_CUSTOM = 'custom';

    /**
     * listing setup shipping related fields
     */
    const FLD_ACCEPT_RETURNS = 'accept_returns';
    const FLD_RETURNS_POLICY = 'returns_policy';
    const FLD_PICKUP_OPTIONS = 'pickup_options';
    const FLD_SHIPPING_DETAILS = 'shipping_details';
    const FLD_POSTAGE = 'postage';
    const FLD_ITEM_WEIGHT = 'item_weight';
    const FLD_INSURANCE = 'insurance';

    /**
     * listings data array keys
     */
    const DATA_LISTING = 'listing';
    const DATA_QUANTITY = 'quantity';

    /**
     * standard shipping method desc.
     */
    const MSG_STANDARD_SHIPPING = 'Standard Shipping';

    /**
     *
     * weight uom list
     *
     * @var array
     */
    public static $weightUom = array(
        self::UOM_LBS => 'Lbs',
        self::UOM_KG  => 'Kg'
    );

    /**
     *
     * pick-up options array
     *
     * @var array
     */
    public static $pickupOptions = array(
        self::NO_PICKUPS  => 'No pick-ups',
        self::CAN_PICKUP  => 'Buyer can pick-up',
        self::MUST_PICKUP => 'Buyer must pick-up',
    );

    /**
     *
     * seller postage setup page fields
     *
     * @var array
     */
    public static $postageSetupFields = array(
        self::SETUP_FREE_POSTAGE        => 'Offer Free Postage',
        self::SETUP_FREE_POSTAGE_AMOUNT => 'If amount exceeds',
        self::SETUP_POSTAGE_TYPE        => 'Postage Calculation Type',
        self::SETUP_POSTAGE_FLAT_FIRST  => 'First Item',
        self::SETUP_POSTAGE_FLAT_ADDL   => 'Additional Items',
        self::SETUP_SHIPPING_CARRIERS   => 'Select Shipping Carriers',
        self::SETUP_WEIGHT_UOM          => 'Weight UOM',
        self::SETUP_SHIPPING_LOCATIONS  => 'Shipping Locations',
        self::SETUP_LOCATION_GROUPS     => 'Location Groups',
    );

    /**
     *
     * listing postage related fields array
     *
     * @var array
     */
    public static $postageFields = array(
        self::FLD_ACCEPT_RETURNS   => 'Accept Returns',
        self::FLD_RETURNS_POLICY   => 'Return Policy Details',
        self::FLD_PICKUP_OPTIONS   => 'Pick-ups',
        self::FLD_POSTAGE          => 'Postage',
        self::FLD_ITEM_WEIGHT      => 'Item Weight',
        self::FLD_INSURANCE        => 'Insurance',
        self::FLD_SHIPPING_DETAILS => 'Shipping Instructions',
    );

    /**
     *
     * seller user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * the postage settings that the seller has set up
     *
     * @var array
     */
    protected $_postageSettings = array();

    /**
     *
     * array of data of listing object - quantity combinations for which the postage is to be calculated
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * locations table service
     *
     * @var \Ppb\Service\Table\Relational\Locations
     */
    protected $_locations;

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * the location id of the destination (from the locations table)
     * TODO: we could use a location object
     *
     * @var int
     */
    protected $_locationId;

    /**
     *
     * the zip/post code of the destination
     *
     * @var string
     */
    protected $_postCode;

    /**
     *
     * the name of the shipping method that will be used to send the items.
     * (valid for shipping carriers too)
     *
     * @var string
     */
    protected $_postageMethod;

    /**
     *
     * this flag is set from the addData() method
     *
     * @var bool
     */
    protected $_canPickUp = false;

    /**
     *
     * the currency of the listings inserted in the model
     *
     * @var string
     */
    protected $_currency;

    /**
     *
     * class constructor
     *
     * @param \Ppb\Db\Table\Row\User $user
     */
    public function __construct(UserModel $user)
    {
        $this->_user = $user;

        $this->setPostageSettings($user['postage_settings']);
    }

    /**
     *
     * get pick-up option description
     *
     * @param string $key pick-up option key
     *
     * @return string|null
     */
    public static function getPickupOptions($key)
    {
        if (isset(self::$pickupOptions[$key])) {
            return self::$pickupOptions[$key];
        }

        return null;
    }

    /**
     *
     * get postage settings
     *
     * @return array
     */
    public function getPostageSettings()
    {
        return $this->_postageSettings;
    }

    /**
     *
     * set postage settings (accepted an array or a serialized string)
     *
     * @param array|string $postageSettings
     *
     * @return \Ppb\Model\Shipping
     */
    public function setPostageSettings($postageSettings)
    {
        if (!is_array($postageSettings)) {
            $postageSettings = \Ppb\Utility::unserialize($postageSettings, array());
        }

        $this->_postageSettings = $postageSettings;

        return $this;
    }

    /**
     *
     * get currencies table service
     *
     * @return \Ppb\Service\Table\Currencies
     */
    public function getCurrencies()
    {
        if (!$this->_currencies instanceof Service\Table\Currencies) {
            $this->setCurrencies(
                new Service\Table\Currencies());
        }

        return $this->_currencies;
    }

    /**
     *
     * set currencies service
     *
     * @param \Ppb\Service\Table\Currencies $currencies
     *
     * @return \Ppb\Model\Elements\Listing
     */
    public function setCurrencies(Service\Table\Currencies $currencies)
    {
        $this->_currencies = $currencies;

        return $this;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * get data array
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * set data array
     *
     * @param array $data
     *
     * @return \Ppb\Model\Shipping
     */
    public function setData(array $data)
    {
        $this->_data = array();
        $this->_canPickUp = false;

        foreach ($data as $row) {
            $this->addData($row[self::DATA_LISTING], $row[self::DATA_QUANTITY]);
        }

        return $this;
    }

    /**
     *
     * add a data row in the array
     * all items that are added must have the same location
     *  ** and currency (for now)
     *  ** and pickup options as well
     *
     * @param \Ppb\Db\Table\Row\Listing $listing
     * @param int                       $quantity
     *
     * @throws \RuntimeException
     * @return \Ppb\Model\Shipping
     */
    public function addData(ListingModel $listing, $quantity = 1)
    {
        foreach ($this->_data as $data) {
            if (
                $data[self::DATA_LISTING]['country'] != $listing['country'] ||
                $data[self::DATA_LISTING]['state'] != $listing['state'] ||
                $data[self::DATA_LISTING]['address'] != $listing['address'] ||
                $data[self::DATA_LISTING]['currency'] != $listing['currency'] ||
                $data[self::DATA_LISTING][self::FLD_PICKUP_OPTIONS] != $listing[self::FLD_PICKUP_OPTIONS]
            ) {
                $translate = $this->getTranslate();

                throw new \RuntimeException($translate->_("All the listings added in the shipping model must have the same location "
                    . "(country, state, address), use the same currency and have the same pick-up options selected."));
            }
        }

        if ($listing[self::FLD_PICKUP_OPTIONS] !== self::NO_PICKUPS) {
            $this->_canPickUp = true;
        }

        $this->_data[] = array(
            self::DATA_LISTING  => $listing,
            self::DATA_QUANTITY => ($quantity > 0) ? (int)$quantity : 1
        );

        $this->_currency = $listing['currency'];

        return $this;
    }

    /**
     *
     * get locations service
     *
     * @return \Ppb\Service\Table\Relational\Locations
     */
    public function getLocations()
    {
        if (!$this->_locations instanceof Service\Table\Relational\Locations) {
            $this->setLocations(
                new Service\Table\Relational\Locations());
        }

        return $this->_locations;
    }

    /**
     *
     * set locations service
     *
     * @param \Ppb\Service\Table\Relational\Locations $locations
     *
     * @return \Ppb\Model\Shipping
     */
    public function setLocations(Service\Table\Relational\Locations $locations)
    {
        $this->_locations = $locations;

        return $this;
    }

    /**
     *
     * get destination location id
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->_locationId;
    }

    /**
     *
     * set destination location id
     *
     * @param int $locationId
     *
     * @return \Ppb\Model\Shipping
     */
    public function setLocationId($locationId)
    {
        $this->_locationId = (int)$locationId;

        return $this;
    }

    /**
     *
     * get destination location post code
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->_postCode;
    }

    /**
     *
     * set destination location post code
     *
     * @param string $postCode
     *
     * @return \Ppb\Model\Shipping
     */
    public function setPostCode($postCode)
    {
        $this->_postCode = (string)$postCode;

        return $this;
    }

    public function getPostageMethod()
    {
        $translate = $this->getTranslate();

        if (null !== $translate) {
            return $translate->_($this->_postageMethod);
        }

        return $this->_postageMethod;
    }

    public function setPostageMethod($postageMethod)
    {
        $this->_postageMethod = (string)$postageMethod;

        return $this;
    }

    /**
     *
     * get weight uom set by the user
     *
     * @return string
     */
    public function getWeightUom()
    {
        if (isset($this->_postageSettings[self::SETUP_WEIGHT_UOM])) {
            $translate = $this->getTranslate();

            $sentence = self::$weightUom[$this->_postageSettings[self::SETUP_WEIGHT_UOM]];
            if (null !== $translate) {
                return $translate->_($sentence);
            }

            return $sentence;
        }

        return null;
    }

    /**
     *
     * get location groups array
     *
     * TODO: review translation
     *
     * @return array|bool    will return false if there are no locations or if the location based calculation is not set to custom
     */
    public function getLocationGroups()
    {
        if (!isset($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS])) {
            return false;
        }

        if ($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS] == self::POSTAGE_LOCATION_CUSTOM) {
            return $this->_postageSettings[self::SETUP_LOCATION_GROUPS][FlatRatesLocationGroups::FIELD_NAME];
        }

        return false;
    }

    /**
     *
     * get the postage calculation type set by the user
     *
     * TODO: review translation
     *
     * @return string
     */
    public function getPostageType()
    {
        return isset($this->_postageSettings[self::SETUP_POSTAGE_TYPE]) ?
            $this->_postageSettings[self::SETUP_POSTAGE_TYPE] : null;
    }

    /**
     *
     * return the postage options available based on a set of input values
     *
     * required values:
     * - one or more listings from the same seller
     * - a destination location (id from the locations table)
     * - a destination zip/post code
     *
     * outputs:
     * - an array of all available postage methods
     * OR
     * - a runtime error if any error has occurred
     *
     * [OBSOLETE]
     * currencies output:
     * - item based : item currency
     * - flat rates : site's default currency
     * - carriers : carrier currency
     *
     * [ACTUAL]
     * currency output will always be in the item's currency
     *
     * @return array
     * @throws \RuntimeException
     */
    public function calculatePostage()
    {
        $result = array();
        $translate = $this->getTranslate();

        if (!$this->_locationId) {
            throw new \RuntimeException($translate->_("No destination location has been set."));
        }
        else if (!$this->_postCode) {
            throw new \RuntimeException($translate->_("No destination zip/post code has been set."));
        }
        else if (empty($this->_data)) {
            throw new \RuntimeException($translate->_("At least one item needs to be set in order to calculate the postage."));
        }

        $shippableLocations = $this->getShippableLocations();

        if (!in_array($this->_locationId, $shippableLocations) && !$this->_canPickUp) {
            throw new \RuntimeException($translate->_("The item(s) cannot be shipped to your selected destination."));
        }

        if ($this->_isFreePostage()) {
            $result[] = array(
                'currency' => $this->_currency,
                'price'    => 0,
                'carrier'  => $translate->_('N/A'),
                'method'   => self::MSG_STANDARD_SHIPPING,
            );
        }
        else {
            switch ($this->getPostageType()) {
                case self::POSTAGE_TYPE_ITEM:
                    $postageMethods = null;
                    $postageData = array();

                    foreach ($this->_data as $row) {
                        $fldPostage = $row[self::DATA_LISTING][self::FLD_POSTAGE];


                        if (isset($fldPostage['locations'])) {
                            if (in_array($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS],
                                array(self::POSTAGE_LOCATION_DOMESTIC, self::POSTAGE_LOCATION_WORLDWIDE))
                            ) {
                                // unset locations field if we have domestic or worldwide postage
                                unset($fldPostage['locations']);
                            }
                            else {
                                // set locations as countries
                                $fldPostage['locations'] = array_filter($fldPostage['locations']);
                                foreach ($fldPostage['locations'] as $key => $loc) {
                                    foreach ($loc as $k => $v) {
                                        $fldPostage['locations'][$key][$k] = $this->_postageSettings[self::SETUP_LOCATION_GROUPS][FlatRatesLocationGroups::FIELD_LOCATIONS][$v];
                                    }

                                    $fldPostage['locations'][$key] = call_user_func_array('array_merge',
                                        $fldPostage['locations'][$key]);
                                }
                            }
                        }

                        // modify the price to be calculated on the quantity requested
                        if (!empty($fldPostage['price'])) {
                            foreach ($fldPostage['price'] as $key => $value) {
                                $fldPostage['price'][$key] = $fldPostage['price'][$key] * $row[self::DATA_QUANTITY];
                            }

                            // flip postage array
                            $postageData[] = $this->_flipArray($fldPostage);
                        }


                        // unset price field to compare data
                        if (isset($fldPostage['price'])) {
                            unset($fldPostage['price']);
                        }

                        // the locations and postage methods must be the same for the postage to be calculated (for multiple items)
                        if ($postageMethods !== null && $postageMethods != $fldPostage) {
                            throw new \RuntimeException($translate->_("All listings included must have the same postage methods."));
                        }

                        $postageMethods = $fldPostage;
                    }

                    $listing = $this->_data[0][self::DATA_LISTING];


                    switch ($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS]) {
                        case self::POSTAGE_LOCATION_DOMESTIC:
                        case self::POSTAGE_LOCATION_WORLDWIDE:

                            if (!empty($postageMethods['method'])) {
                                // TODO: seems to work, but will need some further testing
                                foreach ($postageMethods['method'] as $key => $method) {
                                    if (!empty($method)) {
                                        foreach ($postageData as $data) {
                                            $price = (!empty($result[$key]['price'])) ? $result[$key]['price'] : 0;

                                            $result[$key] = array(
                                                'currency' => $listing['currency'],
                                                'price'    => ($price + $data[$key]['price']),
                                                'method'   => $method,
                                            );
                                        }
                                    }
                                }
                            }

                            break;

                        case self::POSTAGE_LOCATION_CUSTOM:
                            // TODO: seems to work, but will need some further testing
                            foreach ($postageMethods['method'] as $key => $method) {
                                if (!empty($method)) {
                                    foreach ($postageData as $data) {
                                        if (in_array($this->_locationId, (array)$data[$key]['locations'])) {
                                            $price = (!empty($result[$key]['price'])) ? $result[$key]['price'] : 0;

                                            $result[$key] = array(
                                                'currency' => $listing['currency'],
                                                'price'    => ($price + $data[$key]['price']),
                                                'method'   => $method,
                                            );
                                        }
                                    }
                                }
                            }

                            break;
                    }

                    break;


                case self::POSTAGE_TYPE_FLAT:
                    $quantity = 0;
                    foreach ($this->_data as $row) {
                        $quantity += $row[self::DATA_QUANTITY];
                    }

                    $settings = Front::getInstance()->getBootstrap()->getResource('settings');

                    switch ($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS]) {
                        case self::POSTAGE_LOCATION_DOMESTIC:
                        case self::POSTAGE_LOCATION_WORLDWIDE:
                            $price = $this->_postageSettings[self::SETUP_POSTAGE_FLAT_FIRST] +
                                ($this->_postageSettings[self::SETUP_POSTAGE_FLAT_ADDL] * ($quantity - 1));

                            $result[] = array(
                                'currency' => $settings['currency'],
                                'price'    => doubleval($price),
                                'method'   => self::MSG_STANDARD_SHIPPING,
                            );
                            break;

                        case self::POSTAGE_LOCATION_CUSTOM:
                            $locationGroups = $this->_postageSettings[self::SETUP_LOCATION_GROUPS];
                            foreach ($locationGroups[FlatRatesLocationGroups::FIELD_LOCATIONS] as $key => $val) {
                                if (in_array($this->_locationId, array_values((array)$val))) {
                                    $price = $locationGroups[FlatRatesLocationGroups::FIELD_FIRST][$key] +
                                        ($locationGroups[FlatRatesLocationGroups::FIELD_ADDL][$key] * ($quantity - 1));
                                    $result[] = array(
                                        'currency' => $settings['currency'],
                                        'price'    => doubleval($price),
                                        'method'   => $locationGroups[FlatRatesLocationGroups::FIELD_NAME][$key],
                                    );
                                }
                            }
                            break;
                    }
                    break;


                case self::POSTAGE_TYPE_CARRIERS:
                    $weight = $this->_calculateTotalWeight();

                    if (!empty($this->_postageSettings[self::SETUP_SHIPPING_CARRIERS])) {
                        foreach ((array)$this->_postageSettings[self::SETUP_SHIPPING_CARRIERS] as $rowCarrier) {
                            $className = '\\Ppb\\Model\\Shipping\\Carrier\\' . $rowCarrier;
                            if (class_exists($className)) {
                                /** @var \Ppb\Model\Shipping\Carrier\AbstractCarrier $carrier */
                                $carrier = new $className();

                                if (!empty($this->_data[0][self::DATA_LISTING]['country'])) {
                                    $sourceCountry = $this->getLocations()->findBy('id',
                                        $this->_data[0][self::DATA_LISTING]['country']);
                                    $carrier->setSourceCountry($sourceCountry['iso_code']);
                                }
                                $destCountry = $this->getLocations()->findBy('id', $this->getLocationId());

                                $carrier->setSourceZip($this->_data[0][self::DATA_LISTING]['address'])
                                    ->setDestCountry($destCountry['iso_code'])
                                    ->setDestZip($this->_postCode)
                                    ->setWeightUom($this->_postageSettings[self::SETUP_WEIGHT_UOM])
                                    ->setWeight($weight);

                                if (($carrierResult = $carrier->getPrice()) !== false) {
                                    foreach ($carrierResult as $val) {
                                        $result[] = array(
                                            'currency' => $val['currency'],
                                            'price'    => $val['price'],
                                            'method'   => $translate->_($rowCarrier) . ' ' . $val['name'],
                                            'code'     => $val['code'],
                                            'carrier'  => $rowCarrier,
                                            'class'    => $className,
                                        );
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }

        if ($this->_canPickUp === true) {
            $result[self::KEY_PICK_UP] = array(
                'currency' => $this->_currency,
                'price'    => 0,
                'method'   => $translate->_(self::VALUE_PICK_UP),
                'carrier'  => '-',

            );
        }

//        ksort($result);

        if (empty($result)) {
            throw new \RuntimeException($translate->_("No shipping available for the selected destination."));
        }

        // convert currencies
        $result = $this->_convertCurrency($result);

        return $result;
    }

    /**
     *
     * calculate insurance amount for the items in a sale
     *
     * @return float
     */
    public function calculateInsurance()
    {
        $insuranceAmount = 0;
        foreach ($this->_data as $row) {
            $insuranceAmount += $row[self::DATA_LISTING][self::FLD_INSURANCE];
        }

        return $insuranceAmount;
    }

    /**
     *
     * get all locations the item(s) can be posted to
     *
     * @param bool $dropDown
     *
     * @return array
     */
    public function getShippableLocations($dropDown = false)
    {
        $regions = false;

        if ($this->getPostageType() == self::POSTAGE_TYPE_ITEM) {
            foreach ($this->_data as $row) {
                $listingRegions = array();

                $listingPostageRegions = isset($row[self::DATA_LISTING][self::FLD_POSTAGE][FlatRatesLocationGroups::FIELD_LOCATIONS]) ?
                    $row[self::DATA_LISTING][self::FLD_POSTAGE][FlatRatesLocationGroups::FIELD_LOCATIONS] : array();

                $listingPostageRegions = array_filter($listingPostageRegions);

                if (count($listingPostageRegions) > 0) {
                    $listingRegions = array_unique(
                        call_user_func_array('array_merge', $listingPostageRegions));
                }

                $regions = (is_array($regions)) ? array_intersect($regions, $listingRegions) : $listingRegions;
            }
        }

        $locations = $this->_getShippingLocations($regions);

//        if (empty($locations)) {
//            throw new \RuntimeException($translate->_("No shipping available for the selected items."));
//        }

        if ($dropDown === true) {
            $locations = $this->getLocations()->getMultiOptions((array)$locations);
        }

        return $locations;
    }

    /**
     *
     * get an array containing the ids of the shipping locations from the user's postage settings
     * and optionally a set of regions
     *
     * by default users will be set to ship domestically, based on the location of the item(s) and not the one of the seller
     *
     * @param array|bool $regions
     *
     * @return array
     */
    protected function _getShippingLocations($regions = false)
    {
        $locations = array();

        $shippingLocations = (isset($this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS])) ?
            $this->_postageSettings[self::SETUP_SHIPPING_LOCATIONS] : self::POSTAGE_LOCATION_DOMESTIC;

        switch ($shippingLocations) {
            case self::POSTAGE_LOCATION_DOMESTIC:
                $locations = array($this->_data[0][self::DATA_LISTING]['country']);
//                $locations = array($this->_store['country']); // changed to the item's location.
                break;
            case self::POSTAGE_LOCATION_WORLDWIDE:
                $locationsService = new Service\Table\Relational\Locations();
                $locations = array_keys($locationsService->getMultiOptions());
                break;
            case self::POSTAGE_LOCATION_CUSTOM:
                $postageLocations = array_filter($this->_postageSettings[self::SETUP_LOCATION_GROUPS][FlatRatesLocationGroups::FIELD_LOCATIONS]);

                if ($regions !== false) {
                    foreach ($postageLocations as $key => $val) {
                        if (!in_array($key, $regions)) {
                            unset($postageLocations[$key]);
                        }
                    }

                    if (count($postageLocations) > 0) {
                        $locations = $this->_mergeLocations($postageLocations);
                    }
                }
                else {
                    $locations = $this->_mergeLocations($postageLocations);
                }
                break;
        }

        return $locations;
    }

    /**
     *
     * rebuild locations array and remove duplicate locations
     *
     * @param $locations
     *
     * @return array
     */
    protected function _mergeLocations(array $locations)
    {
        if (count($locations) > 0) {
            $locations = array_unique(
                call_user_func_array('array_merge', $locations));
        }

        return $locations;
    }

    protected function _flipArray(array $array)
    {
        $output = array();
        foreach ($array['method'] as $key => $value) {
            if (!empty($value)) {
                if (isset($array['price'])) {
                    $output[$key]['price'] = $array['price'][$key];
                }

                $output[$key]['method'] = $array['method'][$key];

                if (isset($array['locations'])) {
                    $output[$key]['locations'] = (isset($array['locations'][$key])) ? $array['locations'][$key] : null;
                }
            }
        }

        return $output;
    }

    /**
     *
     * accepts an array returned by the calculatePostage() method and converts the amounts and currencies
     * to the currency of the listings in the model
     *
     * @param array $data
     *
     * @throws \RuntimeException
     * @return array
     */
    protected function _convertCurrency($data)
    {
        $translate = $this->getTranslate();

        foreach ($data as $key => $value) {
            if (!array_key_exists('currency', $value)
                && !array_key_exists('price', $value)
            ) {
                throw new \RuntimeException($translate->_("Invalid array input in the _convertCurrency() method."));
            }

            if ($value['currency'] != $this->_currency) {
                $data[$key]['currency'] = $this->_currency;
                $data[$key]['price'] = $this->getCurrencies()->convertAmount($value['price'], $value['currency'],
                    $this->_currency);
            }
        }

        return $data;
    }

    /**
     *
     * this method will calculate the total invoice amount and if the total is over the minimum
     * amount required for the postage to be free, it will return true; otherwise it will return false
     *
     * @return bool
     */
    protected function _isFreePostage()
    {
        if ($this->_postageSettings[self::SETUP_FREE_POSTAGE]) {
            $total = 0;

            foreach ($this->_data as $data) {
                $total += $data[self::DATA_LISTING]['buyout_price'] * $data[self::DATA_QUANTITY];
            }

            $settings = Front::getInstance()->getBootstrap()->getResource('settings');

            $freePostageAmount = $this->getCurrencies()->convertAmount($this->_postageSettings[self::SETUP_FREE_POSTAGE_AMOUNT],
                $settings['currency'], $this->_currency);
            if ($total >= $freePostageAmount) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * calculate the total weight of the items added in the shipping model
     *
     * @return float
     */
    protected function _calculateTotalWeight()
    {
        $weight = 0;
        foreach ($this->_data as $row) {
            $weight += $row[self::DATA_LISTING][self::FLD_ITEM_WEIGHT] * $row[self::DATA_QUANTITY];
        }

        return ($weight > 0) ? $weight : self::MIN_WEIGHT;
    }
}
