<?php

/**
 *
 * PHP Pro Bid $Id$ txRZDVBPG5TvTXNQnw6gzKCOOkcE+1275KfpIrhU+7k=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * fees table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Fees as FeesTable,
    Ppb\Service,
    Ppb\Db\Table\Row\User,
    Ppb\Db\Table\Row\Category,
    Ppb\Db\Table\Row\TaxType,
    Ppb\Db\Table\Row\Voucher;

class Fees extends AbstractService
{

    /**
     * default calculation type
     */
    const DEFAULT_CALCULATION_TYPE = 'flat';

    /**
     * default fee type
     */
    const DEFAULT_TYPE = 'default';

    /**
     * listing setup/sale
     */
    const SETUP = 'setup';
    const SALE = 'sale';
    const HPFEAT = 'hpfeat';
    const CATFEAT = 'catfeat';
    const HIGHLIGHTED = 'highlighted';
    const BOLD = 'bold';
    const IMAGES = 'image';
    const MEDIA = 'video';
    const DIGITAL_DOWNLOADS = 'download';
    const ADDL_CATEGORY = 'addl_category_id';
    const BUYOUT = 'buyout_price';
    const RESERVE = 'reserve_price';
    const MAKE_OFFER = 'enable_make_offer';
    const ITEM_SWAP = 'item_swap';
    const SUBTITLE = 'subtitle';

    /**
     * user verification
     */
    const USER_VERIFICATION = 'user_verification';

    /**
     * user signup
     */
    const SIGNUP = 'signup';

    /**
     * store subscription
     */
    const STORE_SUBSCRIPTION = 'store_subscription';

    /**
     * additional constants
     */
    const NB_FREE_IMAGES = 'free_images';
    const NB_FREE_MEDIA = 'free_media';
    const NB_FREE_DOWNLOADS = 'free_downloads';

    /**
     *
     * fees to be applied
     * overridden by child classes
     *
     * @var array
     */
    protected $_fees = array();

    /**
     *
     * types of fees that will have tiers enabled
     *
     * @var array
     */
    protected $_feesTiers = array(
        self::SETUP,
        self::SALE
    );

    /**
     *
     * types of fees that cannot be set as category specific
     *
     * @var array
     */
    protected $_feesNoCategory = array(
        self::SIGNUP,
    );

    /**
     *
     * constants which should not have the preferred sellers reduction applied on
     *
     * @var array
     */
    protected $_additionalConstants = array(
        self::NB_FREE_IMAGES,
        self::NB_FREE_MEDIA,
        self::NB_FREE_DOWNLOADS,
    );

    /**
     *
     * the name of the fee, used to compare against the fees tiers array
     *
     * @var string
     */
    protected $_feeName;

    /**
     *
     * category id to calculate the fee against
     * (only if custom_fees = true)
     *
     * @var int
     */
    protected $_categoryId;

    /**
     *
     * the amount for which the fee is to be calculated
     * (applies to percentage based fees)
     *
     * @var float
     */
    protected $_amount;

    /**
     *
     * currency that the amount (if set) is in.
     * if different than the site's currency, calculate an exchange rate for percentage fees
     *
     * @var string
     */
    protected $_currency;

    /**
     *
     * currencies table service
     *
     * @var \Ppb\Service\Table\Currencies
     */
    protected $_currencies;

    /**
     *
     * flag to disable fees
     *
     * @var bool
     */
    protected $_disableFees = false;

    /**
     *
     * the tax that will apply to the fees
     *
     * @var \Ppb\Db\Table\Row\TaxType
     */
    protected $_taxType;

    /**
     *
     * voucher object that will apply to the fees
     *
     * @var \Ppb\Db\Table\Row\Voucher
     */
    protected $_voucher;

    /**
     *
     * payment completed redirect url in array or string format
     *
     * @var array|string
     */
    protected $_redirect;

    /**
     *
     * fee type - default fees have this field as null
     * will be used for listing types that will require their own fees like wanted ads etc
     *
     * @var string
     */
    protected $_type = self::DEFAULT_TYPE;

    /**
     *
     * transaction details
     *
     * @var mixed
     */
    protected $_transactionDetails;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new FeesTable());
    }

    /**
     *
     * set fees array
     *
     * @param array $fees
     *
     * @return $this
     */
    public function setFees($fees)
    {
        $this->_fees = $fees;

        return $this;
    }

    /**
     *
     * get fees array or the name of a certain fee if key is specified
     *
     * @param string $key
     *
     * @return array|string
     */
    public function getFees($key = null)
    {
        if (array_key_exists($key, $this->_fees)) {
            return $this->_fees[$key];
        }

        return $this->_fees;
    }


    /**
     *
     * get fees tiers array
     *
     * @return array
     */
    public function getFeesTiers()
    {
        return $this->_feesTiers;
    }

    /**
     *
     * set fees tiers array
     *
     * @param array $feesTiers
     *
     * @return $this
     */
    public function setFeesTiers($feesTiers)
    {
        $this->_feesTiers = $feesTiers;

        return $this;
    }

    /**
     *
     * check if a fee has tiers
     *
     * @param string|null $feeName
     *
     * @return bool
     */
    public function hasTiers($feeName = null)
    {
        if ($feeName === null) {
            $feeName = $this->_feeName;
        }

        return (in_array($feeName, $this->_feesTiers));
    }

    /**
     *
     * get fees with no category array
     *
     * @return array
     */
    public function getFeesNoCategory()
    {
        return $this->_feesNoCategory;
    }

    /**
     *
     * set fees with no category array
     *
     * @param array $feesNoCategory
     *
     * @return $this
     */
    public function setFeesNoCategory($feesNoCategory)
    {
        $this->_feesNoCategory = $feesNoCategory;

        return $this;
    }

    /**
     *
     * check if a fee has no categories option
     *
     * @param string|null $feeName
     *
     * @return bool
     */
    public function hasNoCategory($feeName = null)
    {
        if ($feeName === null) {
            $feeName = $this->_feeName;
        }

        return (in_array($feeName, $this->_feesNoCategory));
    }

    /**
     *
     * get fee name
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getFeeName()
    {
        if ($this->_feeName === null) {
            throw new \RuntimeException(
                "A fee name is required when querying the fees table.");
        }

        return $this->_feeName;
    }

    /**
     *
     * set the fee name
     *
     * @param string $feeName
     *
     * @return $this
     */
    public function setFeeName($feeName)
    {
        $this->_feeName = $feeName;

        return $this;
    }

    /**
     *
     * set user data
     *
     * @param int|string|\Ppb\Db\Table\Row\User $user
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setUser($user)
    {
        if (is_int($user) || is_string($user)) {
            $userService = new Service\Users();
            $user = $userService->findBy('id', $user);
        }

        if (!$user instanceof User) {
            throw new \InvalidArgumentException("The method requires a string, an integer or an object of type \Ppb\Db\Table\Row\User.");
        }

        $this->_user = $user;

        return $this;
    }

    /**
     *
     * get calculation amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     *
     * set calculation amount
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->_amount = $amount;

        return $this;
    }

    /**
     *
     * get currency for the amount for which the fee is to be calculated
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     *
     * set currency for the amount for which the fee is to be calculated
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;

        return $this;
    }

    /**
     *
     * get category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->_categoryId;
    }

    /**
     *
     * set fee category id, but only if custom fees are enabled
     *
     * @param int $categoryId
     *
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        if ($categoryId > 0) {
            $categories = new Service\Table\Relational\Categories();

            $mainCategory = $categories->getRoot($categoryId);

            if ($mainCategory instanceof Category) {
                $categoryId = ($mainCategory->getData('custom_fees')) ? $mainCategory->getData('id') : null;
            }
            else {
                $categoryId = null;
            }
        }

        $this->_categoryId = $categoryId;

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
     * @return $this
     */
    public function setCurrencies(Service\Table\Currencies $currencies)
    {
        $this->_currencies = $currencies;

        return $this;
    }

    /**
     *
     * get fees calculation flag
     *
     * @return bool
     */
    public function isDisabledFees()
    {
        return $this->_disableFees;
    }

    /**
     *
     * set fees calculation flag
     *
     * @param bool $disableFees
     *
     * @return $this
     */
    public function disableFees($disableFees = true)
    {
        $this->_disableFees = (bool)$disableFees;

        return $this;
    }

    /**
     *
     * set tax type object
     *
     * @param \Ppb\Db\Table\Row\TaxType $taxType
     *
     * @return $this
     */
    public function setTaxType(TaxType $taxType)
    {
        $this->_taxType = $taxType;

        return $this;
    }

    /**
     *
     * get tax type object
     *
     * @return \Ppb\Db\Table\Row\TaxType
     */
    public function getTaxType()
    {
        if (!$this->_taxType instanceof TaxType) {
            $settings = $this->getSettings();

            $this->setTaxType(
                new TaxType(array(
                    'table' => new \Ppb\Db\Table\TaxTypes(),
                    'data'  => array()
                )));

            if ($settings['enable_tax_fees'] && $settings['tax_fees_type']) {
                $taxTypesService = new Service\Table\TaxTypes();
                $taxType = $taxTypesService->findBy('id', $settings['tax_fees_type']);

                if ($taxType instanceof TaxType) {
                    $locationsIds = \Ppb\Utility::unserialize($taxType->getData('locations_ids'));

                    $address = $this->getUser()->getAddress();

                    if (isset($address['country']) && isset($address['state']) && is_array($locationsIds)) {
                        if (in_array($address['country'], $locationsIds) || in_array($address['state'], $locationsIds)) {
                            $this->setTaxType($taxType);
                        }
                    }
                }
            }
        }

        return $this->_taxType;
    }

    /**
     *
     * set voucher object
     *
     * @param string|\Ppb\Db\Table\Row\Voucher $voucher voucher code or voucher object
     *
     * @return $this
     */
    public function setVoucher($voucher)
    {
        if (!$voucher instanceof Voucher) {
            $vouchersService = new Service\Vouchers();
            $voucher = $vouchersService->findBy($voucher);
        }

        $this->_voucher = $voucher;

        return $this;
    }

    /**
     *
     * get voucher object
     *
     * @return \Ppb\Db\Table\Row\Voucher
     */
    public function getVoucher()
    {
        return $this->_voucher;
    }

    /**
     *
     * set redirect params
     *
     * @param array|string $redirect
     *
     * @return $this
     */
    public function setRedirect($redirect)
    {
        $this->_redirect = $redirect;

        return $this;
    }

    /**
     *
     * get redirect params
     *
     * @return array|string
     */
    public function getRedirect()
    {
        return $this->_redirect;
    }

    /**
     *
     * set listing type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;

        return $this;
    }

    /**
     *
     * get listing type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     *
     * get transaction details
     *
     * @return mixed
     */
    public function getTransactionDetails()
    {
        return $this->_transactionDetails;
    }

    /**
     *
     * set transaction details
     *
     * @param mixed $transactionDetails
     *
     * @return $this
     */
    public function setTransactionDetails($transactionDetails)
    {
        $this->_transactionDetails = $transactionDetails;

        return $this;
    }

    /**
     *
     * get the amount of a certain fee
     * calculates based on preferred seller feature
     *
     * @param string $name
     * @param float  $amount
     * @param int    $categoryId
     * @param null   $currency
     *
     * @return float|null       return the fee amount or null if no fee applies for the selected action
     */
    public function getFeeAmount($name = null, $amount = null, $categoryId = null, $currency = null)
    {
        if ($this->isDisabledFees()) {
            return null;
        }

        if ($name === null) {
            $name = $this->getFeeName();
        }

        if ($amount === null) {
            $amount = $this->getAmount();
        }

        if ($categoryId !== null) {
            $this->setCategoryId($categoryId);
        }
        $categoryId = $this->getCategoryId();

        if ($currency !== null) {
            $this->setCurrency($currency);
        }
        $currency = $this->getCurrency();

        $select = $this->_table->select(array('amount', 'calculation_type'))
            ->where("name = ?", $name)
            ->where("type = ?", $this->getType());

        if (in_array($name, $this->_feesTiers) && $amount !== null) {
            $amountConverted = $this->getCurrencies()->convertAmount($amount, $currency);
            $select->where("tier_from < ?", $amountConverted)
                ->where("tier_to >= ?", $amountConverted);
        }

        if ($categoryId !== null) {
            $select->where("category_id = ?", $categoryId);
        }
        else {
            $select->where("category_id IS NULL");
        }

        $row = $this->_table->fetchRow($select);

        if ($row !== null) {
            if ($row['calculation_type'] == 'percent') {
                $amount = $row['amount'] * $amount / 100;
                $amount = $this->getCurrencies()->convertAmount($amount, $currency);
            }
            else {
                $amount = $row['amount'];
            }

            if (!in_array($name, $this->_additionalConstants)) {
                $amount = $this->_applyPreferredSellersReduction($amount);
                $amount = $this->_addTax($amount);
            }

            return $amount;
//            return $this->_applyVoucher($amount, $settings['currency']);
        }

        return null;
    }

    /**
     *
     * save data in the fees table
     *
     * @param array $data
     *
     * @return $this
     */
    public function save(array $data)
    {
        $categoryId = null;
        $calculationType = 'flat';
        $feeName = $this->getFeeName();

        if (isset($data['category_id'])) {
            $categoryId = intval($data['category_id']);
            unset($data['category_id']);
        }

        if (isset($data['calculation_type'])) {
            $calculationType = $data['calculation_type'];
            unset($data['calculation_type']);
        }

        if (isset($data['sale_fee_payer'])) {
            $settingsService = new Settings();
            $settingsService->save(array(
                'sale_fee_payer' => $data['sale_fee_payer'],
            ));
        }

        if (isset($data['type'])) {
            $type = $data['type'];
            unset($data['type']);
        }

        if ($this->hasTiers()) {
            foreach ($data as $key => $row) {
                if (is_int($key)) {

                    $where = "id='{$row['id']}'";
                    unset($row['id']);

                    $isRow = $this->_table->fetchRow($where);

                    if (count($isRow) > 0) {
                        $this->_table->update($row, $where);
                    }
                    else {
                        $insert = &$row;
                        $insert['name'] = $feeName;

                        if (isset($type)) {
                            $insert['type'] = $type;
                        }

                        if ($categoryId) {
                            $insert['category_id'] = $categoryId;
                        }

                        $this->_table->insert($insert);
                    }
                }
            }
        }
        else {
            foreach ($data as $key => $value) {
                $where = $this->_table->select()
                    ->where('name = ?', $key)
                    ->where('type = ?', $type);

                if ($categoryId) {
                    $where->where('category_id = ?', $categoryId);
                }
                else {
                    $where->where('category_id IS NULL');
                }

                $row = $this->_table->fetchRow($where);

                $insert = array(
                    'amount'           => $value,
                    'calculation_type' => $calculationType);

                if (count($row) > 0) {
                    $this->_table->update($insert, "id = '{$row['id']}'");
                }
                else {
                    $insert['name'] = $key;

                    if (isset($type)) {
                        $insert['type'] = $type;
                    }

                    if ($categoryId) {
                        $insert['category_id'] = $categoryId;
                    }

                    $this->_table->insert($insert);
                }
            }
        }


        return $this;
    }

    /**
     *
     * get fees data for usage in admin setup forms
     *
     * @param string  $name       the name of the fee
     * @param integer $categoryId id of the category
     * @param string  $type       fee/listing type
     *
     * @return array
     */
    public function getData($name = null, $categoryId = null, $type = null)
    {
        if ($name === null) {
            $name = $this->getFeeName();
        }

        if ($type !== null) {
            $this->setType($type);
        }

        $type = $this->getType();

        $select = $this->_table->select()
            ->where('type = ?', $type);

        if ($name !== null) {
            $select->where("name = ?", $name);
        }

        if ($categoryId) {
            $select->where("category_id = ?", $categoryId);
        }
        else {
            $select->where("category_id IS NULL");
        }

        $data = $this->fetchAll($select)->toArray();


        // if data = 1 then get all non tiered fields plus a 'type' field
        if ($this->hasTiers() !== true) {
            $result['fee_type'] = (isset($data[0]['calculation_type'])) ? $data[0]['calculation_type'] : self::DEFAULT_CALCULATION_TYPE;

            $select = $this->_table->select()
                ->where('type = ?', $type);

            if ($categoryId) {
                $select->where("category_id = ?", $categoryId);
            }
            else {
                $select->where("category_id IS NULL");
            }

            $rows = $this->fetchAll($select);
            foreach ($rows as $row) {
                $result[$row['name']] = $row['amount'];
            }

            $data = $result;
        }

        $data['category_id'] = $categoryId;

        return $data;
    }

    /**
     *
     * formats the post array in a format that the save() method can process
     * used variables: id, amount, type, tier_from, tier_to, id
     *
     * @param array $params
     *
     * @return array the formatted result
     */
    public function preparePostParams($params)
    {
        $result = &$params;

        if (isset($params['tier_from'])) {
            foreach ($params['tier_from'] as $key => $tierFrom) {
                if (!empty($params['amount'][$key])) {
                    array_push($result, array(
                        'id'               => $params['id'][$key],
                        'calculation_type' => $params['calculation_type'][$key],
                        'amount'           => $params['amount'][$key],
                        'tier_from'        => $params['tier_from'][$key],
                        'tier_to'          => $params['tier_to'][$key],
                    ));
                }
            }
        }

        return $result;
    }

    /**
     *
     * delete table data by id - fees tiers only
     *
     * @param array $data
     *
     * @return int          The number of affected rows.
     */
    public function delete(array $data)
    {
        $data = array_filter(
            array_values($data));

        if (count($data) > 0) {
            return $this->_table->delete("id IN (" . implode(',', $data) . ")");
        }

        return null;
    }

    /**
     *
     * add tax to a certain value, and return the rounded value
     *
     * @param float $value
     *
     * @return float
     */
    protected function _addTax($value)
    {
        $value += $value * $this->getTaxType()->getData('amount') / 100;

        return $this->_roundNumber($value);
    }

    /**
     *
     * apply voucher to a certain value and currency and return rounded value
     *
     * @param float  $value
     * @param string $currency
     *
     * @return float
     */
    protected function _applyVoucher($value, $currency)
    {
        if (($voucher = $this->getVoucher()) instanceof Voucher) {
            $value = $voucher->apply($value, $currency);
        }

        return $this->_roundNumber($value);
    }

    /**
     *
     * apply preferred sellers reduction, and return rounded value
     *
     * @param $value
     *
     * @return float
     */
    protected function _applyPreferredSellersReduction($value)
    {
        $settings = $this->getSettings();
        if ($settings['preferred_sellers'] && $this->_user['preferred_seller']) {
            $value = ($value * (1 - $settings['preferred_sellers_reduction'] / 100));
        }

        return $this->_roundNumber($value);
    }

    /**
     *
     * requires an array of data and
     * makes the required modifications to the affected tables
     *
     * functionality defined in child classes
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post data needed by the method
     */
    public function callback($ipn, array $post)
    {

    }


    /**
     *
     * get the signup fee value (tax included)
     *
     * functionality defined in child classes
     *
     * @return float
     */
    public function getTotalAmount()
    {

    }
}

