<?php

/**
 * 
 * PHP Pro Bid $Id$ ApWTXRZ1YQNstyOA/9kKmV3wwTHFgUo7WrOsf5BWxI0=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * advertising table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Advertising extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'advertising';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Advert';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Adverts';
}