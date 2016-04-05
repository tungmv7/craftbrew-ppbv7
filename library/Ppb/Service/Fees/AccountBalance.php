<?php

/**
 *
 * PHP Pro Bid $Id$ 48+MzrAu77jatd1tuIDW9vjvv4ZUrzuIYUlnadT9yWI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * user account balance crediting/debiting fee class
 */

namespace Ppb\Service\Fees;

use Ppb\Service;

class AccountBalance extends Service\Fees
{

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
     * update the balance of the selected user
     *
     * @param bool $ipn  true if payment is completed, false otherwise
     * @param array   $post array keys: {user_id, amount}
     * @return $this
     */
    public function callback($ipn, array $post)
    {
        if ($ipn) {
            $usersService = new Service\Users();
            $user = $usersService->findBy('id', $post['user_id']);

            if (count($user) > 0) {
                $user->updateBalance((-1) * $post['amount']);
            }
        }

        return $this;
    }
}

