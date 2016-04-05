<?php

/**
 * 
 * PHP Pro Bid $Id$ u1RyBR3I2oK/ScNxhlnfgAiC5sx4I98SPZ5BR1yyDoA=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * bid increments table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class BidIncrements extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'bid_increments';
    
    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}