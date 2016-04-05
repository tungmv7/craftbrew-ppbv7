<?php

/**
 *
 * PHP Pro Bid $Id$ u9wmZb7Whyp71RsAD0Vg/NSWfZvNkSxwVyaEF0gD05I=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * SEARCH AUTOCOMPLETE MOD
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class AutocompleteTags extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'autocomplete_tags';

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
        'Category' => array(
            self::COLUMNS         => 'category_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Categories',
            self::REF_COLUMNS     => 'id',
        ),
    );

}

