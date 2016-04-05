<?php

/**
 * 
 * PHP Pro Bid $Id$ MxWlKbWh96PZIrgACQpdgJHZBnLQV3l/77qskP49zASwRbYADcxnaP/JE+tK8mZbqJR2bPcDnFWqypNb3x2pIQ==
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ShippingCarriersSettings extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'shipping_carriers_settings';

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
        'Carrier' => array(
            self::COLUMNS => 'carrier_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\ShippingCarriers',
            self::REF_COLUMNS => 'id',
        ),
    );

}

