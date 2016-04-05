<?php

/**
 * 
 * PHP Pro Bid $Id$ sh8rfeWNnVPvr+T8WeKHn8TeGfj1TYAY214qoRbU3nY=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.4
 */
/**
 * listings table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Listings extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'listings';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Listing';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Listings';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Owner' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Category' => array(
            self::COLUMNS => 'category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS => 'id',
        ),
        'AddlCategory' => array(
            self::COLUMNS => 'addl_category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS => 'id',
        ),
        'Country' => array(
            self::COLUMNS => 'country',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Locations',
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
        '\Ppb\Db\Table\Accounting',
        '\Ppb\Db\Table\Bids',
        '\Ppb\Db\Table\ListingsMedia',
        '\Ppb\Db\Table\ListingsWatch',
        '\Ppb\Db\Table\Offers',
        '\Ppb\Db\Table\SalesListings',
        '\Ppb\Db\Table\Messaging',
        '\Ppb\Db\Table\RecentlyViewedListings',
    );

}

