<?php

/**
 * 
 * PHP Pro Bid $Id$ RmAxuZZ9sDSxVDM9NaBqZ1l/twhQme5M46HOMXGTRlg=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * favorite stores table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class FavoriteStores extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'favorite_stores';

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
        'User' => array(
            self::COLUMNS => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
        'Store' => array(
            self::COLUMNS => 'store_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS => 'id',
        ),
    );

}