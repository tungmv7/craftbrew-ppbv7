<?php

/**
 *
 * PHP Pro Bid $Id$ hwG2qgipCc7KYdWyE8WYN6JxrwI1isqwElqhud59HMU=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * newsletters table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class Newsletters extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'newsletters';

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
        '\Ppb\Db\Table\NewslettersRecipients',
    );

}