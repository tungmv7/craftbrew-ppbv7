<?php

/**
 * 
 * PHP Pro Bid $Id$ 56vUz4t29Wwo4n+8HvSp/vojrCVyp13wVhbZJKfVn8c=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * word filter table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class WordFilter extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'word_filter';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}