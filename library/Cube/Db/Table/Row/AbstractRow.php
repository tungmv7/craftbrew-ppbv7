<?php

/**
 *
 * Cube Framework $Id$ rpmkiISsH5YDcD72rZWwJQTXMcw7ZB8WYEtmd0W4/RI=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.7
 */

namespace Cube\Db\Table\Row;

use Cube\Db\Table\AbstractTable,
    Cube\Db\Select,
    Cube\Db\Expr,
    Cube\Controller\Front,
    Cube\Translate\Adapter\AbstractAdapter as TranslateAdapter,
    Cube\Translate,
    Cube\Exception;

/**
 * abstract db table row class
 *
 * Class AbstractRow
 *
 * @package Cube\Db\Table\Row
 */
class AbstractRow implements \Countable, \ArrayAccess, \IteratorAggregate
{

    const FQN_SEP = '\\';

    /**
     *
     * table row data (column_name => value)
     *
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * table class the row belongs to
     *
     * @var \Cube\Db\Table\AbstractTable
     */
    protected $_table = null;

    /**
     *
     * primary key(s)
     *
     * @var array
     */
    protected $_primary;

    /**
     *
     * true if we do not have a deserialized object, false otherwise
     *
     * @var bool
     */
    protected $_connected = true;

    /**
     *
     * serializable fields
     *
     * @var array
     */
    protected $_serializable = array();

    /**
     *
     * translate adapter
     *
     * @var \Cube\Translate\Adapter\AbstractAdapter
     */
    protected $_translate;

    /**
     *
     * class constructor
     */
    public function __construct(array $data = array())
    {
        if (isset($data['table']) && $data['table'] instanceof AbstractTable) {
            $this->_table = $data['table'];
        }
        else {
            throw new \InvalidArgumentException("The 'table' key must be set when creating a Row object");
        }

        if (isset($data['data'])) {
            if (!is_array($data['data'])) {
                throw new \InvalidArgumentException(
                    sprintf('The "data" key must be an array, %s given.', gettype($data['data'])));
            }

            $this->setData($data['data']);
        }

        if (!$this->_primary) {
            $this->_primary = $this->_table->info('primary');
        }
    }

    /**
     *
     * set row data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->_data = array();

        foreach ($data as $name => $value) {
            $this->addData($name, $value);
        }

        return $this;
    }

    /**
     *
     * set (update) the value of a column in the row.
     * also accepts serialized fields, if the column names are defined in the $_serialized array
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addData($name, $value)
    {
        $array = null;

        if (in_array($name, $this->_serializable)) {
            $array = @unserialize($value);
        }

        if (is_array($array)) {
            $this->_data[$name] = $array;

            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    $this->_data[$key] = $value;
                }
            }
        }
        else {
            $name = $this->_formatColumn($name);
            $this->_data[$name] = $value;
        }

        return $this;
    }

    /**
     *
     * get row data
     *
     * @param string $name key name
     * @param mixed  $null null value
     *
     * @return array|string|null
     */
    public function getData($name = null, $null = null)
    {
        if ($name !== null) {
            $name = $this->_formatColumn($name);

            if (!empty($this->_data[$name])) {
                return $this->_data[$name];
            }

            return $null;
        }

        return $this->_data;
    }

    /**
     *
     * set translate adapter
     *
     * @param \Cube\Translate\Adapter\AbstractAdapter $translate
     *
     * @return $this
     */
    public function setTranslate(TranslateAdapter $translate)
    {
        $this->_translate = $translate;

        return $this;
    }

    /**
     *
     * get translate adapter
     *
     * @return \Cube\Translate\Adapter\AbstractAdapter
     */
    public function getTranslate()
    {
        if (!$this->_translate instanceof TranslateAdapter) {
            $translate = Front::getInstance()->getBootstrap()->getResource('translate');
            if ($translate instanceof Translate) {
                $this->setTranslate(
                    $translate->getAdapter());
            }
        }

        return $this->_translate;
    }

    /**
     *
     * save the updated row in the table
     * also update the data array from the object with the most recent values
     *
     * @param array $data   partial data to be saved
     *                      the complete row is saved if this parameter is null
     *
     * @throws \RuntimeException
     * @return $this
     */
    public function save(array $data = null)
    {
        if ($data === null) {
            $data = $this->_data;
        }
        else {
            foreach ($data as $key => $value) {
                $this->_data[$key] = $value;
            }
        }

        if ($this->_connected === true) {
            $this->_table->update($data, $this->_getWhereQuery());
        }
        else {
            throw new \RuntimeException("Cannot save a row in unconnected state.");
        }

        return $this;
    }

    /**
     *
     * get table
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     *
     * find a dependent rowset
     *
     * @param string|\Cube\Db\Table\AbstractTable $dependentTable
     * @param string                              $ruleKey
     * @param \Cube\Db\Select                     $select
     *
     * @return \Cube\Db\Table\Rowset
     * @throws \RuntimeException
     */
    public function findDependentRowset($dependentTable, $ruleKey = null, Select $select = null)
    {
        if (is_string($dependentTable)) {
            $dependentTable = $this->_getTableFromString($dependentTable);
        }

        if (!$dependentTable instanceof AbstractTable) {
            throw new \RuntimeException(
                sprintf('The table must be an instance of \Cube\Db\Table\AbstractTable, %s given',
                    gettype($dependentTable)));
        }

        if ($select === null) {
            $select = $dependentTable->select();
        }
        else {
            $select->reset(Select::COLUMNS);
            $select->reset(Select::FROM);

            $select->from($dependentTable->getName(), '*');
        }

        $map = $this->_prepareReference($dependentTable, $this->_table, $ruleKey);

        for ($i = 0; $i < count($map[AbstractTable::COLUMNS]); $i++) {
            $value = $this->_data[$map[AbstractTable::REF_COLUMNS][$i]];
            $column = $dependentTable->getAdapter()->quoteIdentifier(
                $map[AbstractTable::COLUMNS][$i]);

            $select->where("$column = ?", $value);
        }

        return $dependentTable->fetchAll($select);
    }

    /**
     *
     * count the rows in a dependent rowset
     *
     * @param string|\Cube\Db\Table\AbstractTable $dependentTable
     * @param string                              $ruleKey
     * @param \Cube\Db\Select                     $select
     *
     * @return int
     * @throws \RuntimeException
     */
    public function countDependentRowset($dependentTable, $ruleKey = null, Select $select = null)
    {
        if (is_string($dependentTable)) {
            $dependentTable = $this->_getTableFromString($dependentTable);
        }

        if (!$dependentTable instanceof AbstractTable) {
            throw new \RuntimeException(
                sprintf('The table must be an instance of \Cube\Db\Table\AbstractTable, %s given',
                    gettype($dependentTable)));
        }

        if ($select === null) {
            $select = $dependentTable->select();
        }
        else {
            $select->reset(Select::FROM);
            $select->from($dependentTable->getName(), '*');
        }

        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER);

        $select->columns(array('nb_rows' => new Expr('count(*)')));

        $map = $this->_prepareReference($dependentTable, $this->_table, $ruleKey);

        for ($i = 0; $i < count($map[AbstractTable::COLUMNS]); $i++) {
            $value = $this->_data[$map[AbstractTable::REF_COLUMNS][$i]];
            $column = $dependentTable->getAdapter()->quoteIdentifier(
                $map[AbstractTable::COLUMNS][$i]);

            $select->where("$column = ?", $value);
        }

        $stmt = $select->query();

        return (integer)$stmt->fetchColumn('nb_rows');
    }

    /**
     *
     * find the matching parent row
     *
     * @param string|\Cube\Db\Table\AbstractTable $parentTable
     * @param string                              $ruleKey
     * @param \Cube\Db\Select                     $select
     *
     * @return \Cube\Db\Table\Row\AbstractRow
     * @throws \RuntimeException
     */
    public function findParentRow($parentTable, $ruleKey = null, Select $select = null)
    {
        if (is_string($parentTable)) {
            $parentTable = $this->_getTableFromString($parentTable);
        }

        if (!$parentTable instanceof AbstractTable) {
            throw new \RuntimeException(
                sprintf('The table must be an instance of \Cube\Db\Table\AbstractTable, %s given',
                    gettype($parentTable)));
        }

        if ($select === null) {
            $select = $parentTable->select();
        }
        else {
            $select->reset(Select::COLUMNS);
            $select->reset(Select::FROM);

            $select->from($parentTable->getName(), '*');
        }

        $map = $this->_prepareReference($this->_table, $parentTable, $ruleKey);

        for ($i = 0; $i < count($map[AbstractTable::COLUMNS]); $i++) {
            $value = $this->_data[$map[AbstractTable::COLUMNS][$i]];
            $column = $parentTable->getAdapter()->quoteIdentifier(
                $map[AbstractTable::REF_COLUMNS][$i]);

            if ($value) {
                $select->where("$column = ?", $value);
            }
            else {
                $select->where("$column is null");
            }
        }

        return $parentTable->fetchRow($select);
    }

    /**
     *
     * delete the row from the table
     *
     * @return int
     */
    public function delete()
    {
        $result = $this->_table->delete($this->_getWhereQuery());

        $this->_data = array();

        return $result;
    }

    /**
     *
     * retrieve field value
     * proxy to getData($name) method
     *
     * @param string $name column name
     *
     * @return string|null      return field value or null if field doesnt exist
     */
    public function __get($name)
    {
        return $this->getData($name);
    }


    /**
     *
     * proxy to addData($name, $value) method
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        return $this->addData($name, $value);
    }

    /**
     *
     * unset column from row
     * TODO: don't allow primary key to be unset.
     *
     * @param string $name column name
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function __unset($name)
    {
        $name = $this->_formatColumn($name);

        if (!array_key_exists($name, $this->_data)) {
            throw new \InvalidArgumentException(
                sprintf("Column name '%s' is not in the row.", $name));
        }


        unset($this->_data[$name]);

        return $this;
    }

    /**
     *
     * data to be added in the serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_primary', '_data');
    }

    /**
     *
     * when deserializing an object, return it in unconnected state
     */
    public function __wakeup()
    {
        $this->_connected = false;
    }

    /**
     *
     * return data as an array
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this->_data;
    }

    /**
     *
     * check if column name exists in row
     *
     * @param string $name column name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $name = $this->_formatColumn($name);

        return array_key_exists($name, $this->_data);
    }

    /**
     *
     * check if column name is a string
     *
     * @param mixed $name column name
     *
     * @return string           valid column name
     * @throws \InvalidArgumentException
     */
    protected function _formatColumn($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                sprintf("The column name must be a string, %s given (%s)", gettype($name), $name));
        }

        return $name;
    }

    /**
     *
     * retrieves an associative array of primary keys.
     *
     * @throws \RuntimeException
     * @return array
     */
    protected function _getPrimaryKey()
    {
        if (!is_array($this->_primary)) {
            throw new \RuntimeException("The primary key must be set as an array");
        }

        $primary = array_flip($this->_primary);
        $array = array_intersect_key($this->_data, $primary);

        if (count($primary) != count($array)) {
            throw new \RuntimeException(
                sprintf("Table '%s' does not have the same primary key as the row.", get_class($this->_table)));
        }

        return $array;
    }

    /**
     *
     * Constructs where statement for retrieving row(s).
     *
     * @return array
     */
    protected function _getWhereQuery()
    {
        $where = array();
        $db = $this->_table->getAdapter();
        $primaryKey = $this->_getPrimaryKey();
        $info = $this->_table->info();
        $metadata = $info[AbstractTable::METADATA];

        // retrieve recently updated row using primary keys
        foreach ($primaryKey as $column => $value) {
            $type = $metadata[$column]['DATA_TYPE'];
            $columnName = $db->quoteIdentifier($column, true);
            $where[] = $db->quoteInto("{$columnName} = ?", $value, $type);
        }

        return $where;
    }

    /**
     *
     * create a new table object
     *
     * @param string $tableName
     *
     * @return \Cube\Db\Table\AbstractTable
     * @throws \RuntimeException
     */
    protected function _getTableFromString($tableName)
    {
        if (class_exists($tableName)) {
            return new $tableName();
        }

        throw new \RuntimeException(
            sprintf("Table '%s' does not exist.", $tableName));
    }

    /**
     *
     * prepare table reference
     *
     * @param \Cube\Db\Table\AbstractTable $dependentTable
     * @param \Cube\Db\Table\AbstractTable $parentTable
     * @param string                       $ruleKey
     *
     * @return array
     */
    protected function _prepareReference(AbstractTable $dependentTable, AbstractTable $parentTable, $ruleKey)
    {
        $parentTableName = self::FQN_SEP . get_class($parentTable);

        $map = $dependentTable->getReference($parentTableName, $ruleKey);

        if (!isset($map[AbstractTable::REF_COLUMNS])) {
            $parentInfo = $parentTable->info();
            $map[AbstractTable::REF_COLUMNS] = array_values((array)$parentInfo['primary']);
        }

        $map[AbstractTable::COLUMNS] = (array)$map[AbstractTable::COLUMNS];
        $map[AbstractTable::REF_COLUMNS] = (array)$map[AbstractTable::REF_COLUMNS];

        return $map;
    }

    /*
     * methods needed to implement the ArrayAccess and IteratorAggregate interfaces
     */

    /**
     * check whether a offset exists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     *
     * get offset
     *
     * @param mixed $offset
     *
     * @return mixed|null|string
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     *
     * set offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     *
     * unset offset
     *
     * @param mixed $offset
     *
     * @return $this|void
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    /**
     *
     * get iterator
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator((array)$this->_data);
    }

    /**
     *
     * count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count((array)$this->_data);
    }

}

