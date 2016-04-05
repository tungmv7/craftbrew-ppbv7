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
 * reputation (feedback) table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Reputation extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'reputation';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Reputation';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Reputation';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Poster' => array(
            self::COLUMNS => 'poster_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'SaleListing' => array(
            self::COLUMNS => 'sale_listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\SalesListings',
            self::REF_COLUMNS => 'id',
        ),

    );

}