<?php

/**
 *
 * PHP Pro Bid $Id$ QabOCfjVHe+VhQSDgpcp2qkAwwIDbgSQrpB4VqNLibE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * users table service class
 */

namespace Ppb\Service;

use Ppb\Db\Table\Users as UsersTable,
    Ppb\Service,
    Ppb\Model\Shipping as ShippingModel,
    Ppb\Db\Table\Row\User as UserModel,
    Cube\Db\Expr;

class Users extends AbstractService
{

    /**
     * admin user roles
     */
    const ADMIN_ROLE_PRIMARY = 'Admin';
    const ADMIN_ROLE_MANAGER = 'Manager';

    protected static $_adminRoles = array(
        self::ADMIN_ROLE_PRIMARY => 'Administrator',
        self::ADMIN_ROLE_MANAGER => 'Manager',
    );

    /**
     * custom fields tables "type" column
     */
    const CUSTOM_FIELDS_TYPE = 'user';

    /**
     *
     * custom fields data table service
     *
     * @var \Ppb\Service\CustomFieldsData
     */
    protected $_customFieldsData;

    /**
     *
     * payment gateways service
     *
     * @var \Ppb\Service\Table\PaymentGateways
     */
    protected $_paymentGateways;

    /**
     *
     * users address book table service
     *
     * @var \Ppb\Service\UsersAddressBook
     */
    protected $_usersAddressBook;


    /**
     *
     * user subscriptions that are used when:
     * - disabling expired subscriptions,
     * - re-billing automatically in account mode
     * - notifying users by email that subscriptions are about to expire
     *
     * @var array
     */
    protected $_subscriptionTypes = array(
        'UserVerification'  => array(
            'name'           => 'User Verification Subscription',
            'active'         => 'user_verified',
            'expirationDate' => 'user_verified_next_payment',
            'emailFlag'      => 'user_verified_email',
            'updateMethod'   => 'updateUserVerification',
            'feesService'    => '\Ppb\Service\Fees\UserVerification',
            'renewalLink'    => array('module' => 'app', 'controller' => 'payment', 'action' => 'user-verification'),
            'managementLink' => array('module' => 'members', 'controller' => 'user', 'action' => 'verification'),
        ),
        'StoreSubscription' => array(
            'name'           => 'Store Subscription',
            'active'         => 'store_active',
            'expirationDate' => 'store_next_payment',
            'emailFlag'      => 'store_expiration_email',
            'updateMethod'   => 'updateStoreSubscription',
            'feesService'    => '\Ppb\Service\Fees\StoreSubscription',
            'renewalLink'    => array('module' => 'app', 'controller' => 'payment', 'action' => 'store-subscription'),
            'managementLink' => array('module' => 'members', 'controller' => 'store', 'action' => 'setup'),
        ),
    );

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new UsersTable());
    }

    /**
     *
     * set custom fields data service
     *
     * @param \Ppb\Service\CustomFieldsData $customFieldsData
     *
     * @return \Ppb\Service\Users
     */
    public function setCustomFieldsDataService(Service\CustomFieldsData $customFieldsData)
    {
        $this->_customFieldsData = $customFieldsData;

        return $this;
    }

    /**
     *
     * get custom fields data service
     *
     * @return \Ppb\Service\CustomFieldsData
     */
    public function getCustomFieldsDataService()
    {
        if (!$this->_customFieldsData instanceof Service\CustomFieldsData) {
            $this->setCustomFieldsDataService(
                new Service\CustomFieldsData());
        }

        return $this->_customFieldsData;
    }


    /**
     *
     * set payment gateways service
     *
     * @param \Ppb\Service\Table\PaymentGateways $paymentGateways
     *
     * @return \Ppb\Service\Users
     */
    public function setPaymentGateways(Service\Table\PaymentGateways $paymentGateways)
    {
        $this->_paymentGateways = $paymentGateways;

        return $this;
    }

    /**
     *
     * get payment gateways service
     *
     * @return \Ppb\Service\Table\PaymentGateways
     */
    public function getPaymentGateways()
    {
        if (!$this->_paymentGateways instanceof Service\Table\PaymentGateways) {
            $this->setPaymentGateways(
                new Service\Table\PaymentGateways());
        }

        return $this->_paymentGateways;
    }

    /**
     *
     * set users address book service
     *
     * @param \Ppb\Service\UsersAddressBook $addressBook
     *
     * @return \Ppb\Service\Users
     */
    public function setUsersAddressBook(Service\UsersAddressBook $addressBook)
    {
        $this->_usersAddressBook = $addressBook;

        return $this;
    }

    /**
     *
     * get users address book service
     *
     * @return \Ppb\Service\UsersAddressBook
     */
    public function getUsersAddressBook()
    {
        if (!$this->_usersAddressBook instanceof Service\UsersAddressBook) {
            $this->setUsersAddressBook(
                new Service\UsersAddressBook());
        }

        return $this->_usersAddressBook;
    }

    /**
     *
     * set subscription types array
     *
     * @param array $subscriptionTypes
     *
     * @return $this
     */
    public function setSubscriptionTypes($subscriptionTypes)
    {
        $this->_subscriptionTypes = $subscriptionTypes;

        return $this;
    }

    /**
     *
     * get subscription types array
     *
     * @return array
     */
    public function getSubscriptionTypes()
    {
        return $this->_subscriptionTypes;
    }

    /**
     *
     * set admin roles
     *
     * @param $adminRoles
     */
    public static function setAdminRoles($adminRoles)
    {
        self::$_adminRoles = $adminRoles;
    }

    /**
     *
     * get admin roles
     *
     * @return array
     */
    public static function getAdminRoles()
    {
        return self::$_adminRoles;
    }

    /**
     *
     * find a row on the table by querying a certain column
     * also get the primary address from the UsersAddressBook table
     *
     * @param string $name     column name
     * @param string $value    column value
     * @param bool   $enhanced if set to true, it will retrieve all additional related data as an array (including the primary address)
     *
     * @return \Ppb\Db\Table\Row\User
     */
    public function findBy($name, $value, $enhanced = false)
    {
        /** @var \Ppb\Db\Table\Row\User $user */
        $user = parent::findBy($name, $value);

        if (count($user) > 0) {
            $user->setAddress();

            if ($enhanced === true) {
                // custom fields data
                $customFieldsData = $this->getCustomFieldsData($user['id']);
                foreach ($customFieldsData as $key => $value) {
                    $user['custom_field_' . $key] = $value;
                }
            }
        }

        return $user;
    }

    /**
     *
     * save users data in the users table
     * also create the postage settings default array (type = item and shipping locations = domestic)
     *
     * @param array $post
     * @param int   $userId used for when editing a user
     *
     * @return int              the id of the user that was saved
     */
    public function save($post, $userId = null)
    {
        $user = null;

        $data = $this->_prepareSaveData($post);

        if ($userId !== null) {
            $user = $this->findBy('id', $userId);
        }
        else if (array_key_exists('username', $post)) {
            $user = $this->findBy('username', $post['username']);
        }

        if (isset($post['name'])) {
            $post['first_name'] = (!empty($post['name']['first'])) ? $post['name']['first'] : '';
            $post['last_name'] = (!empty($post['name']['last'])) ? $post['name']['last'] : '';
        }

        if (count($user) > 0) {
            $data['updated_at'] = new Expr('now()');

            unset($data['username']);

            $this->_table->update($data, "id='{$user['id']}'");
            $id = $user['id'];
        }
        else {
            $data['created_at'] = new Expr('now()');

            if (!isset($data['postage_settings'])) {
                $data['postage_settings'] = serialize(array(
                    ShippingModel::SETUP_SHIPPING_LOCATIONS => ShippingModel::POSTAGE_LOCATION_DOMESTIC,
                    ShippingModel::SETUP_POSTAGE_TYPE       => ShippingModel::POSTAGE_TYPE_ITEM,
                    ShippingModel::SETUP_FREE_POSTAGE       => 0,
                ));
            }

            $settings = $this->getSettings();
            $data['balance'] = (-1) * $settings['signup_credit'];
            $data['max_debit'] = doubleval($settings['maximum_debit']);
            $data['account_mode'] = $settings['payment_mode'];

            $this->_table->insert($data);
            $id = $this->_table->getAdapter()->lastInsertId();

            $user = $this->findBy('id', $id);
        }

        if (!empty($post['password'])) {
            $this->savePassword($user, $post['password']);
        }

        if (!isset($post['partial'])) {
            // save custom fields data in the database
            foreach ($post as $key => $value) {
                if (strstr($key, 'custom_field_')) {
                    $fieldId = str_replace('custom_field_', '', $key);
                    $this->getCustomFieldsDataService()->save(
                        $value, self::CUSTOM_FIELDS_TYPE, $fieldId, $id);
                }
            }

            // save payment gateways data in the database
            $gatewayFields = $this->getPaymentGateways()->getDirectPaymentFields();
            foreach ($gatewayFields as $key => $gatewayField) {
                $gatewayFields[$key]['user_id'] = $id;
                if (array_key_exists($gatewayField['name'], $post)) {
                    $gatewayFields[$key]['value'] = $post[$gatewayField['name']];
                }
            }

            foreach ((array)$gatewayFields as $gatewayField) {
                $this->getPaymentGateways()->getPaymentGatewaysSettings()->save($gatewayField);
            }

            // save the user's address in the address book (but only if the address form - at least one field from it - is present)
            if (array_intersect($this->getUsersAddressBook()->getAddressFields(), array_keys($post))) {
                $this->getUsersAddressBook()->save($post, $id);
            }
        }

        return $id;
    }

    /**
     *
     * hash a password
     *
     * @param string $password
     * @param string $salt
     *
     * @return string   hashed password
     */
    public function hashPassword($password, $salt)
    {
        return hash('sha256', $password . $salt);
    }

    /**
     *
     * save a password for a certain user in the users table
     *
     * @param \Ppb\Db\Table\Row\User $user
     * @param string                 $password the raw password
     *
     * @return $this
     */
    public function savePassword(UserModel $user, $password)
    {
        $salt = date('U', time());
        $password = $this->hashPassword($password, $salt);

        $user->save(array(
            'password' => $password,
            'salt'     => $salt,
        ));

        return $this;
    }

    /**
     *
     * delete a user from the table
     *
     * @param integer $userId the id of the user
     *
     * @return integer      the number of affected rows
     */
    public function delete($userId)
    {
        $where = $this->_table->getAdapter()->quoteInto('id = ?', $userId);

        return $this->_table->delete($where);
    }

    /**
     *
     * generate a unique registration key used for verifying the user's email address
     *
     * @param integer $id
     * @param string  $username
     *
     * @return string
     */
    public function generateRegistrationKey($id, $username)
    {
        $hash = md5(uniqid(time()));

        return substr(
            hash('sha256', $id . $username . $hash), 0, 10);
    }

    /**
     *
     * verify an email address and return true if successful, false otherwise
     *
     * @param string $key the key used to verify the account
     *
     * @return bool
     */
    public function verifyEmailAddress($key)
    {
        return (bool)$this->_table->update(array('mail_activated' => 1), "registration_key='{$key}'");
    }

    /**
     *
     * unsubscribe user from newsletter
     *
     * @param string $username
     * @param string $email
     *
     * @return bool
     */
    public function newsletterUnsubscribe($username, $email)
    {
        return (bool)$this->_table->update(array('newsletter_subscription' => 0), "username='{$username}' AND email='{$email}'");
    }

    /**
     *
     * get the custom fields data of a certain user
     *
     * @param integer $id
     *
     * @return array
     */
    public function getCustomFieldsData($id)
    {
        $result = array();

        // custom fields data
        $rowset = $this->getCustomFieldsDataService()->fetchAll(
            $this->getCustomFieldsDataService()->getTable()->select('value, field_id')
                ->where('type = ?', self::CUSTOM_FIELDS_TYPE)
                ->where('owner_id = ?', (int)$id));

        foreach ($rowset as $row) {
            $result[$row['field_id']] = \Ppb\Utility::unserialize($row['value']);
        }

        return $result;
    }

    /**
     *
     * prepare user data for when saving to the table
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareSaveData($data = array())
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        if (isset($data['password'])) {
            unset($data['password']);
        }

        if (isset($data['salt'])) {
            unset($data['salt']);
        }

        return parent::_prepareSaveData($data);
    }

}

