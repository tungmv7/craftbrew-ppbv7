<?php

/**
 * 
 * PHP Pro Bid $Id$ cx/TKhIgYw2jf06SvyF1e43rpQkZJeRs0naiY0Tfc/yOlExDS6lIcpA75Sn/Zgtr8ZMoYMkrTHTXj6ot5LrqkQ==
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class PaymentGatewaysSettings extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'payment_gateways_settings';

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
        'Gateway' => array(
            self::COLUMNS => 'gateway_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\PaymentGateways',
            self::REF_COLUMNS => 'id',
        ),
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
    );

}

