<?php

/**
 * 
 * PHP Pro Bid $Id$ wgmNCGGyCATf9DsHTJQKDfH+blKz6IyY3pottmC//J0=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * listings watch table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ListingsWatch extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'listings_watch';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Listing' => array(
            self::COLUMNS => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS => 'id',
        ),
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
    );

}

