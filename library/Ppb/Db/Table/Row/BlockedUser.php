<?php

/**
 *
 * PHP Pro Bid $Id$ enoDMA3YeQvFwi3Q2eFBgUJtCeC48+9gKgBFrxfoy1s=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * blocked user table row object model
 */

namespace Ppb\Db\Table\Row;

class BlockedUser extends AbstractRow
{
    /**
     * types of variables/values accepted for blocking
     */
    const TYPE_IP = 'ip';
    const TYPE_EMAIL = 'email';
    const TYPE_USERNAME = 'username';

    /**
     * types of actions that can be blocked
     */
    const ACTION_REGISTER = 'register';
    const ACTION_PURCHASE = 'purchase';
    const ACTION_MESSAGING = 'messaging';

    /**
     *
     * block types
     *
     * @var array
     */
    public static $blockTypes = array(
        self::TYPE_IP       => 'IP Address',
        self::TYPE_EMAIL    => 'Email Address',
        self::TYPE_USERNAME => 'Username',
    );

    /**
     *
     * blocked actions
     *
     * @var array
     */
    public static $blockedActions = array(
        self::ACTION_REGISTER  => 'Registering / Logging In',
        self::ACTION_PURCHASE  => 'Purchasing',
        self::ACTION_MESSAGING => 'Messaging',
    );

    /**
     *
     * get blocked actions as an array
     *
     * @return array
     */
    public function getBlockedActions()
    {
        return array_filter(\Ppb\Utility::unserialize($this->getData('blocked_actions'), array()));
    }
}

