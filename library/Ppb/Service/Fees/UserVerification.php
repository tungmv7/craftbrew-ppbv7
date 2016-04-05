<?php

/**
 *
 * PHP Pro Bid $Id$ 6k2HWZ4WYzRTO3SdFm1dYLJ5ULghGyuNMFdasGva4YM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * user verification fee class
 */

namespace Ppb\Service\Fees;

use Cube\Controller\Front,
        Ppb\Service;

class UserVerification extends Service\Fees
{

    /**
     *
     * fees to be included
     *
     * @var array
     */
    protected $_fees = array(
        self::USER_VERIFICATION => 'User Verification Fee',
    );

    /**
     *
     * completed payment redirect path
     *
     * @var array
     */
    protected $_redirect = array(
        'module'     => 'members',
        'controller' => 'summary',
        'action'     => 'index'
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
        }
    }

    /**
     *
     * activate the affected user
     *
     * @param bool  $ipn  true if payment is completed, false otherwise
     * @param array $post array keys: {user_id, recurring, refund}
     * @return $this
     */
    public function callback($ipn, array $post)
    {
        $usersService = new Service\Users();
        $user = $usersService->findBy('id', $post['user_id']);

        $flag = ($ipn) ? 1 : 0;

        if (count($user) > 0) {
            $user->updateUserVerification($flag, true, $post['recurring'], $post['refund']);
        }

        return $this;
    }

    public function getTotalAmount()
    {
        $settings = $this->getSettings();

        return $this->_addTax($settings['user_verification_fee']);
    }
}

