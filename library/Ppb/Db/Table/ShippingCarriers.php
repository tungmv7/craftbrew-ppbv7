<?php

/**
 * 
 * PHP Pro Bid $Id$ tiqz+t1Y5w17PMmlYmi0nnTUK+6/47b57GUmmLndMw8=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ShippingCarriers extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'shipping_carriers';

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
        '\Ppb\Db\Table\ShippingCarriersSettings',
        '\Ppb\Db\Table\Transactions',
    );

}

