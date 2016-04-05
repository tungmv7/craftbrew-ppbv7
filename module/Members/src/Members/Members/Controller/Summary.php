<?php

/**
 *
 * PHP Pro Bid $Id$ P55T/GTgUk00ZF5ah08Lc8ODsVeAiTCSFbxC1f3OCbM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * members module - summary controller
 */

namespace Members\Controller;

use Members\Controller\Action\AbstractAction;

class Summary extends AbstractAction
{

    public function Index()
    {

        return array(
            'controller' => 'Members Area',
            'headline' => $this->_('Account Details'),
            'user'       => $this->_user,
            'messages'   => $this->_flashMessenger->getMessages(),
        );
    }

}

