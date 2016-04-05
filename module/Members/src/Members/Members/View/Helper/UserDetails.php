<?php

/**
 *
 * PHP Pro Bid $Id$ dgQ1gkb4slAB331ZHOZq1N/ak7iWBHnPiYPQCCEwHGY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * user details view helper class
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\User,
    Ppb\Db\Table\Row\UserAddressBook,
    Ppb\Service;

class UserDetails extends AbstractHelper
{

    const STORE_DESC_MAX_CHARS = 255;
    /**
     *
     * user model
     *
     * @var \Ppb\Db\Table\Row\User
     */
    protected $_user;

    /**
     *
     * settings array
     *
     * @var array
     */
    protected $_settings;

    /**
     *
     * admin roles
     *
     * @var array
     */
    protected $_adminRoles;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\User $user
     *
     * @return \Members\View\Helper\UserDetails
     */
    public function userDetails($user = null)
    {
        if ($user !== null) {
            $this->setUser($user);
        }

        return $this;
    }

    public function getUser()
    {
        if (!$this->_user instanceof User) {
            throw new \InvalidArgumentException("The user model has not been instantiated");
        }

        return $this->_user;
    }

    /**
     *
     * get & initialize settings array
     *
     * @return array
     */
    public function getSettings()
    {
        if (empty($this->_settings)) {
            $this->_settings = $this->getView()->get('settings');
        }

        return $this->_settings;
    }

    /**
     *
     * get admin roles
     *
     * @return array
     */
    public function getAdminRoles()
    {
        if (empty($this->_adminRoles)) {
            $this->_adminRoles = array_keys(Service\Users::getAdminRoles());
        }

        return $this->_adminRoles;
    }


    /**
     *
     * set user data
     *
     * @param int|string|\Ppb\Db\Table\Row\User $user
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setUser($user)
    {
        if (is_int($user) || is_string($user)) {
            $userService = new Service\Users();
            $user = $userService->findBy('id', $user);
        }

//        if (!$user instanceof User) {
//            throw new \InvalidArgumentException("The method requires a string, an integer or an object of type \Ppb\Db\Table\Row\User.");
//        }

        $this->_user = $user;

        $this->setAddress();

        return $this;
    }

    /**
     *
     * set an address from the user's address book, or the primary address if id = null
     *
     * @param int|null|\Ppb\Db\Table\Row\UserAddressBook $address the address (id, object or null for primary address)
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setAddress($address = null)
    {
        $user = null;

        try {
            $user = $this->getUser();
        } catch (\Exception $e) {

        }

        if ($user instanceof User) {
            if (!$address instanceof UserAddressBook) {
                $address = $user->getAddress($address);
            }

            if ($address) {
                foreach ($address as $key => $value) {
                    if ($key != 'id') {
                        $this->_user[$key] = $value;
                    }
                }
            }
        }

        return $this;
    }


    /**
     *
     * display username
     *
     * @param bool $private  if set to true, we have a private auction related display
     * @param bool $enhanced if set to true, display all user related icons TODO
     *
     * @return string
     */
    public function display($private = false, $enhanced = false)
    {
        $output = array();
        $reputationIcon = null;

        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            $translate = $this->getTranslate();

            return '<em>' . $translate->_('Account Deleted') . '</em>';
        }


        $username = $user->getData('username');

        if ($private) {

            $username = substr($username, 0, 1) . '****' . substr($username, -1);
        }


        if (in_array($user->getData('role'), $this->getAdminRoles())) {
            return '<span class="label label-primary">' . $username . '</span>';
        }
        else {
            $translate = $this->getTranslate();
            $settings = $this->getSettings();

            $output[] = $username;

            if ($user->isVerified(false)) {
                $output[] = '<small><i class="fa fa-check-square text-success" title="' . $translate->_('Verified User') . '"></i></small>';
            }

            if ($settings['enable_reputation']) {
                $reputationScore = $user->getReputationScore();

                $reputationLink = $this->getView()->url($user->reputationLink());

                foreach (Service\Reputation::$icons as $key => $value) {
                    if ($reputationScore >= $key) {
                        $reputationIcon = ' ' . $value;
                    }
                }

                $output[] = '<small>('
                    . ((!$private) ? '<a href="' . $reputationLink . '">' : '')
                    . $reputationScore . $reputationIcon
                    . ((!$private) ? '</a>' : '')
                    . ')</small>';
            }

            return implode(' ', $output);
        }
    }

    /**
     *
     * display user location
     *
     * @param bool         $detailed display state as well
     * @param string|false $implode  if false return array, otherwise return the imploded array
     *
     * @return string
     */
    public function location($detailed = true, $implode = ', ')
    {
        $location = array(
            'state'   => null,
            'country' => null,
        );

        $user = $this->getUser();

        $translate = $this->getTranslate();

        $country = $this->_getLocationName($user->getData('country'));
        if ($country !== null) {
            $location['country'] = $translate->_($country);
        }

        if ($detailed === true) {
            $state = $this->_getLocationName($user->getData('state'));
            if ($state !== null) {
                $location['state'] = $translate->_($state);
            }
        }

        if ($implode === false) {
            return $location;
        }

        return (($output = implode(', ', array_reverse($location))) != '') ? $output : null;
    }

    /**
     *
     * display user account status
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function status()
    {
        $output = array();

        $user = $this->getUser();

        $translate = $this->getTranslate();

        if (
            isset($user['approved']) &&
            isset($user['mail_activated']) &&
            isset($user['preferred_seller']) &&
            isset($user['active']) &&
            isset($user['payment_status'])
        ) {
            $settings = $this->getSettings();

            if ($user['payment_status'] != 'confirmed') {
                $output[] = '<span class="label label-warning">' . $translate->_('Signup Fee Not Paid') . '</span>';
            }

            if (!$user['mail_activated']) {
                $output[] = '<span class="label label-default">' . $translate->_('Email Not Verified') . '</span>';
            }

            if (!$user['approved']) {
                $output[] = '<span class="label label-danger">' . $translate->_('Unapproved') . '</span>';
            }
            else if (!$user['active']) {
                $output[] = '<span class="label label-warning">' . $translate->_('Suspended') . '</span>';
            }
            else {
                $output[] = '<span class="label label-success">' . $translate->_('Active') . '</span>';
            }

            if (!in_array($user['role'], $this->getAdminRoles())) {
                if ($settings['private_site']) {
                    if ($user['is_seller']) {
                        $output[] = '<span class="label label-seller">' . $translate->_('Seller') . '</span>';
                    }
                    else {
                        $output[] = '<span class="label label-buyer">' . $translate->_('Buyer') . '</span>';
                    }
                }

                if ($user->userPaymentMode() == 'live') {
                    $output[] = '<span class="label label-live-mode">' . $translate->_('Live Payment') . '</span>';
                }
                else if ($user->userPaymentMode() == 'account') {
                    $output[] = '<span class="label label-account-mode">' . $translate->_('Account Mode') . '</span>';

                    $balance = $this->getView()->amount(abs($user['balance']), null, null, true);
                    if ($user['balance'] > 0) {
                        $output[] = '<span class="label label-danger">' . $balance . ' ' . $translate->_('Debit') . '</span>';
                    }
                    else {
                        $output[] = '<span class="label label-success">' . $balance . ' ' . $translate->_('Credit') . '</span>';
                    }
                }


                if ($user['store_active']) {
                    if ($user['store_subscription_id']) {
                        $subscription = $user->findParentRow('\Ppb\Db\Table\StoresSubscriptions');
                        $storeAccount = ($subscription) ? $translate->_($subscription->getData('name')) : $translate->_('Unknown');
                    }
                    else {
                        $storeAccount = $translate->_('Default');
                    }

                    $description = $storeAccount . ' ' . $translate->_('Store');
                    if ($user['store_next_payment'] > 0) {
                        $description .= ' ' . $translate->_('until') . ' ' . $this->getView()->date($user['store_next_payment'],
                                true);
                    }
                    $output[] = '<span class="label label-store-info">' . $description . ' </span>';

                }

                if ($user['user_verified']) {
                    $description = $translate->_('Verified User');
                    if ($user['user_verified_next_payment'] > 0) {
                        $description .= ' ' . $translate->_('until') . ' ' . $this->getView()->date($user['user_verified_next_payment'],
                                true);
                    }
                    $output[] = '<span class="label label-verified">' . $description . '</span>';
                }

                if ($user['preferred_seller']) {
                    $description = $translate->_('Preferred Seller');
                    if ($user['preferred_seller_expiration'] > 0) {
                        $description .= ' ' . $translate->_('until') . ' ' . $this->getView()->date($user['preferred_seller_expiration'],
                                true);
                    }
                    $output[] = '<span class="label label-preferred">' . $description . '</span>';
                }
            }
            else {
                $output[] = '<span class="label label-primary">' . $user['role'] . '</span>';
            }

            return implode(' ', $output);
        }
        else {
            throw new \InvalidArgumentException("The user object must include values for
                'preferred_seller', 'approved', 'mail_activated' and 'active' keys.");
        }
    }

    /**
     *
     * display the full name of the user
     *
     * @return string
     */
    public function displayFullName()
    {
        $user = $this->getUser();

        $fullName = trim($user['name']['first'] . ' ' . $user['name']['last']);

        return (!empty($fullName)) ? $fullName : $user['username'];
    }

    /**
     *
     * display the user's address
     *
     * @param string $separator
     *
     * @return string
     */
    public function displayAddress($separator = '<br>')
    {
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            $translate = $this->getTranslate();

            return '<em>' . $translate->_('Account Deleted') . '</em>';
        }

        $location = $this->location(true, false);

        $settings = $this->getSettings();

        $address = array();

        $address[] = $user->getData('address');
        $address[] = $user->getData('city');

        if ($settings['address_display_format'] == 'default') {
            $address[] = $user->getData('zip_code');
            $address[] = $location['state'];
        }
        else {
            $address[] = $location['state'];
            $address[] = $user->getData('zip_code');
        }

        $address[] = $location['country'];

        return implode($separator, array_filter($address));
    }

    /**
     *
     * display the user's full address (includes full name and phone, used for invoices etc)
     *
     * @param string $separator
     *
     * @return string
     */
    public function displayFullAddress($separator = '<br>')
    {
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            $translate = $this->getTranslate();

            return '<em>' . $translate->_('Account Deleted') . '</em>';
        }

        $settings = $this->getSettings();

        $translate = $this->getTranslate();

        return '<address>'
        . '<strong>' . $this->displayFullName() . '</strong>'
        . $separator
        . $this->displayAddress($separator)
        . $separator
        . ($settings['sale_phone_numbers'] && (!empty($user['phone'])) ? '<abbr title="' . $translate->_('Phone') . '">#:</abbr> ' . $user['phone'] : '')
        . '</address>';
    }

    /**
     *
     * get the user's store description and display either in full html format, or in short format,
     * with the html stripped out
     *
     * @param bool $full
     *
     * @return string
     */
    public function storeDescription($full = true)
    {
        $storeSettings = $this->getUser()->getStoreSettings();
        $output = $this->getView()->renderHtml(
            (!empty($storeSettings['store_description'])) ? $storeSettings['store_description'] : null);

        if ($full !== true) {
            $output = strip_tags($output);
            if (strlen($output) > self::STORE_DESC_MAX_CHARS) {
                $output = substr($output, 0, self::STORE_DESC_MAX_CHARS) . ' ... ';
            }
        }

        return $output;
    }

    /**
     *
     * display seller vacation mode message
     *
     * @return bool|string
     */
    public function vacationMode()
    {
        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
            return false;
        }

        if ($user->isVacation()) {
            $translate = $this->getTranslate();

            $returnDate = $user->getGlobalSettings('vacation_mode_return_date');
            if (!empty($returnDate)) {
                return sprintf($translate->_('The seller is in vacation until %s.'), $this->getView()->date($returnDate, true));
            }
            else {
                return $translate->_('The seller is currently in vacation.');
            }
        }

        return false;
    }

    /**
     *
     * get the name of a location based on its id
     *
     * @param int|string $location
     * @param string     $key
     *
     * @return array|null|string
     */
    protected function _getLocationName($location, $key = 'name')
    {
        if (empty($location)) {
            return null;
        }
        if (is_numeric($location)) {
            $locations = new Service\Table\Relational\Locations();
            $row = $locations->findBy('id', (int)$location);
            if ($row != null) {
                $location = $row->getData($key);
            }
        }

        return $location;
    }
}

