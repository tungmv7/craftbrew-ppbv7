<?php

/**
 * 
 * PHP Pro Bid $Id$ txRZDVBPG5TvTXNQnw6gzKCOOkcE+1275KfpIrhU+7k=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * fees table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Fees extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'fees';

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
        'Category' => array(
            self::COLUMNS => 'category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS => 'id',
        ),
    );

}

