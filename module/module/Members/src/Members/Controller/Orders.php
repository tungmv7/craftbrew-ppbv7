<?php

/**
 * 
 * PHP Pro Bid $Id$ KBfH17WoZiagacxP4sfhYxF4q+UtoIUxhQiyKz/2YcM=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2013 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * members module - orders controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction;

class Orders extends AbstractAction
{

    public function Sold()
    {
        return array(
            'headline' => 'Sold Items',
        );
    }

    public function Purchased()
    {
        return array(
            'headline' => 'Purchased Items',
        );
    }

}

