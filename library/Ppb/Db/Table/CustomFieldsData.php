<?php

/**
 * 
 * PHP Pro Bid $Id$ eBR8qPhV1vmloIN+phBSX/hQtbRdh1l1d5eCHIePGQY=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * custom fields data table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class CustomFieldsData extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'custom_fields_data';

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
        'Field' => array(
            self::COLUMNS => 'field_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\CustomFields',
            self::REF_COLUMNS => 'id',
        ),
    );

}