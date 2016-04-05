<?php

/**
 * 
 * PHP Pro Bid $Id$ yREXlQQAGkt+WsJYQ8DCFjLSZzyMmmEEd4TJMmFE2XA=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * link redirects table
 */

namespace Ppb\Db\Table;

use Cube\Db\Table\AbstractTable;

class LinkRedirects extends AbstractTable
{

    /**
     *
     * table name
     * 
     * @var string
     */
    protected $_name = 'link_redirects';

    /**
     *
     * primary key
     * 
     * @var string
     */
    protected $_primary = 'id';

}