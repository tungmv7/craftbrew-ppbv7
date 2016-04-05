<?php

/**
 *
 * PHP Pro Bid $Id$ OSRjVgFrb5oE5X/av3bMlm4jZwej4rbJIgdLfuomCPg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * user signup fee class
 */

namespace Ppb\Service\Fees;

use Ppb\Service;

class UserSignup extends Service\Fees
{

    /**
     *
     * fees to be included
     *
     * @var array
     */
    protected $_fees = array(
        self::SIGNUP => 'User Signup Fee',
    );

    /**
     *
     * total amount to be paid after the calculate method is called
     *
     * @var float
     */
    protected $_totalAmount;

    /**
     *
     * redirect to login page after signup fee payment
     *
     * @var array
     */
    protected $_redirect = array(
        'module'     => 'members',
        'controller' => 'user',
        'action'     => 'login',
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
     * @param array $post array keys: {user_id}
     * @return \Ppb\Service\Fees\UserSignup
     */
    public function callback($ipn, array $post)
    {
        $usersService = new Service\Users();
        $user = $usersService->findBy('id', $post['user_id']);

        $flag = ($ipn) ? 1 : 0;
        $paymentStatus = ($ipn) ? 'confirmed' : 'failed';
        $user->save(array(
            'active'         => $flag,
            'payment_status' => $paymentStatus,
        ));

        return $this;
    }

    /**
     *
     * get the signup fee value (tax included)
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->getFeeAmount('signup');
    }

}

