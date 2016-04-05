<?php

/**
 *
 * PHP Pro Bid $Id$ tEjlngvwvLF7KFwpv3OUuoP3ePAms+STun7lqkZ8qH8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * users table row object model
 */

namespace Ppb\Db\Table\Row;

use Cube\Controller\Request,
    Cube\Db\Expr,
    Cube\Db\Select,
    Cube\Controller\Front,
    Cube\View\Helper\Url as UrlViewHelper,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Service;

class User extends AbstractRow
{

    /**
     * name of user token cookie
     */
    const USER_TOKEN = 'UserToken';

    /**
     * remember me cookie
     */
    const REMEMBER_ME = 'RememberMe';

    /**
     *
     * reputation service
     *
     * @var \Ppb\Service\Reputation
     */
    protected $_reputation;

    /**
     *
     * shipping model
     *
     * @var \Ppb\Model\Shipping
     */
    protected $_shipping;

    /**
     *
     * generate unique user token
     *
     * @return string
     */
    public static function generateToken()
    {
        return uniqid(time(), true);
    }

    /**
     *
     * get reputation service
     *
     * @return \Ppb\Service\Reputation
     */
    public function getReputation()
    {
        if (!$this->_reputation instanceof Service\Reputation) {
            $this->setReputation(
                new Service\Reputation());
        }

        return $this->_reputation;
    }

    /**
     *
     * set reputation service
     *
     * @param \Ppb\Service\Reputation $reputation
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function setReputation(Service\Reputation $reputation)
    {
        $this->_reputation = $reputation;

        return $this;
    }

    /**
     *
     * get shipping model
     *
     * @return \Ppb\Model\Shipping
     */
    public function getShipping()
    {
        if (!$this->_shipping instanceof ShippingModel) {
            $this->setShipping(
                new ShippingModel($this));
        }

        return $this->_shipping;
    }

    /**
     *
     * set shipping model
     *
     * @param \Ppb\Model\Shipping $shipping
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function setShipping(ShippingModel $shipping)
    {
        $this->_shipping = $shipping;

        return $this;
    }

    /**
     *
     * checks if the user accepts public questions
     *
     * @return bool
     */
    public function acceptPublicQuestions()
    {
        $settings = $this->getSettings();
        if ($settings['enable_public_questions'] && $this->getGlobalSettings('enable_public_questions')) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user can list items
     *
     * @return bool
     */
    public function canList()
    {
        $settings = $this->getSettings();

        $privateSite = (isset($settings['private_site'])) ? (bool)$settings['private_site'] : false;
        if (!$privateSite || $this->getData('is_seller')) {
            return true;
        }

        return false;
    }

    /**
     *
     * get user role
     * we have Guest, Incomplete, Suspended, Buyer, Seller, BuyerSeller
     *
     * @return string the role of the user
     */
    public function getRole()
    {
        if (!count($this)) {
            return 'Guest';
        }

        if ($this->getData('approved') && $this->getData('mail_activated') && $this->getData('payment_status') == 'confirmed') {
            if ($this->getData('active')) {
                if ($this->canList()) {
                    return 'BuyerSeller';
                }
                else {
                    return 'Buyer';
                }
            }
            else {
                return 'Suspended';
            }
        }
        else {
            return 'Incomplete';
        }
    }

    /**
     *
     * get store status (true if enabled and items can be listed in it, false otherwise)
     *
     * @param bool $simple if this flag is set, it will not check if items can be listed in the store
     *
     * @return bool      true if user can list in store, false otherwise
     */
    public function storeStatus($simple = false)
    {
        $settings = $this->getSettings();

        if ($settings['enable_stores'] && $this->getData('store_active')) {
            if ($simple === true) {
                return true;
            }

            if ($this->getData('store_subscription_id')) {
                $subscription = $this->findParentRow('\Ppb\Db\Table\StoresSubscriptions');

                if ($this->countStoreListings() < $subscription['listings']) {
                    return true;
                }

                return false;
            }
            else {
                // default store - unlimited items
                return true;
            }
        }

        return false;
    }

    /**
     *
     * get store logo
     *
     * @return string|null
     */
    public function storeLogo()
    {
        $storeSettings = $this->getStoreSettings();
        $logo = null;

        if (!empty($storeSettings['store_logo_path'])) {
            $logo = (is_array($storeSettings['store_logo_path'])) ?
                current(array_filter($storeSettings['store_logo_path'])) : $storeSettings['store_logo_path'];
        }

        return $logo;
    }

    /**
     *
     * returns the user's total number of store listings (open/closed, active/suspended)
     *
     * @return int
     */
    public function countStoreListings()
    {
        $request = new Request();
        $request->clearParams()
            ->setParam('show', 'store')
            ->setParam('user_id', $this->getData('id'));

        $listingsService = new Service\Listings();
        $select = $listingsService->select(Service\Listings::SELECT_LISTINGS, $request);

        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER);

        $select->columns(array('nb_rows' => new Expr('count(*)')));

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * get user payment mode
     *
     * @return string will return the account type ('live', 'account')
     */
    public function userPaymentMode()
    {
        $settings = $this->getSettings();

        if ($settings['user_account_type'] == 'global') {
            return $settings['payment_mode'];
        }

        return $this->getData('account_mode');
    }

    /**
     *
     * update user balance (if in account mode)
     * on the front end only listings from active users are shown
     *
     * @param float $amount positive = debit | negative = credit
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updateBalance($amount)
    {
        $paymentMode = $this->userPaymentMode();

        if ($paymentMode == 'account') {
            $settings = $this->getSettings();

            $balance = $this->getData('balance') + $amount;

            $this->save(array(
                'balance' => $balance,
            ));

            if ($amount > 0 && $balance > $this->getData('max_debit')) {
                $sendEmail = false;
                if ($settings['suspend_over_limit_accounts']) {
                    $this->updateActive(0);
                    $sendEmail = true;
                }
                else if (!$this->getData('debit_exceeded_date')) { // only set this flag if the column is null
                    $sendEmail = true;
                    $this->save(array(
                        'debit_exceeded_date' => new Expr('now()')
                    ));
                }

                if ($sendEmail) {
                    $mail = new \Members\Model\Mail\User();
                    $mail->accountBalanceExceeded($this)->send();
                }

            }
            else if ($amount < 0 && $balance < $this->getData('max_debit')) {
                $this->save(array(
                    'debit_exceeded_date' => new Expr('null') // reset cron suspension flag
                ));
                $this->updateActive(1);
            }
        }

        return $this;
    }

    /**
     *
     * when suspending the user, also suspend his active listings
     * inactive listings will not have their active flag altered
     *
     * @param int $active
     *
     * @return $this
     */
    public function updateActive($active = 1)
    {
        $listingsFlag = ($active) ? -1 : 1;

        $listingsService = new Service\Listings();
        $listingsService->getTable()->update(
            array('active' => (-1) * $listingsFlag),
            "user_id = '" . $this->getData('id') . "' AND active = '{$listingsFlag}'");

        $this->save(array(
            'active' => $active
        ));

        return $this;

    }

    /**
     *
     * update the store settings for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updateStoreSettings(array $data)
    {
        $array = array();

        // if store account id is changed, store_active = 0
        if (!empty($data['store_subscription_id'])) {
            if ($data['store_subscription_id'] != $this->getData('store_subscription_id')) {
                $array['store_active'] = 0;
            }
            $array['store_subscription_id'] = $data['store_subscription_id'];
        }

        if (isset($data['store_name'])) {
            $array['store_name'] = $data['store_name'];
            $array['store_slug'] = $this->_sluggizeStoreName($array['store_name']);
        }

        if (isset($data['store_category_id'])) {
            $array['store_category_id'] = $data['store_category_id'];
        }

        $data = array_merge($this->getStoreSettings(), $data);

        $array['store_settings'] = serialize($data);

        $this->save($array);

        return $this;
    }

    /**
     *
     * update the postage settings for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updatePostageSettings(array $data)
    {
        $this->save(array(
            'postage_settings' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * update the selling prefilled fields for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updatePrefilledFields(array $data)
    {
        $this->save(array(
            'prefilled_fields' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * update the global settings field for the user
     *
     * @param array $data
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function updateGlobalSettings(array $data)
    {
        $data = array_merge($this->getGlobalSettings(), $data);

        $this->save(array(
            'global_settings' => serialize($data)
        ));

        return $this;
    }

    /**
     *
     * update preferred seller status
     *
     * @param int $flag
     *
     * @return $this
     */
    public function updatePreferredSeller($flag)
    {
        $flag = ($flag == 1) ? 1 : 0;

        if ($flag) {
            $settings = $this->getSettings();

            $expirationDate = ($settings['preferred_sellers_expiration'] > 0) ?
                new Expr('(now() + interval ' . intval($settings['preferred_sellers_expiration']) . ' day)') : new Expr('null');
        }
        else {
            $expirationDate = new Expr('now()');
        }
        $this->save(array(
            'preferred_seller'            => $flag,
            'preferred_seller_expiration' => $expirationDate,
        ));

        return $this;
    }

    /**
     *
     * updates the user verification related fields (flag and last/next payment dates)
     *
     * @param int  $flag
     * @param bool $updateLastPayment if set to true and the flag is enabled, will update the last payment field
     * @param null $recurringDays     custom recurring days field. if null, will use the value from the settings table
     * @param null $refundUser
     *
     * @return $this
     */
    public function updateUserVerification($flag, $updateLastPayment = true, $recurringDays = null, $refundUser = null)
    {
        $flag = ($flag == 1) ? 1 : 0;

        $params = array(
            'user_verified' => $flag,
        );

        if ($flag) {
            $settings = $this->getSettings();

            $recurringDays = ($recurringDays !== null) ? $recurringDays : $settings['user_verification_recurring'];
            $nextPayment = ($recurringDays > 0) ?
                new Expr('(greatest(now(), "' . $this->getData('user_verified_next_payment') . '") + interval ' . intval($recurringDays) . ' day)') : new Expr('null');

            $refundUser = ($refundUser !== null) ? $refundUser : $settings['user_verification_refund'];

            if ($updateLastPayment) {
                $params['user_verified_last_payment'] = new Expr('now()');
            }

            if ($refundUser && $this->userPaymentMode() == 'account') {
                $params['balance'] = $this->getData('balance') - $settings['user_verification_fee'];
            }
        }
        else {
            $nextPayment = new Expr('now()');
        }

        $params['user_verified_next_payment'] = $nextPayment;

        $this->save($params);

        return $this;
    }

    /**
     *
     * updates the user's store account and subscription
     *
     * @param int           $flag
     * @param bool|null|int $storeSubscriptionId if false, don't update the subscription id, if null set default account, otherwise set subscription id
     * @param bool          $updateLastPayment
     *
     * @return $this
     */
    public function updateStoreSubscription($flag, $storeSubscriptionId = false, $updateLastPayment = true)
    {
        $flag = ($flag == 1) ? 1 : 0;

        $params = array();
        $params['store_active'] = $flag;

        if ($storeSubscriptionId !== false) {
            $params['store_subscription_id'] = ($storeSubscriptionId === null) ?
                new Expr('null') : (int)$storeSubscriptionId;
        }

        $this->save($params);

        $params = array();

        if ($flag) {
            if ($this->getData('store_subscription_id') > 0) {
                $storeSubscription = $this->findParentRow('\Ppb\Db\Table\StoresSubscriptions');
                $params['store_next_payment'] = ($storeSubscription['recurring_days'] > 0) ?
                    new Expr('(greatest(now(), "' . $this->getData('store_next_payment') . '") + interval ' . intval($storeSubscription['recurring_days']) . ' day)') : new Expr('null');
            }
            else {
                $params['store_next_payment'] = new Expr('null');
            }

            if ($updateLastPayment) {
                $params['store_last_payment'] = new Expr('now()');
            }
        }
        else {
            $params['store_next_payment'] = new Expr('now()');
        }

        $this->save($params);

        return $this;
    }

    /**
     *
     * save the user's settings (admin area) and return any output messages
     *
     * @param array $params
     *
     * @return array
     */
    public function updateSettings(array $params)
    {
        $translate = $this->getTranslate();

        $messages = array();
        $data = array(
            'account_mode' => $params['account_mode'],
        );

        if ($params['user_verified'] != $this->getData('user_verified')) {
            $this->updateUserVerification($params['user_verified'], false, null, false);

            $status = ($params['user_verified'] == 1) ? 'verified' : 'unverified';
            $messages[] = sprintf($translate->_("The account has been %s."), $status);
        }

        if ($params['account_mode'] == 'account') {
            $data = array_merge($data, array(
                'balance'   => $params['balance'],
                'max_debit' => $params['max_debit'],
            ));
            if ($params['balance'] != $this->getData('balance')) {
                $view = Front::getInstance()->getBootstrap()->getResource('view');

                $messages[] = sprintf($translate->_("The user's balance has been changed to: %s %s"),
                    $view->amount(abs($params['balance'])), (($params['balance'] > 0) ? 'debit' : 'credit'));

                $settings = $this->getSettings();

                $amount = $params['balance'] - $this->getData('balance');

                $name = sprintf($translate->_('Admin Balance Adjustment - User ID: #%s'), $this->getData('id'));

                if ($params['balance_adjustment_reason']) {
                    $name .= $translate->_(' - Comment: ') . $params['balance_adjustment_reason'];
                }

                // save balance adjustment process in the accounting table
                $accountingService = new Service\Accounting();
                $accountingService->save(array(
                    'name'     => $name,
                    'amount'   => $amount,
                    'user_id'  => $this->getData('id'),
                    'currency' => $settings['currency'],
                ));
            }
        }

        if (array_key_exists('is_seller', $params)) {
            if ($params['is_seller'] != $this->getData('is_seller')) {
                $data['is_seller'] = $params['is_seller'];

                $status = ($params['is_seller'] == 1) ? $translate->_('enabled') : $translate->_('disabled');
                $messages[] = sprintf($translate->_("The user's listing capabilities have been %s."), $status);
            }
        }

        if (array_key_exists('preferred_seller', $params)) {
            if ($params['preferred_seller'] != $this->getData('preferred_seller')) {
                $this->updatePreferredSeller($params['preferred_seller']);

                $status = ($params['preferred_seller'] == 1) ? $translate->_('enabled') : $translate->_('disabled');
                $messages[] = sprintf($translate->_("The preferred seller status has been %s."), $status);
            }
        }

        if (array_key_exists('store_active', $params)) {
            if ($params['store_active'] != $this->getData('store_active')) {
                $data['store_active'] = $params['store_active'];
                $status = ($params['store_active'] == 1) ? $translate->_('enabled') : $translate->_('disabled');
                $messages[] = sprintf($translate->_("The store has been %s."), $status);
            }
        }

        if (array_key_exists('assign_default_store_account', $params)) {
            if ($params['assign_default_store_account']) {
                $data = array_merge($data,
                    array('store_active'          => 1,
                          'store_subscription_id' => new Expr('null'),
                          'store_next_payment'    => new Expr('null'),
                    ));

                $messages[] = $translate->_("The default store account has been set.");
            }
        }

        if (count($data) > 0) {
            $this->save($data);
        }

        return $messages;
    }

    /**
     *
     * get the reputation score of the user
     * proxy for the \Ppb\Service\Reputation::getScore() method
     *
     * @return integer
     */
    public function getReputationScore()
    {
        $reputationData = \Ppb\Utility::unserialize($this->getData('reputation_data'));

        if (isset($reputationData['score'])) {
            return $reputationData['score'];
        }

        return $this->getReputation()->getScore($this->getData('id'));
    }

    /**
     *
     * get the positive reputation percentage of the user
     * proxy for the \Ppb\Service\Reputation::getPercentage() method
     *
     * @return string
     */
    public function getReputationPercentage()
    {
        $reputationData = \Ppb\Utility::unserialize($this->getData('reputation_data'));

        if (isset($reputationData['percentage'])) {
            return $reputationData['percentage'];
        }

        return $this->getReputation()->getPercentage($this->getData('id'));
    }

    /**
     *
     * get user postage settings
     *
     * @return array
     */
    public function getPostageSettings()
    {
        $postageSettings = \Ppb\Utility::unserialize($this->getData('postage_settings'), array());

        return $postageSettings;
    }

    /**
     *
     * get user store settings
     *
     * @param string|null $key
     *
     * @return array|string|null
     */
    public function getStoreSettings($key = null)
    {
        $storeSettings = \Ppb\Utility::unserialize($this->getData('store_settings'), array());

        $storeSettings['store_subscription_id'] = $this->getData('store_subscription_id');

        if ($key !== null) {
            return isset($storeSettings[$key]) ? $storeSettings[$key] : null;
        }

        return $storeSettings;
    }

    /**
     *
     * get listing setup prefilled fields
     *
     * @return array|null
     */
    public function getPrefilledFields()
    {
        $prefilledFields = \Ppb\Utility::unserialize($this->getData('prefilled_fields'), null);

        return array_merge(array(
            'country' => $this->getData('country'),
            'state'   => $this->getData('state'),
            'address' => $this->getData('zip_code'),
        ), (array)$prefilledFields);
    }


    /**
     *
     * get user global settings
     *
     * @param string|null $key
     *
     * @return array|string|null
     */
    public function getGlobalSettings($key = null)
    {
        $globalSettings = \Ppb\Utility::unserialize($this->getData('global_settings'), array());

        if ($key !== null) {
            return isset($globalSettings[$key]) ? $globalSettings[$key] : null;
        }

        return $globalSettings;
    }

    /**
     *
     * calculate the reputation score of a user based on different input variables
     * proxy for the \Ppb\Service\Reputation::calculateScore() method
     *
     * @param float  $score          score threshold
     * @param string $operand        calculation operand
     * @param string $reputationType reputation type to be calculated (sale, purchase)
     * @param string $interval       calculation interval
     *
     * @return int                      resulted score
     */
    public function calculateReputationScore($score = null, $operand = '=', $reputationType = null, $interval = null)
    {
        return intval($this->getReputation()
            ->calculateScore($this->getData('id'), $score, $operand, $reputationType, $interval));
    }

    /**
     *
     * check if the user can pay the signup fee
     *
     * @return bool
     */
    public function canPaySignupFee()
    {
        if ($this->getData('payment_status') != 'confirmed') {
            return true;
        }

        return false;
    }

    /**
     *
     * check if the user can apply tax to his listings
     * return the tax type id if tax can be applied
     * seller will choose which tax to apply from the global settings page
     *
     * @return int|false
     */
    public function canApplyTax()
    {
        $settings = $this->getSettings();

        if ($settings['enable_tax_listings']) {
            return ($this->getGlobalSettings('enable_tax')) ? $this->getGlobalSettings('tax_type') : false;
        }

        return false;
    }

    /**
     *
     * check if the user is a verified user
     * - used on the purchase controller for buyer verification
     * - used on the listing controller (add/edit actions) for the seller verification
     *
     * @param bool $default
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function isVerified($default = true)
    {
        $settings = $this->getSettings();

        if ($settings['user_verification']) {
            if ($this->getData('user_verified')) {
                return true;
            }

            return false;
        }

        return (bool)$default;
    }

    /**
     *
     * check if the user has enabled force payment for his products
     *
     * @return bool
     */
    public function isForcePayment()
    {
        $settings = $this->getSettings();
        if ($settings['enable_products'] && $settings['enable_force_payment']) {
            if ($this->getGlobalSettings('enable_force_payment')) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if the user is an administrator
     *
     * @return bool
     */
    public function isAdmin()
    {
        $roles = array_keys(Service\Users::getAdminRoles());
        if (in_array($this->getData('role'), $roles)) {
            return true;
        }

        return false;
    }

    /**
     *
     * returns an array used by the url view helper to generate the store url
     * if the store is disabled, return the main page url
     *
     * @return array
     */
    public function storeLink()
    {
        if ($this->storeStatus(true)) {
            $slug = $this->getData('store_slug');

            if ($slug) {
                return array(
                    'module'     => 'listings',
                    'controller' => 'browse',
                    'action'     => 'index',
                    'show'       => 'store',
                    'store_slug' => $slug,
                );
            }
            else {
                return array(
                    'module'     => 'listings',
                    'controller' => 'browse',
                    'action'     => 'index',
                    'show'       => 'store',
                    'name'       => $this->getData('store_name'),
                    'user_id'    => $this->getData('id'),
                );
            }
        }
        else {
            return array(
                'module'     => 'app',
                'controller' => 'index',
                'action'     => 'index',
            );
        }
    }

    /**
     *
     * user's store name
     *
     * @return string
     */
    public function storeName()
    {
        return ($this->getData('store_name')) ? $this->getData('store_name') : $this->getData('username');
    }

    /**
     *
     * returns an array used by the url view helper to generate the user's other items url
     *
     * @return array
     */
    public function otherItemsLink()
    {
        return array(
            'module'     => 'listings',
            'controller' => 'browse',
            'action'     => 'index',
            'show'       => 'other-items',
            'username'   => $this->getData('username'),
            'user_id'    => $this->getData('id'),
        );
    }

    /**
     * returns an array used by the url view helper to generate the user's feedback details url
     *
     * @return array
     */
    public function reputationLink()
    {
        $username = $this->getData('username');

        return array(
            'module'     => 'members',
            'controller' => 'reputation',
            'action'     => 'details',
            'username'   => (stristr($username, ' ')) ? $this->getData('id') : $username,
        );
    }

    /**
     *
     * set the active address for the user
     *
     * @param int|null|\Ppb\Db\Table\Row\UserAddressBook $address
     *
     * @return $this
     */
    public function setAddress($address = null)
    {
        if (!$address instanceof UserAddressBook) {
            $address = $this->getAddress(intval($address));
        }

        if (count($address) > 0) {
            foreach ($address as $key => $value) {
                if ($key == 'id') {
                    $key = 'address_id';
                }
                $this->addData($key, $value);
            }
        }

        return $this;
    }

    /**
     *
     * get a user's address from the address book table
     *
     * @param int|null $id the id of the address or null if we are looking for the primary address
     *
     * @return \Ppb\Db\Table\Row\UserAddressBook|null   will return an address book row object or null if the user has no address saved
     */
    public function getAddress($id = null)
    {
        $select = $this->getTable()->select();
        if (!$id) {
            $select->where('is_primary = ?', 1);
        }
        else {
            $select->where('id = ?', $id);
        }

        $rowset = $this->findDependentRowset('\Ppb\Db\Table\UsersAddressBook', null, $select);

        $result = $rowset->getRow(0);

//        if ($result === null) {
//            $addressBookService = new Service\UsersAddressBook();
//
//            $result = new UserAddressBook(array(
//                'table' => $addressBookService->getTable(),
//                'data'  => array_map(function () {
//                }, array_flip($addressBookService->getAddressFields()))
//            ));
//        }

        return $result;
    }

    /**
     *
     * check whether to display make offer ranges
     *
     * @return bool
     */
    public function displayMakeOfferRanges()
    {
        $settings = $this->getSettings();
        if ($settings['show_make_offer_ranges'] && $this->getGlobalSettings('show_make_offer_ranges')) {
            return true;
        }

        return false;
    }

    /**
     *
     * check if a user has added this store owner as favorite
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isFavoriteStore($userId)
    {
        return (count($this->_getFavoriteStores($userId))) ? true : false;
    }

    /**
     *
     * add/remove this user store to favorites for a certain user
     *
     * @param int $userId
     *
     * @return $this
     */
    public function processFavoriteStore($userId)
    {
        if ($this->isFavoriteStore($userId)) {
            $this->_getFavoriteStores($userId)->delete();
        }
        else {
            $favoriteStoresService = new Service\FavoriteStores();
            $favoriteStoresService->save(array(
                'user_id'  => $userId,
                'store_id' => $this->getData('id'),
            ));
        }

        return $this;
    }

    /**
     *
     * checks if the user is in vacation
     *
     * @return bool
     */
    public function isVacation()
    {
        if ($this->getGlobalSettings('vacation_mode')) {
            $returnDate = $this->getGlobalSettings('vacation_mode_return_date');
            if (empty($returnDate) || (strtotime($returnDate) > time())) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * check if the user has selected to receive seller specific email notifications
     *
     * @return bool
     */
    public function emailSellerNotifications()
    {
        return (
            $this->getGlobalSettings('disable_emails') ||
            $this->getGlobalSettings('disable_seller_notifications')
        ) ? false : true;
    }

    /**
     *
     * check if the user has selected to receive offers module specific email notifications
     *
     * @return bool
     */
    public function emailOffersNotifications()
    {
        return (
            $this->getGlobalSettings('disable_emails') ||
            $this->getGlobalSettings('disable_offers_notifications')
        ) ? false : true;
    }

    /**
     *
     * check if the user has selected to receive messaging module specific email notifications
     *
     * @return bool
     */
    public function emailMessagingNotifications()
    {
        return (
            $this->getGlobalSettings('disable_emails') ||
            $this->getGlobalSettings('disable_messaging_notifications')
        ) ? false : true;
    }


    /**
     *
     * get favorite stores rowset for a certain user
     *
     * @param int $userId
     *
     * @return \Cube\Db\Table\Rowset
     */
    protected function _getFavoriteStores($userId = null)
    {
        $select = null;

        if ($userId !== null) {
            $select = $this->getTable()->select()
                ->where('user_id = ?', $userId);
        }

        return $this->findDependentRowset('\Ppb\Db\Table\FavoriteStores', 'Store', $select);
    }

    /**
     *
     * return sluggized store name value
     * uses the cleanString method from the Url view helper
     *
     * @param string $storeName
     *
     * @return string
     */
    protected function _sluggizeStoreName($storeName)
    {
        $usersService = new Service\Users();

        $duplicate = true;
        do {
            $storeSlug = UrlViewHelper::cleanString($storeName);

            $rowset = $usersService->fetchAll(
                $usersService->getTable()->select()
                    ->where('store_slug = ?', $storeSlug)
                    ->where('id != ?', $this->getData('id'))
            );

            if (count($rowset) > 0) {
                $storeName .= '1';
            }
            else {
                $duplicate = false;
            }
        } while ($duplicate === true);

        return $storeSlug;
    }
}

