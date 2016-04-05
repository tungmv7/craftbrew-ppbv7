<?php

/**
 * 
 * PHP Pro Bid $Id$ znSUuM4wWKyi4xJDIYEIDq/SUq4DNL248GWN8/gagtM=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * content pages table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ContentPages extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'content_pages';

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
        'Section' => array(
            self::COLUMNS => 'section_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\ContentSections',
            self::REF_COLUMNS => 'id',
        ),
    );

}