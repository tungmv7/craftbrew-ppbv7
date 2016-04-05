<?php

/**
 * 
 * PHP Pro Bid $Id$ jghX25IWD/hdDZR1c1H/iixTjAoEaC/Oy+vBj3YV1RA=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Sales extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'sales';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

    /**
     * 
     * class name for row
     *
     * @var string
     */
    protected $_rowClass = '\Ppb\Db\Table\Row\Sale';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Sales';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Buyer' => array(
            self::COLUMNS => 'buyer_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Seller' => array(
            self::COLUMNS => 'seller_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'BillingAddress' => array(
            self::COLUMNS => 'billing_address_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\UsersAddressBook',
            self::REF_COLUMNS => 'id',
        ),
        'ShippingAddress' => array(
            self::COLUMNS => 'shipping_address_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\UsersAddressBook',
            self::REF_COLUMNS => 'id',
        ),
        'Messaging' => array(
            self::COLUMNS => 'messaging_topic_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Messaging',
            self::REF_COLUMNS => 'topic_id',
        ),
    );

    /**
     *
     * dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\SalesListings',
        '\Ppb\Db\Table\Transactions',
        '\Ppb\Db\Table\Messaging',
        '\Ppb\Db\Table\Accounting',
    );

}

