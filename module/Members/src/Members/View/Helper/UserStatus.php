<?php

/**
 * 
 * PHP Pro Bid $Id$ BEF06JodcXCuBM4bXKZ5aJxnTs7/6Ofw1+aE41Ug+ZY=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2013 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * user status view helper class
 */

namespace Members\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Db\Table\Row\User as UserModel;

class UserStatus extends AbstractHelper
{

    public function userStatus(UserModel $user)
    {
        $output = null;

        if (
                isset($user['approved']) &&
                isset($user['mail_activated']) &&
                isset($user['preferred_seller']) &&
                isset($user['active'])) {

            if ($user['preferred_seller']) {
                $output .= '<span class="label label-preferred">Preferred Seller</span><br>';
            }

            if (!$user['mail_activated']) {
                $output .= '<span class="label">Email Not Verified</span><br>';
            }

            if (!$user['approved']) {
                $output .= '<span class="label label-important">Unapproved</span>';
            }
            else if (!$user['active']) {
                $output .= '<span class="label label-warning">Suspended</span>';
            }
            else {
                $output .= '<span class="label label-success">Active</span>';
            }

            return $output;
        }
        else {
            throw new \InvalidArgumentException("The user object must include values for 
                'preferred_seller', 'approved', 'mail_activated' and 'active' keys.");
        }
    }

}

