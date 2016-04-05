<?php

/**
 * 
 * PHP Pro Bid $Id$ /qp7MZGEyxgIGXe0MBH81AczIHbibzsKu1ZGXlhYZKY=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * currencies table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Currencies extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'currencies';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}