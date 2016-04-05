<?php

/**
 * 
 * PHP Pro Bid $Id$ DiFLsX6dxxi9VqyWgpy/iUrUj1z7h5qRiwsqEth5maQ=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * custom fields table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class CustomFields extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'custom_fields';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

    /**
     *
     * dependent tables
     * 
     * @var array
     */
    protected $_dependentTables = array(
        '\Ppb\Db\Table\CustomFieldsData',
    );

}