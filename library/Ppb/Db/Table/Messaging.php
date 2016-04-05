<?php

/**
 * 
 * PHP Pro Bid $Id$ z0ojKERmqaMw5qtG7Kes3tUPPFX++KgxZfjTvEYT+oQ=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Messaging extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'messaging';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Message';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Messages';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Topic' => array(
            self::COLUMNS => 'topic_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Messaging',
            self::REF_COLUMNS => 'topic_id',
        ),
        'Sender' => array(
            self::COLUMNS => 'sender_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Receiver' => array(
            self::COLUMNS => 'receiver_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Listing' => array(
            self::COLUMNS => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS => 'id',
        ),
        'Sale' => array(
            self::COLUMNS => 'sale_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Sales',
            self::REF_COLUMNS => 'id',
        ),
    );

    /**
     *
     * dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\Messaging',
        '\Ppb\Db\Table\Sales',
    );

}

