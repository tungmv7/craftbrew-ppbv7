<?php

/**
 * 
 * Cube Framework $Id$ /Lyt4RWP2OrKkVjbnBHdyLnznWT1aPsunCE4DR2SB9I= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * paginator adapter interface
 */

namespace Cube\Paginator\Adapter;

interface AdapterInterface extends \Countable
{

    /**
     * 
     * returns an collection of items for a page
     *
     * @param  integer $offset              page offset
     * @param  integer $itemCountPerPage    number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage);
}

