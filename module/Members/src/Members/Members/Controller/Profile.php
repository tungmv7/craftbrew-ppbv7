<?php

/**
 *
 * PHP Pro Bid $Id$ gt+yJmoXBK+NL/Pn9/da/O4gX5C3in4xOhheg1Kbq8k=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * members module - profile management controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction;

class Profile extends AbstractAction
{

    public function Setup()
    {
        return array(
            'headline' => $this->_('Profile Setup'),
        );
    }

}

