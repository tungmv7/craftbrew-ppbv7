<?php

/**
 * 
 * PHP Pro Bid $Id$ K9XX3eTUECwOh4n55QE6tdfVoZxrJa5mvgM8j0ZExgQ=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * transactions table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Transactions extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'transactions';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\Transaction';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\Transactions';

    /**
     *
     * reference map
     * 
     * @var array
     */
    protected $_referenceMap = array(
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Sale' => array(
            self::COLUMNS => 'sale_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Sales',
            self::REF_COLUMNS => 'id',
        ),
        'PaymentGateway' => array(
            self::COLUMNS => 'gateway_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\PaymentGateways',
            self::REF_COLUMNS => 'id',
        ),
    );

}