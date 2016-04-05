<?php

/**
 * 
 * PHP Pro Bid $Id$ SgG0qjxnlKxoL/9XlE6Axag2s4Mqok3tVX8GyQtZIJg=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * tax types table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class TaxTypes extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'tax_types';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\TaxType';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\TaxTypes';

}