<?php

/**
 * 
 * PHP Pro Bid $Id$ pSDcq50uuxcH1jiF8nRW5lOYPgL86IdnLnS+xYpyiZQ=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class PaymentGateways extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'payment_gateways';

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
        '\Ppb\Db\Table\PaymentGatewaysSettings',
        '\Ppb\Db\Table\Transactions',
    );

}

