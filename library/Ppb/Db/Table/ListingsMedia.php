<?php

/**
 *
 * PHP Pro Bid $Id$ ReCBLmVGgLBaCgnOHQYB6i8sHmO54CF1gvHo41gp5G4=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * listings media table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class ListingsMedia extends AbstractTable
{

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name = 'listings_media';

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
    protected $_rowClass = '\Ppb\Db\Table\Row\ListingMedia';

    /**
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Ppb\Db\Table\Rowset\ListingsMedia';
    /**
     *
     * reference map
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Listing' => array(
            self::COLUMNS         => 'listing_id',
            self::REF_TABLE_CLASS => '\Ppb\Db\Table\Listings',
            self::REF_COLUMNS     => 'id',
        ),
    );

}

