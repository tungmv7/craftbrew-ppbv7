<?php

/**
 *
 * PHP Pro Bid $Id$ uOT5Iya5Hlkxhb5v1FSOLe7Qlk8MSfEmRPt6pS7i+ok=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * store subscription fee class
 */

namespace Ppb\Service\Fees;

use Ppb\Service,
        Ppb\Db\Table\Row\StoreSubscription as StoreSubscriptionModel;

class StoreSubscription extends Service\Fees
{

    /**
     *
     * fees to be included
     *
     * @var array
     */
    protected $_fees = array(
        self::USER_VERIFICATION => 'Store Subscription Fee',
    );

    /**
     *
     * store subscription model
     *
     * @var \Ppb\Db\Table\Row\StoreSubscription
     */
    protected $_subscription;

    /**
     *
     * completed payment redirect path
     *
     * @var array
     */
    protected $_redirect = array(
        'module'     => 'members',
        'controller' => 'store',
        'action'     => 'setup'
    );

    /**
     *
     * class constructor
     *
     * @param integer|string|\Ppb\Db\Table\Row\User $user the user that will be paying and for which the signup action will apply
     */
    public function __construct($user = null)
    {
        parent::__construct();

        if ($user !== null) {
            $this->setUser($user);
            $this->setSubscription();
        }
    }

    /**
     *
     * get store subscription model
     *
     * @return \Ppb\Db\Table\Row\StoreSubscription
     */
    public function getSubscription()
    {
        return $this->_subscription;
    }

    /**
     *
     * set store subscription model
     *
     * @param \Ppb\Db\Table\Row\StoreSubscription $subscription
     * @return $this
     */
    public function setSubscription(StoreSubscriptionModel $subscription = null)
    {
        if (!$subscription instanceof StoreSubscriptionModel) {
            $subscription = $this->_user->findParentRow('\Ppb\Db\Table\StoresSubscriptions');
        }

        $this->_subscription = $subscription;

        return $this;
    }


    /**
     *
     * activate the affected user
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post array keys: {user_id, subscription_id}
     * @return $this
     */
    public function callback($ipn, array $post)
    {
        $usersService = new Service\Users();
        $user = $usersService->findBy('id', $post['user_id']);

        $flag = ($ipn) ? 1 : 0;

        if (count($user) > 0) {
            $user->updateStoreSubscription($flag, $post['subscription_id']);
        }

        return $this;
    }

    /**
     *
     * get subscription fee amount
     *
     * @return float
     */
    public function getTotalAmount()
    {
        if ($this->_subscription instanceof StoreSubscriptionModel) {
            return $this->_addTax($this->_subscription->getData('price'));
        }

        return null;
    }
}

