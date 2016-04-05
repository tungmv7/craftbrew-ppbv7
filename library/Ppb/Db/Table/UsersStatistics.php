<?php

/**
 *
 * PHP Pro Bid $Id$ dWgD00lP7qvB+C78YKYRE/O/MLMVfW3RvVKO/4FHYdY=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.1
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class UsersStatistics extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'users_statistics';

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
            self::COLUMNS         => 'user_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Users',
            self::REF_COLUMNS     => 'id',
        ),
    );

}

