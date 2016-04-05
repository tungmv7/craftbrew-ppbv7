<?php

/**
 * 
 * Cube Framework $Id$ Md4kk7x2hfC1g3OkOGuqR0D+xAyWfcF5868oaNmWOQU= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * db table select pagination adapter
 * returns an object of type Cube\Db\Table\Rowset\AbstractRowset
 */

namespace Cube\Paginator\Adapter;

use Cube\Db\Select,
    Cube\Db\Table\AbstractTable;

class DbTableSelect extends DbSelect
{

    /**
     *
     * table class the row belongs to
     * 
     * @var \Cube\Db\Table\AbstractTable
     */
    protected $_table = null;

    /**
     *
     * class constructor
     *
     * @param \Cube\Db\Select              $select the select object
     * @param \Cube\Db\Table\AbstractTable $table
     */
    public function __construct(Select $select, AbstractTable $table)
    {
        parent::__construct($select);

        $this->_table = $table;
    }

    /**
     * 
     * returns an array of items for the selected page
     *
     * @param  integer $offset              page offset
     * @param  integer $itemCountPerPage    number of items per page
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function getItems($offset, $itemCountPerPage)
    {

        $this->_select->limit($itemCountPerPage, $offset);

        return $this->_table->fetchAll($this->_select);
    }

}

