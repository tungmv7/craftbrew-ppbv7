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
 * store subscription details view helper class
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
        Ppb\Db\Table\Row\StoreSubscription as SubscriptionModel,
        Ppb\Service;

class StoreSubscription extends AbstractHelper
{

    /**
     *
     * store subscription model
     *
     * @var \Ppb\Db\Table\Row\StoreSubscription
     */
    protected $_subscription;

    /**
     *
     * main method, only returns object instance
     *
     * @param int|string|\Ppb\Db\Table\Row\StoreSubscription $subscription
     * @return $this
     */
    public function storeSubscription($subscription = null)
    {
        if ($subscription !== null) {
            $this->setSubscription($subscription);
        }

        return $this;
    }

    /**
     *
     * get store subscription model
     *
     * @return \Ppb\Db\Table\Row\StoreSubscription
     * @throws \InvalidArgumentException
     */
    public function getSubscription()
    {
        if (!$this->_subscription instanceof SubscriptionModel) {
            throw new \InvalidArgumentException("The store subscription model has not been instantiated");
        }

        return $this->_subscription;
    }

    /**
     *
     * set store subscription model
     *
     * @param \Ppb\Db\Table\Row\StoreSubscription $subscription
     * @return $this
     */
    public function setSubscription(SubscriptionModel $subscription)
    {
        $this->_subscription = $subscription;

        return $this;
    }

    /**
     *
     * display store subscription description
     *
     * @param string $separator
     * @return string
     */
    public function description($separator = ', ')
    {
        $output = array();

        $translate = $this->getTranslate();

        $subscription = $this->getSubscription();

        $output[] = $this->getView()->amount($subscription->getData('price'));

        $output[] = sprintf($translate->_('%s listings'), $subscription->getData('listings'));

        $recurring = $subscription->getData('recurring_days');

        if ($recurring > 0) {
            $output[] = sprintf($translate->_('recurring every %s days'), $recurring);
        }
        else {
            $output[] = $translate->_('one time fee');
        }

        if ($subscription->getData('featured_store')) {
            $output[] = $translate->_('featured store');
        }

        return implode($separator, $output);
    }
}

