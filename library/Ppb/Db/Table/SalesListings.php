<?php

/**
 *
 * PHP Pro Bid $Id$ WevKWhWSCeUDZY2PxkWLEPRQ7PcoH3837lNApuyRf24=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class SalesListings extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'sales_listings';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\SaleListing';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\SalesListings';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Listing' => array(
            self::COLUMNS         => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS     => 'id',
        ),
        'Sale'    => array(
            self::COLUMNS         => 'sale_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Sales',
            self::REF_COLUMNS     => 'id',
        ),
    );
}

