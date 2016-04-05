<?php

/**
 * 
 * PHP Pro Bid $Id$ vczOEJasrBSlO0BTfRYcEogTYg3phIm7Bzl2MKvZk3U=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * stores subscriptions table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class StoresSubscriptions extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'stores_subscriptions';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\StoreSubscription';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\StoresSubscriptions';

    /**
     *
     * dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\Users',
    );

}