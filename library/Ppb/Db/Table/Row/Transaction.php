<?php

/**
 *
 * PHP Pro Bid $Id$ dHSQ8BEJNbDbCB/Dp7yYg8u5kjrZzWehb8Qalo5gbLw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * transactions table row object model
 */

namespace Ppb\Db\Table\Row;

class Transaction extends AbstractAccounting
{


    /**
     *
     * transactions row invoice details link
     *
     * @return array
     */
    public function link()
    {
        return array(
            'module'     => 'members',
            'controller' => 'account',
            'action'     => 'invoice',
            'type'       => 'transactions',
            'id'         => $this->getData('id')
        );
    }

    /**
     *
     * invoice details page caption
     *
     * @return string
     */
    public function caption()
    {
        return $this->getTranslate()->_('Receipt');
    }


}

