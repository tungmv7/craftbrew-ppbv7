<?php

/**
 * 
 * PHP Pro Bid $Id$ gnZ+fYbyUp2Dt3nc5pWhtjkKScHsYYGu7yrmf7e5QFE=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * locations table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Locations extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'locations';

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
        'Location' => array(
            self::COLUMNS => 'parent_id',
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
        '\Ppb\Db\Table\Locations',
    );

}