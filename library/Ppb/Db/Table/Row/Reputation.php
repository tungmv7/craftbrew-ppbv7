<?php

/**
 *
 * PHP Pro Bid $Id$ sU4nEvQMmyXOZJvru2b2wMl9lhIk4RKXaMOHXIbNVC8=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * reputation table row object model
 */

namespace Ppb\Db\Table\Row;

class Reputation extends AbstractRow
{

    /**
     *
     * checks if the reputation comments can be shown.
     *
     *
     * @param bool $admin
     * @return bool
     */
    public function canShowComments($admin = false)
    {
        if ($admin) {
            return true;
        }

        $settings = $this->getSettings();

        if (!$settings['private_reputation']) {
            return true;
        }
        else {
            $user = $this->getUser();

            if (in_array($user['id'], array($this->getData('poster_id'), $this->getData('user_id')))) {
                return true;
            }
        }

        return false;
    }

}

