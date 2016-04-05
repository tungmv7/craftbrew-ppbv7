<?php

/**
 *
 * PHP Pro Bid $Id$ pw4JZI77Vz5pZE6Ta5a1CTuGDAvNsQhA9M5mp9F1GpQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * accounting table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Accounting extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'accounting';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Accounting';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Accounting';

    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'User'    => array(
            self::COLUMNS         => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS     => 'id',
        ),
        'Listing' => array(
            self::COLUMNS         => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS     => 'id',
        ),
        'Sale' => array(
            self::COLUMNS         => 'sale_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Sales',
            self::REF_COLUMNS     => 'id',
        ),

    );

}