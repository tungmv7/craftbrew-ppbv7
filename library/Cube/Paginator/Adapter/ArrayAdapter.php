<?php

/**
 * 
 * Cube Framework $Id$ Boj3xOLKEvBEjiBrYl13ljwjNUSGcVSGYv8qTpYGnss= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * array pagination adapter
 */

namespace Cube\Paginator\Adapter;

class ArrayAdapter implements AdapterInterface
{

    /**
     *
     * data to paginate
     * 
     * @var array
     */
    protected $_data = null;

    /**
     *
     * number of array rows
     * 
     * @var integer
     */
    protected $_count = null;

    /**
     * 
     * class constructor
     * 
     * @param array $data   the array to be paginated
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
        $this->_count = count($this->_data);
    }

    /**
     * 
     * returns an array of items for the selected page
     *
     * @param  integer $offset              page offset
     * @param  integer $itemCountPerPage    number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return array_slice($this->_data, $offset, $itemCountPerPage);
    }

    /**
     * 
     * return the number of array rows
     * 
     * @return integer
     */
    public function count()
    {
        return $this->_count;
    }

}

