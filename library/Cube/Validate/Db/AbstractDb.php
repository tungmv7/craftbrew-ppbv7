<?php

/**
 * 
 * Cube Framework $Id$ bw0vKUINIQFZsk7mi88aHoLNfQzUsY4GPygKoiWOEO0= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.5
 */

namespace Cube\Validate\Db;

use Cube\Validate\AbstractValidate,
    Cube\Db\Table\AbstractTable,
    Cube\Db\Select;

abstract class AbstractDb extends AbstractValidate
{

    /**
     *
     * table object
     * 
     * @var \Cube\Db\Table\AbstractTable 
     */
    protected $_table;

    /**
     *
     * table field
     * 
     * @var string
     */
    protected $_field;

    /**
     *
     * data to be excluded by the query
     * 
     * @var mixed
     */
    protected $_exclude;

    /**
     *
     * select object
     * 
     * @var \Cube\Db\Select 
     */
    protected $_select;

    /**
     *
     * class constructor
     *
     * add the table and the field to compare against
     *
     * @param array $data
     * Supported keys:
     * 'table'      -> table - fully qualified namespace;
     * 'field'      -> table field
     * 'exclude'    -> where clause or field/value pair to exclude from the query
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = null)
    {
        if (array_key_exists('table', $data)) {
            $this->setTable($data['table']);
        }
        else {
            throw new \InvalidArgumentException("'table' option missing from the validator.");
        }

        if (array_key_exists('field', $data)) {
            $this->setField($data['field']);
        }
        else {
            throw new \InvalidArgumentException("'field' option missing from the validator.");
        }

        if (array_key_exists('exclude', $data)) {
            $this->setExclude($data['exclude']);
        }
    }

    /**
     * 
     * get table object
     * 
     * @return \Cube\Db\Table\AbstractTable 
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     *
     * set table object
     *
     * @param string|\Cube\Db\Table\AbstractTable $table table object or fully qualified name
     * @throws \InvalidArgumentException
     * @return \Cube\Validate\Db\AbstractDb
     */
    public function setTable($table)
    {
        if ($table instanceof AbstractTable) {
            $this->_table = $table;
        }
        else if (class_exists((string) $table)) {
            $this->_table = new $table();
        }
        else {
            throw new \InvalidArgumentException("The table variable must be 
                an instance of \Cube\Db\Table\AbstractTable, 
                or a string representing the fully qualified namespace of the table.");
        }


        return $this;
    }

    /**
     * 
     * get table field
     * 
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * 
     * set table field
     * 
     * @param string $field
     * @return \Cube\Validate\Db\AbstractDb
     */
    public function setField($field)
    {
        $this->_field = (string) $field;

        return $this;
    }

    /**
     * 
     * get data to exclude
     * 
     * @return array
     */
    public function getExclude()
    {
        return $this->_exclude;
    }

    /**
     * 
     * set data to exclude
     * 
     * @param array $exclude
     * @return \Cube\Validate\Db\AbstractDb
     */
    public function setExclude($exclude)
    {
        $this->_exclude = $exclude;

        return $this;
    }

    /**
     * 
     * set select object
     * 
     * @param \Cube\Db\Select $select
     * @return \Cube\Validate\Db\AbstractDb
     */
    public function setSelect(Select $select)
    {
        $this->_select = $select;
        
        return $this;
    }

    /**
     * 
     * get select object, construct if it wasn't set yet
     * 
     * @return \Cube\Db\Select
     */
    public function getSelect()
    {
        if ($this->_select === null) {
            $adapter = $this->_table->getAdapter();

            $select = $this->_table->select()
                    ->where($adapter->quoteIdentifier($this->_field) . ' = ?', strval($this->_value));

            if ($this->_exclude !== null) {
                if (is_array($this->_exclude)) {
                    $select->where(
                            $adapter->quoteIdentifier($this->_exclude['field'], true) .
                            ' != ?', strval($this->_exclude['value'])
                    );
                }
                else {
                    $select->where($this->_exclude);
                }
            }
            
            $select->limit(1);
            
            $this->_select = $select;            
        }
        
        return $this->_select;
    }

}

