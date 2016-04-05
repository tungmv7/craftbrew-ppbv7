<?php

/**
 * 
 * PHP Pro Bid $Id$ BOlDQIqCKNAafbGqCKLCNrniNlkGfeHFKSJ2QAlYy+o=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * categories table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Categories extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'categories';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Category';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Categories';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'Parent' => array(
            self::COLUMNS => 'parent_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS => 'id',
        ),
        'Owner' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
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
        '\Ppb\Db\Table\Fees',
        '\Ppb\Db\Table\Listings',
        '\Ppb\Db\Table\Categories',
        '\Ppb\Db\Table\AutocompleteTags',
        '\Ppb\Db\Table\Users',
    );

}