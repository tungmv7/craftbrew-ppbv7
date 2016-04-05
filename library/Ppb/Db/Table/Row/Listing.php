<?php

/**
 *
 * PHP Pro Bid $Id$ mrOuBmJIkhgHvHxC6EKyhCQ3VFizhK9QSLFxUgUHdzQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * listings table row object model
 */

namespace Ppb\Db\Table\Row;

use Ppb\Service,
    Cube\Controller\Front,
    Cube\Db\Table,
    Cube\Db\Expr,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Db\Table\Rowset;

class Listing extends AbstractRow
{
    /**
     * listing statuses
     */

    const OPEN = 'open';
    const CLOSED = 'closed';
    const SCHEDULED = 'scheduled';

    /**
     * custom fields tables "type" column
     */
    const CUSTOM_FIELDS_TYPE = 'item';

    /**
     * unlimited quantity flag
     */
    const UNLIMITED_QUANTITY = -1;

    /**
     *
     * relist type
     * 'same' -> same listing
     * 'new' -> new listing
     */
    const RELIST_SAME = 'same';
    const RELIST_NEW = 'new';

    /**
     * last count operation flags
     */
    const COUNT_OP_NONE = 'none';
    const COUNT_OP_ADD = 'add';
    const COUNT_OP_SUBTRACT = 'subtract';

    /**
     * operations
     */
    const ADD = 'add';
    const SUBTRACT = 'subtract';

    /**
     * max chars for the short description method
     */
    const SHORT_DESC_MAX_CHARS = 255;
    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array('postage_settings', 'offline_payment', 'direct_payment');
    /**
     *
     * array of available relist methods
     *
     * @var array
     */
    protected $_relistMethods = array(
        self::RELIST_SAME,
        self::RELIST_NEW,
    );

    /**
     *
     * relist method to be used
     *
     * @var string
     */
    protected $_relistMethod;

    /**
     *
     * bids rowset
     * contains all bids placed on this listing
     *
     * @var \Ppb\Db\Table\Rowset\Bids
     */
    protected $_bids = null;

    /**
     *
     * offers rowset
     * contains all offers posted on this listing
     *
     * @var \Ppb\Db\Table\Rowset\Offers
     */
    protected $_offers = null;

    /**
     *
     * sales rowset
     * contains all sales transactions
     *
     * @var \Ppb\Db\Table\Rowset\Sales
     */
    protected $_sales = null;

    /**
     *
     * bid increments table service
     *
     * @var \Ppb\Service\Table\BidIncrements
     */
    protected $_bidIncrements;

    /**
     *
     * custom fields and custom fields data tables service
     *
     * @var \Ppb\Service\CustomFields
     */
    protected $_customFields;


    /**
     *
     * sales listings table service
     *
     * @var \Ppb\Service\Table\SalesListings
     */
    protected $_salesListings;
    /**
     *
     * custom fields data table service
     *
     * @var \Ppb\Service\CustomFieldsData
     */
    protected $_customFieldsData;

    /**
     *
     * categories table service
     *
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;

    /**
     *
     * listing media rowset
     *
     * @var \Cube\Db\Table\Rowset\AbstractRowset
     */
    protected $_listingsMedia;

    /**
     *
     * the id of a sale that was created as a result of a purchase
     * (buy out or assign winner)
     *
     * @var int
     */
    protected $_saleId;

    /**
     *
     * tax type object that applies to the listing
     * or null if no tax will apply
     *
     * @var \Ppb\Db\Table\Row\TaxType|null
     */
    protected $_taxType = null;

    /**
     *
     * closed flag
     *
     * @var bool
     */
    protected $_closedFlag = false;


    /**
     *
     * class constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (!isset($data['data']['id'])) {
            $data['data']['id'] = 0;
        }

        parent::__construct($data);
    }

    /**
     *
     * get bids rowset ordered from the highest to the lowest
     *
     * @return \Ppb\Db\Table\Rowset\Bids
     */
    public function getBids()
    {
        if (!$this->_bids instanceof Rowset\Bids) {
            $select = $this->getTable()->select()
                ->order(array('amount DESC', 'id DESC'));

            $this->_bids = $this->findDependentRowset('\Ppb\Db\Table\Bids', null, $select);
        }

        return $this->_bids;
    }

    /**
     *
     * clear bids rowset
     *
     * @return $this
     */
    public function clearBids()
    {
        $this->_bids = null;

        return $this;
    }

    /**
     *
     * get offers rowset
     *
     * @return \Ppb\Db\Table\Rowset\Offers
     */
    public function getOffers()
    {
        if (!$this->_offers instanceof Rowset\Offers) {
            $select = $this->getTable()->select()
                ->order(array('id DESC'));

            $this->_offers = $this->findDependentRowset('\Ppb\Db\Table\Offers', null, $select);
        }

        return $this->_offers;
    }

    /**
     *
     * clear offers rowset
     *
     * @return $this
     */
    public function clearOffers()
    {
        $this->_offers = null;

        return $this;
    }

    /**
     *
     * get sales listings table rowset
     * TODO: change this to sales listings rowset
     *
     * @return \Ppb\Db\Table\Rowset\Sales
     */
    public function getSales()
    {
        if (!$this->_sales instanceof Rowset\Sales) {
            $service = new Service\Sales();

            $select = $service->getTable()->getAdapter()->select()
                ->from(array('l' => 'sales_listings'))
                ->joinLeft(array('s' => 'sales'), 'l.sale_id = s.id', 's.buyer_id')
                ->where('l.listing_id = ?', intval($this->getData('id')))
                ->where('s.pending = ?', 0)
                ->order(array('l.id DESC'));

            $this->_sales = $service->fetchAll($select);
        }

        return $this->_sales;
    }

    /**
     *
     * clear sales rowset
     *
     * @return $this
     */
    public function clearSales()
    {
        $this->_sales = null;

        return $this;
    }

    /**
     *
     * get bid increments table service
     *
     * @return \Ppb\Service\Table\BidIncrements
     */
    public function getBidIncrements()
    {
        if (!$this->_bidIncrements instanceof Service\Table\BidIncrements) {
            $this->setBidIncrements(
                new Service\Table\BidIncrements());
        }

        return $this->_bidIncrements;
    }

    /**
     *
     * set bid increments table service
     *
     * @param \Ppb\Service\Table\BidIncrements $bidIncrements
     *
     * @return $this
     */
    public function setBidIncrements(Service\Table\BidIncrements $bidIncrements)
    {
        $this->_bidIncrements = $bidIncrements;

        return $this;
    }

    /**
     *
     * get custom fields table service
     *
     * @return \Ppb\Service\CustomFields
     */
    public function getCustomFieldsService()
    {
        if (!$this->_customFields instanceof Service\CustomFields) {
            $this->setCustomFieldsService(
                new Service\CustomFields());
        }

        return $this->_customFields;
    }

    /**
     *
     * set custom fields table service
     *
     * @param \Ppb\Service\CustomFields $customFields
     *
     * @return $this
     */
    public function setCustomFieldsService(Service\CustomFields $customFields)
    {
        $this->_customFields = $customFields;

        return $this;
    }

    /**
     *
     * set sales listings table service
     *
     * @param \Ppb\Service\Table\SalesListings $salesListings
     *
     * @return $this
     */
    public function setSalesListings($salesListings)
    {
        $this->_salesListings = $salesListings;

        return $this;
    }

    /**
     * @return \Ppb\Service\Table\SalesListings
     */
    public function getSalesListings()
    {
        if (!$this->_salesListings instanceof Service\Table\SalesListings) {
            $this->setSalesListings(
                new Service\Table\SalesListings());
        }

        return $this->_salesListings;
    }


    /**
     *
     * get categories table service
     *
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        if (!$this->_categories instanceof Service\Table\Relational\Categories) {
            $this->setCategories(
                new Service\Table\Relational\Categories());
        }

        return $this->_categories;
    }

    /**
     *
     * set categories table service
     *
     * @param \Ppb\Service\Table\Relational\Categories $categories
     *
     * @return $this
     */
    public function setCategories(Service\Table\Relational\Categories $categories)
    {
        $this->_categories = $categories;

        return $this;
    }

    /**
     *
     * set sale id
     *
     * @param int $saleId
     *
     * @return $this
     */
    public function setSaleId($saleId)
    {
        $this->_saleId = $saleId;

        return $this;
    }

    /**
     *
     * get sale id
     *
     * @return int
     */
    public function getSaleId()
    {
        return $this->_saleId;
    }

    /**
     *
     * get custom fields data service
     *
     * @return \Ppb\Service\CustomFieldsData
     */
    public function getCustomFieldsData()
    {
        if (!$this->_customFieldsData instanceof Service\CustomFieldsData) {
            $this->setCustomFieldsData(
                new Service\CustomFieldsData());
        }

        return $this->_customFieldsData;
    }

    /**
     *
     * set custom fields data service
     *
     * @param \Ppb\Service\CustomFieldsData $customFieldsData
     *
     * @return \Ppb\Service\CustomFieldsData
     */
    public function setCustomFieldsData(Service\CustomFieldsData $customFieldsData)
    {
        $this->_customFieldsData = $customFieldsData;

        return $this;
    }

    /**
     *
     * get closed flag
     *
     * @return bool
     */
    public function getClosedFlag()
    {
        return $this->_closedFlag;
    }

    /**
     *
     * retrieve the listing's selected media types or all if $types = null
     * if the listing has not been created yet, then use the $_data array,
     * otherwise get data from the listings_media table
     *
     * @param string|array|null $types
     *
     * @return array|\Ppb\Db\Table\Rowset\ListingsMedia
     */
    public function getMedia($types = null)
    {
        $media = array();

        if ($types !== null) {
            if (!is_array($types)) {
                $types = array($types);
            }
        }
        else {
            $types = Service\ListingsMedia::getTypes();
        }

        // if we are creating a listing, we will use the $_data array
        if (!$this->getData('id')) {
            foreach ($types as $type) {
                $files = $this->getData($type);
                if ($files !== null) {
                    if (!is_array($files)) {
                        $this->addData($type, \Ppb\Utility::unserialize($files));
                    }

                    foreach ((array)$this->getData($type) as $fileName) {
                        $media[] = array(
                            'id'    => null,
                            'value' => $fileName,
                            'type'  => $type,
                        );
                    }
                }
            }
        }
        else {
            /** @var \Ppb\Db\Table\Rowset\ListingsMedia $media */
            $media = $this->findDependentRowset('\Ppb\Db\Table\ListingsMedia', null,
                $this->getTable()->select()->where('type IN (?)', $types)
                    ->order('order_id ASC'));
        }

        return $media;
    }

    /**
     *
     * retrieve the listing's main image
     *
     * @param bool $absolutePath if true, return the full path of the image
     *
     * @return string|null
     */
    public function getMainImage($absolutePath = false)
    {
        $images = $this->getMedia('image');

        $image = null;

        if (count($images) > 0) {
            $image = $images[0]['value'];

            if ($absolutePath === true) {
                if (!preg_match('#^http(s)?://(.*)+$#i', $image)) {
                    $settings = $this->getSettings();

                    $uploadsPath = $settings['site_path'] . \Ppb\Utility::URI_DELIMITER . \Ppb\Utility::getFolder('uploads');
                    $image = $uploadsPath . \Ppb\Utility::URI_DELIMITER . $image;
                }
            }
        }

        return $image;
    }

    /**
     *
     * get listing status (open/closed/scheduled)
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->getData('start_time') > date('Y-m-d H:i:s', time())) {
            return self::SCHEDULED;
        }
        else if ($this->getData('closed')) {
            return self::CLOSED;
        }
        else {
            return self::OPEN;
        }
    }

    /**
     *
     * generate the meta description of the listing.
     *
     * it will be something like: {title} in {category}
     *
     * @return string
     */
    public function getMetaDescription()
    {
        $breadcrumbs = implode(' > ', $this->getCategories()->getBreadcrumbs(
            $this->getData('category_id')));

        return sprintf('%s in %s', $this->getData('name'), $breadcrumbs);
    }

    /**
     *
     * get relist method
     *
     * @return string
     */
    public function getRelistMethod()
    {
        if (!$this->_relistMethod) {
            $settings = $this->getSettings();

            $this->setRelistMethod(
                (isset($settings['relist_method'])) ? $settings['relist_method'] : null);
        }

        return $this->_relistMethod;
    }

    /**
     *
     * set relist method
     *
     * @param string $relistMethod
     *
     * @return $this
     */
    public function setRelistMethod($relistMethod = null)
    {
        if (!in_array($relistMethod, $this->_relistMethods)) {
            $relistMethod = array_shift(
                array_values($this->_relistMethods));
        }

        $this->_relistMethod = $relistMethod;

        return $this;
    }

    /**
     *
     * get the available quantity for this listing
     * this is calculated based on the quantity field minus and pending sales that have the listing added
     * if quantity = -1 - we have an unlimited quantity of items for the listing
     *
     * @param int|null   $quantity          initial quantity
     * @param array|null $productAttributes the product attributes for which to check stock levels
     *
     * @return int|true true if we have unlimited quantity
     */
    public function getAvailableQuantity($quantity = null, $productAttributes = null)
    {
        $stockLevels = \Ppb\Utility::unserialize($this->getData('stock_levels'));

        $quantityAvailable = 0;
        if (!empty($stockLevels) && $this->getData('listing_type') == 'product') {
            foreach ($stockLevels as $stockLevel) {
                if (array_key_exists('options', $stockLevel)) {
                    $selected = \Ppb\Utility::unserialize($stockLevel['options']);

                    if ($selected == $productAttributes) {
                        $quantityAvailable = $stockLevel['quantity'];
                    }
                }
            }
        }
        else {
            $quantityAvailable = $this->getData('quantity');
        }

        if ($quantityAvailable == self::UNLIMITED_QUANTITY) {
            return true;
        }

        $settings = $this->getSettings();

        if ($settings['pending_sales_listings_expire_hours']) {
            $quantity += $quantityAvailable;

            $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');
            /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
            foreach ($salesListings as $saleListing) {
                $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales');
                if ($sale['pending']) {
                    $quantity -= $saleListing['quantity'];
                }
            }
        }
        else {
            $quantity = $quantityAvailable;
        }

        return ($quantity > 0) ? $quantity : 0;
    }

    /**
     *
     * returns the time left in seconds or 0 for time left or if this doesnt apply for the listing
     *
     * @return int
     */
    public function getTimeLeft()
    {
        $timeLeft = strtotime($this->getData('end_time')) - time();

        return ($timeLeft > 0) ? $timeLeft : 0;
    }


    /**
     *
     * get the tax type but only if it applies to the selected buyer
     *
     * @param \Ppb\Db\Table\Row\User $buyer            buyer user model
     * @param int                    $billingAddressId billing address id, needed for a sale model
     * @param string                 $country          country override
     * @param string                 $state            state override
     *
     * @return bool|\Ppb\Db\Table\Row\TaxType
     */
    public function getTaxType(User $buyer = null, $billingAddressId = null, $country = null, $state = null)
    {
        if (!$this->getData('apply_tax')) {
            $this->_taxType = false;
        }
        else if ($this->_taxType === null) {
            /** @var \Ppb\Db\Table\Row\User $seller */
            $seller = $this->findParentRow('\Ppb\Db\Table\Users');

            $taxType = false;

            if (($taxTypeId = $seller->canApplyTax()) != false) {
                $taxTypeService = new Service\Table\TaxTypes();
                $taxTypeModel = $taxTypeService->findBy('id', $taxTypeId);

                if ($taxTypeModel instanceof TaxType) {
                    $locationsIds = \Ppb\Utility::unserialize($taxTypeModel->getData('locations_ids'));

                    if ($country === null && $state === null) {
                        if ($buyer === null) {
                            $buyer = $this->getUser();
                        }

                        if ($buyer instanceof User) {
                            $address = $buyer->getAddress($billingAddressId);

                            if (isset($address['country']) && isset($address['state'])) {
                                $country = $address['country'];
                                $state = $address['state'];
                            }
                        }
                    }

                    if (in_array($country, $locationsIds) || in_array($state, $locationsIds)
                    ) {
                        $taxType = $taxTypeModel;
                    }
                }
            }

            $this->_taxType = $taxType;
        }

        return $this->_taxType;
    }

    /**
     *
     * get listing owner
     *
     * @return \Ppb\Db\Table\Row\User|null
     */
    public function getOwner()
    {
        return $this->findParentRow('\Ppb\Db\Table\Users');
    }

    /**
     *
     * get the maximum bid from an array of bids
     *
     * @param bool $current         if set to true, include start_price in the array,
     *                              will display the current bid or start price
     *
     * @return float
     */
    public function currentBid($current = false)
    {
        $amounts = array(0);


        if ($current === true) {
            $amounts[] = $this->getData('start_price');
        }

        $bids = $this->getBids();

        foreach ($bids as $bid) {
            $amounts[] = $bid['amount'];
        }

        return max($amounts);
    }

    /**
     *
     * get the latest bid for the selected user (normally for the logged in user)
     *
     * @param $userId
     *
     * @return \Ppb\Db\Table\Row\Bid|null
     */
    public function yourBid($userId)
    {
        $bids = $this->getBids();

        foreach ($bids as $bid) {
            if ($bid['user_id'] == $userId) {
                return $bid;
            }
        }

        return null;
    }

    /**
     *
     * return the minimum amount that can be bid on the listing
     *
     * @param bool $amount      if set to true, it will calculate the minimum bid that will
     *                          need to be set, based on the amount input
     *                          the variable will always be higher than $bidAmount
     *
     * @return float
     */
    public function minimumBid($amount = null)
    {
        $settings = $this->getSettings();

        $bidAmount = $this->getData('start_price');

        $this->clearBids();

        if ($this->countDependentRowset('\Ppb\Db\Table\Bids') > 0) {
            $maximumBid = $this->currentBid();

            $bidIncrement = $this->getData('bid_increment');
            if ($bidIncrement > 0) {
                $bidAmount = $maximumBid + $bidIncrement;
            }
            else {
                $incrementsTable = $this->getBidIncrements()->getTable();
                $incrementAmount = $incrementsTable->fetchRow(
                    $incrementsTable->select('amount')
                        ->where('tier_from <= ?', $maximumBid)
                        ->where('tier_to > ?', $maximumBid)
                );

                $bidAmount = $maximumBid + $incrementAmount['amount'];
            }
        }

        if ($amount !== null) {
            if ($amount < $bidAmount) {
                $amount = $bidAmount;
            }

            $reservePrice = $this->getData('reserve_price');
            if ($bidAmount < $reservePrice) {
                $bidAmount = min(array(
                    $reservePrice, $amount));
            }

            if (!$settings['proxy_bidding']) {
                $bidAmount = $amount;
            }
        }

        return $bidAmount;
    }

    /**
     *
     * get payment methods available for the listing
     * convert old gateways field to new serialized field
     *
     * @param string $type type of payment methods to retrieve ('direct', 'offline' or null for all)
     *
     * @return array
     */
    public function getPaymentMethods($type = null)
    {
        $result = array();

        if (in_array($type, array(null, 'direct'))) {
            $paymentGatewaysService = new Service\Table\PaymentGateways();
            $directPayment = \Ppb\Utility::unserialize($this->getData('direct_payment'));
            if (!is_array($directPayment)) {
                $directPayment = @explode(',', $directPayment);

                if (is_array($directPayment)) {
                    $this->save(array(
                        'direct_payment' => serialize($directPayment),
                    ));
                }
            }
            $directPaymentMethods = array_filter((array)$directPayment);

            if (count($directPaymentMethods) > 0) {
                // check if the direct payment gateway is still enabled by the seller
                $userId = $this->getData('user_id');
                $rows = $paymentGatewaysService->getData($userId, $directPaymentMethods, true);

                foreach ($rows as $row) {
                    $className = '\\Ppb\\Model\\PaymentGateway\\' . $row['name'];

                    if (class_exists($className)) {
                        /** @var \Ppb\Model\PaymentGateway\AbstractPaymentGateway $gatewayModel */
                        $gatewayModel = new $className($userId);
                        if ($gatewayModel->enabled()) {
                            $result[] = array(
                                'id'   => $row['id'],
                                'type' => 'direct',
                                'name' => $row['name'],
                                'logo' => $row['logo_path'],
                            );
                        }
                    }
                }
            }
        }

        if (in_array($type, array(null, 'offline'))) {
            $offlinePaymentMethodsService = new Service\Table\OfflinePaymentMethods();
            $offlinePayment = \Ppb\Utility::unserialize($this->getData('offline_payment'));
            if (!is_array($offlinePayment)) {
                $offlinePayment = @explode(',', $offlinePayment);

                if (is_array($offlinePayment)) {
                    $this->save(array(
                        'offline_payment' => serialize($offlinePayment),
                    ));
                }
            }
            $offlinePaymentMethods = array_filter((array)$offlinePayment);

            if (count($offlinePaymentMethods) > 0) {
                $select = $offlinePaymentMethodsService->getTable()->select()
                    ->where('id IN (?)', new Expr(implode(', ', $offlinePaymentMethods)));
                $rows = $offlinePaymentMethodsService->fetchAll($select, array('order_id ASC', 'name ASC'));

                foreach ($rows as $row) {
                    $result[] = array(
                        'id'   => $row['id'],
                        'type' => 'offline',
                        'name' => $row['name'],
                        'logo' => $row['logo'],
                    );
                }
            }
        }

        return $result;
    }

    /**
     *
     * create an array of key => value from the custom fields and custom fields data tables
     * the custom fields data is available in the listing object when its created
     *
     * @param string $type
     *
     * @return array
     */
    public function getCustomFields($type = self::CUSTOM_FIELDS_TYPE)
    {
        $result = array();

        $categoryId = $this->getData('category_id');
        $addlCategoryId = $this->getData('addl_category_id');

        $categoriesFilter = array(0);

        if ($categoryId) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $this->getCategories()->getBreadcrumbs($categoryId)));
        }

        if ($addlCategoryId) {
            $categoriesFilter = array_merge($categoriesFilter, array_keys(
                $this->getCategories()->getBreadcrumbs($addlCategoryId)));
        }

        $customFields = $this->getCustomFieldsService()->getFields(array(
            'type'         => $type,
            'active'       => 1,
            'category_ids' => $categoriesFilter,
        ));

        $listingsService = new Service\Listings();
        $customFieldsData = $listingsService->getCustomFieldsData($this->getData('id'));

        foreach ($customFields as $row) {
            foreach ($row as $key => $value) {
                if (($array = @unserialize($value)) !== false) {
                    $row[$key] = $array;
                }
            }


            $customFieldValuePost = $this->getData('custom_field_' . $row['id']);
            if (!empty($customFieldValuePost)) {
                $row['value'] = $customFieldValuePost;
            }
            else if (!empty($customFieldsData[$row['id']])) {
                $row['value'] = $customFieldsData[$row['id']];
            }
            else {
                $row['value'] = null;
            }

            $row['value'] = \Ppb\Utility::unserialize($row['value']);

            $rowDisplay = array();

            $isMultiOptions = false;


            if (!empty($row['multiOptions'])) {
                if (count(array_filter($row['multiOptions']['key']))) {
                    $isMultiOptions = true;
                }
            }
            if ($isMultiOptions === true) {
                foreach ((array)$row['value'] as $customFldValue) {
                    if (($customFldKey = array_search($customFldValue, $row['multiOptions']['key'])) !== false) {
                        $customFldValue = trim($row['prefix'] . ' ' . $row['multiOptions']['value'][$customFldKey] . ' ' . $row['suffix']);
                    }
                    if (!empty($customFldValue)) {
                        $rowDisplay[] = $customFldValue;
                    }
                }
            }
            else if (is_string($row['value'])) {
                $rowDisplay[] = trim($row['prefix'] . ' ' . $row['value'] . ' ' . $row['suffix']);
            }

            $row['display'] = $rowDisplay;

            $result[] = $row;
        }

        return $result;
    }

    /**
     *
     * returns an array used by the url view helper to generate the listing details page uri
     *
     * @return array
     */
    public function link()
    {
        return array(
            'module'     => 'listings',
            'controller' => 'listing',
            'action'     => 'details',
            'id'         => $this->getData('id'),
            'name'       => $this->getData('name')
        );
    }

    /**
     *
     * check if the listing exists
     * should return false if it doesnt exist or if it is marked deleted and a user other
     * than its owner access the method, or if its inactive
     *
     * @param bool $extended        if set to true, it will return false if the item is marked deleted or inactive
     *                              otherwise it will return false only if the item doesnt exist in the database
     *
     * @return bool
     */
    public function exists($extended = true)
    {
        $listing = $this->getData();

        if (isset($listing['id'])) {
            unset($listing['id']);
        }

        if (empty($listing)) {
            return false;
        }

        if ($extended) {
            $user = $this->getUser();

            $userId = (isset($user['id'])) ? $user['id'] : null;

            if ($userId !== $listing['user_id'] &&
                ($listing['deleted'] || ($listing['active'] != 1) || !$listing['approved'])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * check if the listing exists and if the logged in / specified user is the owner of the listing
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return bool
     */
    public function isOwner(User $user = null)
    {
        $exists = $this->exists(false);

        if ($exists === true) {
            if ($user === null) {
                $user = $this->getUser();
            }

            $userId = (isset($user['id'])) ? $user['id'] : null;

            if ($userId !== $this->getData('user_id')) {
                return false;
            }
        }

        return $exists;
    }

    /**
     *
     * check if the listing exists and if the logged in / specified user has added it to the watch list
     *
     * @param \Ppb\Db\Table\Row\User $user
     *
     * @return bool
     */
    public function isWatched(User $user = null)
    {
        $exists = $this->exists(false);

        if ($exists === true) {
            if ($user === null) {
                $user = $this->getUser();
            }

            $bootstrap = Front::getInstance()->getBootstrap();
            $session = $bootstrap->getResource('session');

            $userToken = strval($session->getCookie(User::USER_TOKEN));

            $userId = (isset($user['id'])) ? $user['id'] : null;

            $select = $this->getTable()->select();

            if ($userId !== null) {
                $select->where('user_token = "' . $userToken . '" OR user_id = "' . $userId . '"');
            }
            else {
                $select->where('user_token = ?', $userToken);
            }

            $countWatched = $this->countDependentRowset('\Ppb\Db\Table\ListingsWatch', null, $select);

            if ($countWatched) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * get the number of users watching the listing
     *
     * @return int
     */
    public function countWatchers()
    {
        return $this->countDependentRowset('\Ppb\Db\Table\ListingsWatch');
    }

    /**
     *
     * count the total number of bids and offers the logged in user has posted on the listing
     *
     * @return int
     */
    public function getNbBidsOffers()
    {
        $user = $this->getUser();

        $select = $this->getTable()->select()
            ->where('user_id = ?', $user['id'])
            ->group('maximum_bid');
        $nbBids = $this->countDependentRowset('\Ppb\Db\Table\Bids', null, $select);

        $select = $this->getTable()->select()
            ->where('user_id = ?', $user['id']);
        $nbOffers = $this->findDependentRowset('\Ppb\Db\Table\Offers', null, $select);

        return ($nbBids + $nbOffers);

    }

    /**
     *
     * check if a purchase action can be validated, return true if valid or
     * an error message string otherwise
     *
     * @param string $type the type of purchase action requested to be validated (bid, buy, offer)
     *
     * @return bool|string
     */
    public function canPurchase($type = 'bid')
    {
        $user = $this->getUser();
        $translate = $this->getTranslate();

        if (empty($user)) {
            return $translate->_('Please log in to access the purchase confirmation page.');
        }
        else if ($user['id'] == $this->getData('user_id')) {
            return $translate->_('You are the owner of the listing.');
        }
        else if (!$this->isActiveAndOpen()) {
            return $translate->_('The listing is closed.');
        }
        else if (($blockMessage = $this->_isBlockedUserPurchasing()) !== false) {
            return $blockMessage;
        }

        $limitBidsOffersPerUser = $this->findParentRow('\Ppb\Db\Table\Users')
            ->getGlobalSettings('limit_bids_per_user');

        switch ($type) {
            case 'bid':
                if ($this->getData('listing_type') == 'product') {
                    return $translate->_('This listing is a product. No bids can be placed.');
                }
                else if ($limitBidsOffersPerUser > 0) {
                    if ($this->getNbBidsOffers() >= $limitBidsOffersPerUser) {
                        $sentence = $translate->_('You have reached the maximum allowed number of bids/offers per user (%s).');

                        return sprintf($sentence, $limitBidsOffersPerUser);
                    }
                }
                break;
            case 'buy':
                if ($this->isBuyOut() === false) {
                    return $translate->_('Buy Out is disabled for this listing.');
                }
                else if ($this->canAddToCart() === true) {
                    return $translate->_('The product can only be purchased through a shopping cart.');
                }
                break;
            case 'offer':
                if ($this->isMakeOffer() === false) {
                    return $translate->_('Make Offer is disabled for this listing.');
                }
                else if ($limitBidsOffersPerUser > 0) {
                    if ($this->getNbBidsOffers() >= $limitBidsOffersPerUser) {
                        $sentence = $translate->_('You have reached the maximum allowed number of bids/offers per user (%s).');

                        return sprintf($sentence, $limitBidsOffersPerUser);
                    }
                }
                break;
        }

        return true;
    }

    /**
     *
     * check if the listing can be added to the shopping cart
     * or return an error message otherwise
     *
     * the owner can add his own products to the shopping cart, but check out is not allowed.
     *
     * @param int|null   $quantity          if quantity is set, check by quantity as well
     * @param array|null $productAttributes the product attributes for which to check stock levels
     *
     * @return bool|string
     */
    public function canAddToCart($quantity = null, $productAttributes = null)
    {
        $settings = $this->getSettings();
        $translate = $this->getTranslate();

        if (!$settings['enable_shopping_cart']) {
            return $translate->_('The shopping cart module is disabled.');
        }
        else if ($this->getData('listing_type') != 'product') {
            return $translate->_('Only products can be added to a shopping cart.');
        }
        else if (!$this->isActiveAndOpen()) {
            return $translate->_('The listing is closed.');
        }
        else if (($blockMessage = $this->_isBlockedUserPurchasing()) !== false) {
            return $blockMessage;
        }
        else {
            /** @var \Ppb\Db\Table\Row\User $seller */
            $seller = $this->findParentRow('\Ppb\Db\Table\Users');

            switch ($settings['shopping_cart_applies']) {
                case 'store_owners':
                    if (!$seller->storeStatus(true)) {
                        return $translate->_("The seller's store is disabled.");
                    }
                    break;
                case 'store_listings':
                    if (!$seller->storeStatus(true) || $this->getData('list_in') == 'site') {
                        return $translate->_('Only products listed in store can be added to the shopping cart.');
                    }
                    break;
            }

            if ($quantity !== null) {
                $availableQuantity = $this->getAvailableQuantity(null, $productAttributes);
                if ($availableQuantity < $quantity) {
                    return $translate->_('Cannot add to cart - not enough quantity available.');
                }
            }
        }

        return true;
    }

    /**
     *
     * check if a pending offer can be accepted on a listing
     * TODO: [optional] move to Offer Model
     *
     * @param int        $quantity
     * @param array|null $productAttributes the product attributes for which to check stock levels
     *
     * @return bool
     */
    public function canAcceptOffer($quantity = 1, $productAttributes = null)
    {
        if ($this->isMakeOffer()) {
            $availableQuantity = $this->getAvailableQuantity(null, $productAttributes);

            if ($quantity <= $availableQuantity) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * drafts can be edited always
     *
     * open listings only can be edited, plus:
     * - for auctions:
     *      no activity (bids, offers, sale).
     *      the remaining duration needs to be greater than the minimum limit set in admin
     * - for products:
     *      there can be activity, but in this case only selected fields can be edited
     *
     * @return bool
     */
    public function canEdit()
    {
        if ($this->isDraft()) {
            return true;
        }

        $startTime = strtotime($this->getData('start_time'));
        if ((!$this->getData('closed') || $startTime > time()) && $this->getData('active')) {
            if ($this->getData('listing_type') == 'auction') {
                $settings = $this->getSettings();

                // the below snippet is needed for when checking if the listing can be edited right on the Listing\Create action
                $endTime = strtotime($this->getData('end_time'));
                if (!$endTime) {
                    $endTime = $startTime + $this->getData('duration') * 86400;
                }

                $timeRemaining = $endTime - time();
                if ($timeRemaining < $settings['auctions_editing_hours'] * 3600) {
                    return false;
                }
            }

            return ($this->getData('listing_type') == 'product') ? true : !$this->hasActivity();
        }

        return false;
    }

    /**
     *
     * check if the poster can close an open listing
     * products can be closed at any time, while auctions can be closed depending on the
     * close auction before end time setting
     *
     * @return bool
     */
    public function canClose()
    {
        if ($this->getStatus() == self::OPEN) {
            $settings = $this->getSettings();
            if ($this->getData('listing_type') == 'auction') {
                if ($settings['close_auctions_end_time'] || !$this->hasActivity()) {
                    return true;
                }
            }
            else {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * a listing can be deleted if not scheduled/closed or for auctions if there is no activity
     *
     * @return bool
     */
    public function canDelete()
    {

        if ($this->getStatus() == self::OPEN) {
            if ($this->getData('listing_type') == 'auction' && $this->hasActivity()) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * check if there was any activity on the listing (bidding, offers, purchasing)
     *
     * @return bool
     */
    public function hasActivity()
    {
        return (count($this->getBids()) ||
            count($this->getOffers()) ||
            count($this->getSales())
        ) ? true : false;
    }

    /**
     *
     * check if buy out is enabled for the listing
     *
     * @return bool
     */
    public function isBuyOut()
    {
        if ($this->getData('listing_type') == 'product') {
            return true;
        }
        else {
            $buyoutPrice = $this->getData('buyout_price');

            if ($buyoutPrice > 0) {
                $settings = $this->getSettings();

                if ($settings['enable_buyout']) {
                    $maximumBid = $this->currentBid();

                    if ($settings['always_show_buyout'] && $maximumBid < $buyoutPrice) {
                        return true;
                    }
                    else if (!$this->countDependentRowset('\Ppb\Db\Table\Bids') || $maximumBid < $this->getData('reserve_price')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     *
     * check if the make offer feature is enabled for the listing
     *
     * @return bool
     */
    public function isMakeOffer()
    {
        $settings = $this->getSettings();

        if ($settings['enable_make_offer'] && $this->getData('enable_make_offer')) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the listing is active and open
     *
     * @return bool
     */
    public function isActiveAndOpen()
    {
        if (
            $this->getStatus() == self::OPEN &&
            $this->getData('active') == 1 &&
            $this->getData('approved') == 1 &&
            $this->getData('draft') != 1 &&
            $this->getData('deleted') != 1
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * determine and set the 'approved' flag for the listing
     *
     * @param int|null $value
     *
     * @return $this
     */
    public function updateApproved($value = null)
    {
        $settings = $this->getSettings();

        if ($value !== null) {
            $data['approved'] = $value;
        }
        else {
            if ($settings['enable_listings_approval']) {
                $data['approved'] = 0;

                // notify admin TODO: the call is not ideal
                $mail = new \Admin\Model\Mail\Admin();
                $mail->listingApproval($this)->send();
            }
            else {
                $data['approved'] = 1;
            }
        }

        $this->save($data);

        return $this;
    }

    /**
     *
     * update listing quantity field
     *
     * @param int    $quantity
     * @param mixed  $productAttributes
     * @param string $operation
     *
     * @return int remaining quantity (for stock levels it will return -1)
     */
    public function updateQuantity($quantity, $productAttributes = null, $operation = self::SUBTRACT)
    {
        $stockLevels = \Ppb\Utility::unserialize($this->getData('stock_levels'));
        $productAttributes = \Ppb\Utility::unserialize($productAttributes);

        $quantityRemaining = self::UNLIMITED_QUANTITY;

        if (!empty($stockLevels) && $this->getData('listing_type') == 'product') {
            foreach ($stockLevels as $key => $stockLevel) {
                if ($stockLevel['options'] == $productAttributes) {
                    $quantityAvailable = $stockLevel['quantity'];
                    if ($quantityAvailable != self::UNLIMITED_QUANTITY) {

                        $stockLevels[$key]['quantity']
                            = $this->_quantityOperator($quantityAvailable, $quantity, $operation);
                    }
                }
            }

            $this->save(array(
                'stock_levels' => serialize($stockLevels),
            ));
        }
        else {
            $quantityAvailable = $this->getData('quantity');

            if ($quantityAvailable != self::UNLIMITED_QUANTITY) {
                $quantityRemaining
                    = $this->_quantityOperator($quantityAvailable, $quantity, $operation);

                $this->save(array(
                    'quantity' => $quantityRemaining,
                ));
            }
        }

        return $quantityRemaining;
    }

    /**
     *
     * apply operations to the quantity field
     *
     * @param int    $quantityAvailable
     * @param int    $quantity
     * @param string $operation
     *
     * @return int
     */
    protected function _quantityOperator($quantityAvailable, $quantity, $operation = self::ADD)
    {
        if ($quantityAvailable != self::UNLIMITED_QUANTITY) {
            return ($operation == self::ADD) ?
                ($quantityAvailable + $quantity) : ($quantityAvailable - $quantity);
        }

        return self::UNLIMITED_QUANTITY;
    }


    /**
     *
     * determine and set the 'active' flag for the listing
     *
     * @param int $active
     *
     * @return $this
     */
    public function updateActive($active = 1)
    {
        $this->save(array(
            'active' => (int)$active,
        ));

        return $this;
    }

    /**
     *
     * close a listing
     * when the cron closes the listing, the end time will not be modified.
     *
     * @param bool $automatic whether the listing is closed automatically (by the cron job) or not
     *
     * @return $this
     */
    public function close($automatic = false)
    {
        $data = array();

        if (strtotime($this->getData('start_time')) < time() && $this->getData('closed') == 0) {
            $data['closed'] = 1;
            if ($automatic === false) {
                $data['end_time'] = new Expr('now()');
            }

            $this->save($data);

            $this->_closedFlag = true;
        }

        return $this;
    }

    /**
     *
     * relist a listing
     *
     * - the quantity field is calculated from the quantity sum in the sales listings table
     * and the listing quantity field
     *
     * TODO: review the minimum duration option as it is not ideal
     *
     * @param bool $autoRelist auto relist flag
     *
     * @return int the id of the new listing
     */
    public function relist($autoRelist = false)
    {
        // calculate quantity
        $service = $this->getSalesListings();


        if (!$this->getData('stock_levels')) {
            $quantity = $this->getTable()->getAdapter()->fetchOne(
                $service->getTable()->select(array('Qty' => new Expr('sum(quantity)')))
                    ->where('listing_id = ?', $this->getData('id'))
            );
            $quantity += $this->getData('quantity');
        }
        else {
            $quantity = 0;
        }

        // by default all relisted listings are activated [admin default]
        // for the front end, we will run the method that will charge setup fees and set the approval flag
        $params = array(
            'start_time_type'      => 0,
            'closed'               => 0,
            'is_relisted'          => 1,
            'end_time_type'        => 0,
            'active'               => 1,
            'approved'             => 1,
            'deleted'              => 0,
            'nb_clicks'            => 0,
            'draft'                => 0,
            'counted_at'           => new Expr('null'),
            'last_count_operation' => self::COUNT_OP_NONE,
            'quantity'             => $quantity,
        );

        if ($this->getData('duration') == null && $this->getData('end_time') != null) { // duration
            $difference = strtotime($this->getData('end_time')) - strtotime($this->getData('start_time'));

            $params['end_time_type'] = 1;
            $params['end_time'] = date('Y-m-d H:i:s',
                time() + (($difference < 86400) ? 86400 : $difference)); // minimum 1 day duration
        }

        if ($autoRelist !== false) {
            $nbRelists = $this->getData('nb_relists') - 1;
            if ($nbRelists > 0) {
                $params['nb_relists'] = $nbRelists;
            }
            else {
                $params['nb_relists'] = 0;
                $params['auto_relist_sold'] = 0;
            }
        }

        $listingsService = new Service\Listings();
        $listingsService->setUser(
            $this->findParentRow('\Ppb\Db\Table\Users'));
        $listingId = null;

        switch ($this->getRelistMethod()) {
            case 'new':
                $listing = $listingsService->findBy('id', $this->getData('id'), true, true);
                $data = $listing->getData();

                unset($data['id']);

                $this->delete();

                // need to also copy custom fields and
                $listingId = $listingsService->save(array_merge($data, $params));
                break;
            case 'same':
                $params['start_time'] = date('Y-m-d H:i:s', time());
                if (!isset($params['end_time'])) {
                    $endTime = null;
                    $duration = $this->getData('duration');
                    if ($duration > 0) {
                        $endTime = time() + $duration * 86400;
                    }

                    $params['end_time'] = ($endTime) ? date('Y-m-d H:i:s', $endTime) : null;
                }

                $tableColumns = $this->getTable()->info(Table\AbstractTable::COLS);
                $params = array_intersect_key($params, array_flip(array_values($tableColumns)));

                $this->save($params);

                // delete all data from the bids, offers and sales_listings tables
                $dependentTables = array(
                    '\Ppb\Db\Table\Bids',
                    '\Ppb\Db\Table\Offers',
                    '\Ppb\Db\Table\SalesListings',
                );

                foreach ($dependentTables as $dependentTable) {
                    $rowset = $this->findDependentRowset($dependentTable);

                    /** @var \Cube\Db\Table\Row $row */
                    foreach ($rowset as $row) {
                        $row->delete();
                    }
                }

                $listingId = $this->getData('id');
                break;
        }

        return $listingId;
    }


    /**
     *
     * processes post listing setup actions:
     * charges listing setup fees in account mode
     * activates and approves listing based on different settings
     *
     * @param \Ppb\Db\Table\Row\Listing $savedListing in case we edit a listing
     *
     * @return string returns any related output messages
     */
    public function processPostSetupActions(Listing $savedListing = null)
    {
        /** @var \Cube\View $view */
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $message = null;
        /** @var \Ppb\Db\Table\Row\User $user */
        $user = $this->findParentRow('\Ppb\Db\Table\Users');

        $listingSetupService = new Service\Fees\ListingSetup(
            $this, $user);

        // apply listing setup voucher if available
        if ($voucherCode = $this->getData('voucher_code')) {
            $listingSetupService->setVoucher($voucherCode);
            $voucher = $listingSetupService->getVoucher();

            if ($voucher instanceof Voucher) {
                $voucher->updateUses();
            }
        }

        if ($savedListing instanceof Listing) {
            $listingSetupService->setSavedListing($savedListing);
        }

        $listingFees = $listingSetupService->calculate();

        // apply listing setup fee
        $totalAmount = $listingSetupService->getTotalAmount();
        $userPaymentMode = $user->userPaymentMode();

        if ($totalAmount > 0 && $userPaymentMode == 'live') {
            $this->updateActive(0);
        }
        else {
            $this->updateActive();

            if ($totalAmount > 0) {
                $user->updateBalance($totalAmount);

                $accountingService = new Service\Accounting();
                $accountingService->setListingId($this->getData('id'))
                    ->setUserId($user['id'])
                    ->saveMultiple($listingFees);

                if ($view->isHelper('amount')) {
                    $translate = $this->getTranslate();
                    $sentence = $translate->_('The amount of %s has been debited from your account balance.');
                    $message = sprintf($sentence, $view->amount($totalAmount));
                }
            }
        }

        $this->updateApproved();

        return $message;
    }

    /**
     *
     * process category counter for the listing object
     * will work based on the counted_at and last_count_operation flags and will be called by the cron job
     *
     * @param bool|string $force force count
     *
     * @return bool true if counted, false if unmodified
     */
    public function processCategoryCounter($force = false)
    {
        $counted = false;
        $operation = self::COUNT_OP_ADD;

        if ($force === false) {
            if ($this->isActiveAndOpen()) {
                $operation = self::COUNT_OP_ADD;
            }
            else if ($this->getData('list_in') != 'store') {
                $operation = self::COUNT_OP_SUBTRACT;
            }
        }

        $lastCountOperation = $this->getData('last_count_operation');

        if (
            $force === true ||
            ($operation == self::COUNT_OP_ADD && $lastCountOperation != self::COUNT_OP_ADD) ||
            ($operation == self::COUNT_OP_SUBTRACT && $lastCountOperation == self::COUNT_OP_ADD)
        ) {
            $this->countCategoriesCounter($operation);

            $counted = true;
        }

        $this->save(array(
            'last_count_operation' => $operation,
            'counted_at'           => date('Y-m-d H:i:s', time()),
        ));

        return $counted;
    }

    /**
     *
     * do the requested category counting operation
     * 7.2 - added a fix which was causing this method to loop indefinitely if a category that was to be counted didn't exist
     * 7.7 - only count the additional category if the setting is enabled in admin
     *
     * @param $operation
     *
     * @return $this
     */
    public function countCategoriesCounter($operation)
    {
        $settings = $this->getSettings();

        $ids = array(
            $this->getData('category_id'),
        );

        if ($settings['addl_category_listing']) {
            $ids[] = $this->getData('addl_category_id');
        }

        foreach ($ids as $id) {
            if ($id > 0) {
                do {
                    /** @var \Ppb\Db\Table\Row\Category $category */
                    $category = $this->getCategories()->findBy('id', $id);

                    $id = 0;
                    if (count($category) > 0) {
                        if ($operation == self::COUNT_OP_SUBTRACT) {
                            $category->subtractCounter($this->getData('listing_type'));
                        }
                        else if ($operation == self::COUNT_OP_ADD) {
                            $category->addCounter($this->getData('listing_type'));
                        }

                        $id = $category['parent_id'];
                    }
                } while ($id > 0);
            }
        }

        return $this;
    }

    /**
     *
     * The method will post a bid, an offer or create a sale if the buy out method is used
     *
     * @param array  $data   place bid related data
     * @param string $type   the type of purchase action (bid|buy|offer)
     * @param int    $userId force user id
     *
     * @return string return message that is to be output
     */
    public function placeBid(array $data, $type = 'bid', $userId = null)
    {
        $bootstrap = Front::getInstance()->getBootstrap();
        if ($userId === null) {
            $user = $bootstrap->getResource('user');
            $userId = $user['id'];
        }
        $view = $bootstrap->getResource('view');
        $translate = $this->getTranslate();

        $message = null;

        switch ($type) {
            case 'bid':
                $service = new Service\Bids();

                $data['user_id'] = $userId;
                $data['listing_id'] = $this->getData('id');

                $service->save($data);
                $message = $service->getMessage();

                $usersService = new Service\Users();

                $user = $usersService->findBy('id', $userId);

                $mail = new \Listings\Model\Mail\OwnerNotification();
                $mail->newBid($this->getData('id'), $user, $data['amount'])->send();

                break;
            case 'buy':
                $service = new Service\Sales();

                $quantity = 1;
                if (isset($data['quantity'])) {
                    $quantity = ($data['quantity'] > 0) ? $data['quantity'] : $quantity;
                }

                $price = $this->getProductPrice($data['product_attributes']);

                $data = array(
                    'buyer_id'            => $userId,
                    'seller_id'           => $this->getData('user_id'),
                    'postage_id'          => (int)$data['postage_id'],
                    'shipping_address_id' => $data['shipping_address_id'],
                    'apply_insurance'     => (bool)$data['apply_insurance'],
                    'voucher_details'     => $data['voucher_details'],
                    'listings'            => array(
                        array(
                            'listing_id'         => $this->getData('id'),
                            'price'              => $price,
                            'quantity'           => $quantity,
                            'product_attributes' => $data['product_attributes'],
                        )
                    ),
                );
                $service->save($data);


                $message = sprintf($translate->_('You have successfully purchased this item - price: %s, quantity purchased: %s.'),
                    $view->amount($price, $this->getData('currency')), $quantity);

                $this->setSaleId(
                    $service->getSaleId());

                break;
            case 'offer':
                $service = new Service\Offers();

                $data['user_id'] = $userId;
                $data['type'] = 'offer';
                if (empty($data['receiver_id'])) {
                    $data['receiver_id'] = $this->getData('user_id');
                }
                $data['listing_id'] = $this->getData('id');
                $quantity = 1;
                if (isset($data['quantity'])) {
                    $quantity = ($data['quantity'] > 0) ? $data['quantity'] : $quantity;
                }
                $data['quantity'] = $quantity;

                $id = $service->save($data);
                $message = sprintf($translate->_('Your offer, in the amount of %s, has been posted successfully.'),
                    $view->amount($data['amount'], $this->getData('currency')));

                $row = $service->findBy('id', $id);

                $mail = new \Listings\Model\Mail\UserNotification();
                $mail->newOffer($this, $row)->send();

                break;
        }

        return $message;
    }


    /**
     *
     * get the price of the product based on the selected product attributes and their price variations
     *
     * @param mixed $productAttributes
     *
     * @return float
     */
    public function getProductPrice($productAttributes = null)
    {
        $price = $this->getData('buyout_price');

        $stockLevels = \Ppb\Utility::unserialize($this->getData('stock_levels'));
        $productAttributes = \Ppb\Utility::unserialize($productAttributes);

        if (!empty($stockLevels) && $this->getData('listing_type') == 'product') {
            foreach ($stockLevels as $key => $stockLevel) {
                if ($stockLevel['options'] == $productAttributes) {
                    $price += $stockLevel['price'];
                    break;
                }
            }
        }

        return $price;
    }

    /**
     *
     * this method assigns a set bid as a winning bid on a listing, or it assigns it automatically when:
     * - we have a standard auction with a high bid greater or equal to the reserve price
     *   (fixed in V7.3, before it required the bid amount to be greater than the reserve)
     * - we have a first bidder auction (later)
     *
     * @param \Ppb\Db\Table\Row\Bid $bid the bid that should be assigned as winning bid, or null if the bid should be selected automatically
     *
     * @throws \RuntimeException
     * @return int|false    the id of the resulted sale or false if no winner has been assigned
     */
    public function assignWinner(Bid $bid = null)
    {
        if ($bid !== null) {
            if ($bid['listing_id'] !== $this->getData('id')) {
                throw new \RuntimeException("The listing id of the bid object inserted in the assignWinner() method is invalid");
            }
        }
        else {
            $this->clearBids();
            $bids = $this->getBids();

            if (count($bids) > 0) {
                switch ($this->getData('listing_type')) {
                    case 'auction':
                        $highBid = $bids[0];

                        if ($highBid['amount'] >= $this->getData('reserve_price')) {
                            $bid = $highBid;
                        }
                        break;
                }
            }
        }

        if ($bid instanceof Bid) {
            $service = new Service\Sales();

            $data = array(
                'buyer_id'  => $bid['user_id'],
                'seller_id' => $this->getData('user_id'),
                'listings'  => array(
                    array(
                        'listing_id' => $this->getData('id'),
                        'price'      => $bid['amount'],
                        'quantity'   => 1,
                    )
                ),
            );

            $service->save($data);

            $this->setSaleId(
                $service->getSaleId());

            return $this->getSaleId();

        }

        return false;
    }

    /**
     *
     * add a new click to the listing
     *
     * @return $this
     */
    public function addClick()
    {
        $nbClicks = $this->_data['nb_clicks'];

        $this->save(array(
            'nb_clicks' => new Expr('nb_clicks + 1')
        ));
        $this->_data['nb_clicks'] = $nbClicks + 1;

        return $this;
    }

    /**
     *
     * add the product to the shopping cart
     * first we check if a shopping cart is active and if it is, add the item to the existing shopping cart
     *
     * the user id, insurance and postage are only saved when updating the shopping cart or checking out
     *
     * carts need to match the exact same fields like when combining invoices:
     *
     * @param int|null   $quantity
     * @param array|null $productAttributes
     *
     * @return $this
     */
    public function addToCart($quantity = null, $productAttributes = null)
    {
        $quantity = ($quantity < 1) ? 1 : $quantity;

        $bootstrap = Front::getInstance()->getBootstrap();
        $session = $bootstrap->getResource('session');

        $userToken = strval($session->getCookie(User::USER_TOKEN));


        $salesService = new Service\Sales();

        $select = $salesService->getTable()->select()
            ->where('user_token = ?', $userToken)
            ->where('seller_id = ?', $this->getData('user_id'))
            ->where('sale_data REGEXP \'"currency";s:[[:digit:]]+:"' . $this->getData('currency') . '"\'')
            ->where('sale_data REGEXP \'"country";s:[[:digit:]]+:"' . $this->getData('country') . '"\'')
            ->where('sale_data REGEXP \'"state";s:[[:digit:]]+:"' . $this->getData('state') . '"\'')
            ->where('sale_data REGEXP \'"address";s:[[:digit:]]+:"' . $this->getData('address') . '"\'')
            ->where('pending = ?', 1);

        if ($pickupOptions = $this->getData('pickup_options')) {
            $select->where('sale_data REGEXP \'"pickup_options";s:[[:digit:]]+:"' . $pickupOptions . '"\'');
        }
        else {
            $select->where('sale_data REGEXP \'"pickup_options";N\'');
        }

        if ($applyTax = $this->getData('apply_tax')) {
            $select->where('sale_data REGEXP \'"apply_tax";s:[[:digit:]]+:"' . $applyTax . '"\'');
        }
        else {
            $select->where('sale_data REGEXP \'"apply_tax";N\'');
        }

        $row = $salesService->fetchAll($select)->getRow(0);

        $data = array(
            'user_token' => $userToken,
            'seller_id'  => $this->getData('user_id'),
            'quantity'   => (int)$quantity,
            'pending'    => 1,
            'listings'   => array(
                array(
                    'listing_id'         => $this->getData('id'),
                    'price'              => $this->getProductPrice($productAttributes),
                    'quantity'           => (int)$quantity,
                    'product_attributes' => (count($productAttributes) > 0) ? serialize($productAttributes) : null,
                )
            ),
        );

        if ($row !== null) {
            $data['id'] = $row->getData('id');
        }

        $salesService->save($data);

        return $this;
    }

    /**
     *
     * save the recently viewed listing in the table
     *
     * @return $this
     */
    public function addRecentlyViewedListing()
    {
        $settings = $this->getSettings();

        if ($settings['enable_recently_viewed_listings']) {
            $bootstrap = Front::getInstance()->getBootstrap();
            $session = $bootstrap->getResource('session');

            $user = $this->getUser();
            $userId = (isset($user['id'])) ? $user['id'] : null;
            $userToken = strval($session->getCookie(User::USER_TOKEN));

            if (!empty($userToken)) {
                $recentlyViewedListingsService = new Service\RecentlyViewedListings();

                $select = $recentlyViewedListingsService->getTable()->select()
                    ->where('user_token = ?', $userToken)
                    ->where('listing_id = ?', $this->getData('id'));

                $row = $recentlyViewedListingsService->fetchAll($select)->getRow(0);

                $data = array(
                    'user_token' => $userToken,
                    'listing_id' => $this->getData('id'),
                    'user_id'    => $userId,
                );

                if ($row !== null) {
                    $data['id'] = $row->getData('id');
                }

                $recentlyViewedListingsService->save($data);
            }
        }

        return $this;
    }

    /**
     *
     * save the updated row in the table
     * add updated_at flag unless already set and if not saving the whole data array
     *
     * 7.7 - whenever we use the save method on a listing, if the listing was active and open
     * before the save operation, we basically remove the counter and ask the cron to count the item again
     * (code duplicated from the listings service save() method)
     *
     * @param array $data   partial data to be saved
     *                      the complete row is saved if this parameter is null
     *
     * @return $this
     */
    public function save(array $data = null)
    {
        if ($data === null) {
            $data = $this->_data;
        }

        if (!array_key_exists('updated_at', $data)) {
            $data['updated_at'] = date('Y-m-d H:i:s', time());
        }

        if (!array_key_exists('last_count_operation', $data)) {
            if (
                $this->isActiveAndOpen() &&
                $this->getData('list_in') != 'store' &&
                $this->getdata('last_count_operation') == self::COUNT_OP_ADD
            ) {
                $this->countCategoriesCounter(self::COUNT_OP_SUBTRACT);
                $data['last_count_operation'] = self::COUNT_OP_NONE;
            }
        }

        return parent::save($data);
    }

    /**
     *
     * delete or mark a listing as deleted
     * drafts are also deleted directly, rather than just being marked deleted
     * also subtract the listing from the category counters if it wasn't already subtracted
     *
     * 7.7 - for a pending cart, delete related rows from the sales listings table
     *
     * @param bool $admin if true - admin delete (delete all related files as well)
     *
     * @return bool|integer returns true if marked deleted, or number of affected rows if using admin delete
     */
    public function delete($admin = false)
    {
        $this->addData('deleted', 1)
            ->processCategoryCounter();

        $salesListings = $this->findDependentRowset('\Ppb\Db\Table\SalesListings');

        /** @var \Ppb\Db\Table\Row\SaleListing $saleListing */
        foreach ($salesListings as $saleListing) {
            $sale = $saleListing->findParentRow('\Ppb\Db\Table\Sales');
            if ($sale['pending']) {
                $saleListing->delete();
            }
        }

        if ($admin === true || $this->getData('draft')) {
            $this->findDependentRowset('\Ppb\Db\Table\ListingsMedia')
                ->delete();

            $this->getCustomFieldsData()
                ->delete(self::CUSTOM_FIELDS_TYPE, $this->getData('id'));

            return parent::delete();
        }
        else {
            $user = $this->getUser();

            if (isset($user['id'])) {
                $this->save(array(
                    'deleted' => 1,
                ));

                return true;
            }
        }

        return false;

    }

    /**
     *
     * check if the seller has checked the must pick-up option for the listing
     *
     * @return bool
     */
    public function pickUpOnly()
    {
        $settings = $this->getSettings();

        if ($settings['enable_shipping'] && $settings['enable_pickups'] &&
            $this->getData(ShippingModel::FLD_PICKUP_OPTIONS) == ShippingModel::MUST_PICKUP
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * generate a short description for the listing, from the description field
     *
     * @param int $maxChars
     *
     * @return string
     */
    public function shortDescription($maxChars = null)
    {
        /** @var \Cube\View $view */
        $view = Front::getInstance()->getBootstrap()->getResource('view');

        $description = strip_tags($view->renderHtml($this->getData('description')));
        $length = strlen($description);

        $maxChars = ($maxChars === null) ? self::SHORT_DESC_MAX_CHARS : $maxChars;

        return substr($description, 0, $maxChars) . (($length > $maxChars) ? ' ... ' : '');
    }

    /**
     *
     * returns an array used by the url view helper to generate the purchase link for the listing
     * it can be a link to the buy out confirm page, or a link to the add to cart action
     *
     * @return array
     */
    public function purchaseLink()
    {
        if ($this->canAddToCart() === true) {
            return array(
                'module'     => 'listings',
                'controller' => 'cart',
                'action'     => 'add',
                'id'         => $this->getData('id'),
            );

        }

        return array(
            'module'     => 'listings',
            'controller' => 'purchase',
            'action'     => 'confirm',
            'type'       => 'buy',
            'id'         => $this->getData('id'),
        );
    }

    /**
     *
     * check if free shipping is offered for the listing
     *
     * this will work if:
     * - free postage is offered and free postage amount <= current bid
     * - item based postage > there is at least one option that has the price set to 0
     * - flat rates > the first item value is set to 0
     *
     * @return bool
     */
    public function isFreeShipping()
    {
        /** @var \Ppb\Db\Table\Row\User $owner */
        $owner = $this->findParentRow('\Ppb\Db\Table\Users');
        $postageSettings = $owner->getPostageSettings();

        if (isset($postageSettings[ShippingModel::SETUP_FREE_POSTAGE])) {
            if ($postageSettings[ShippingModel::SETUP_FREE_POSTAGE] &&
                $postageSettings[ShippingModel::SETUP_FREE_POSTAGE_AMOUNT] <= $this->currentBid(true)
            ) {
                return true;
            }
        }

        if (isset($postageSettings[ShippingModel::SETUP_POSTAGE_TYPE])) {
            if ($postageSettings[ShippingModel::SETUP_POSTAGE_TYPE] == ShippingModel::POSTAGE_TYPE_ITEM) {
                $postage = $this->getData(ShippingModel::FLD_POSTAGE);
                if (isset($postage['price'])) {
                    foreach ($postage['price'] as $key => $value) {
                        if ($value == 0) {
                            return true;
                        }
                    }
                }
            }
            else if ($postageSettings[ShippingModel::SETUP_POSTAGE_TYPE] == ShippingModel::POSTAGE_TYPE_FLAT) {
                if ($postageSettings[ShippingModel::SETUP_POSTAGE_FLAT_FIRST] <= 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * check if the listing is a draft
     *
     * @return bool
     */
    public function isDraft()
    {
        return (bool)$this->getData('draft');
    }

    /**
     *
     * check if the logged in user is blocked from purchasing by the seller or admin
     * and return the block message if true
     *
     * @return string|false
     */
    protected function _isBlockedUserPurchasing()
    {
        $user = $this->getUser();

        $dataToBlock = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
        );

        if (!empty($user)) {
            $dataToBlock['username'] = $user['username'];
            $dataToBlock['email'] = $user['email'];

        }

        $blockedUsersService = new Service\BlockedUsers();

        $blockedUser = $blockedUsersService->check(
            BlockedUser::ACTION_PURCHASE, $dataToBlock, $this->getData('user_id'));

        if ($blockedUser !== null) {
            $view = Front::getInstance()->getBootstrap()->getResource('view');

            return $view->blockStatus($blockedUser)->blockMessage();
        }

        return false;
    }

    /**
     *
     * call magic method, used for retrieving dependent data
     *
     * @param string $name      the name of the method from the \Cube\Db\Table\Row\AbstractRow method
     * @param array  $arguments the arguments accepted by the method
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $listing = $this;

        return call_user_func_array(
            array($listing, $name), $arguments);
    }

}

