<?php

/**
 *
 * Cube Framework $Id$ tGvOVvVQcScPvxR9FXm9QnJGc3Gywwtd8JyVz1nZ7N4=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Db\Table;

use Cube\Db,
    Cube\Db\Select,
    Cube\Db\Adapter\AbstractAdapter,
    Cube\Controller\Front;

/**
 * abstract table class
 *
 * Class AbstractTable
 *
 * @package Cube\Db\Table
 */
abstract class AbstractTable
{
    /**
     * class constants
     */

    const NAME = 'name';
    const COLS = 'cols';
    const PRIMARY = 'primary';
    const METADATA = 'metadata';
    const REFERENCE_MAP = 'referenceMap';
    const DEPENDENT_TABLES = 'dependentTables';
    const COLUMNS = 'columns';
    const REF_TABLE_CLASS = 'refTableClass';
    const REF_COLUMNS = 'refColumns';

    /**
     *
     * table name
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * table prefix
     * (set in configuration)
     *
     * @var string
     */
    protected $_prefix;

    /**
     *
     * database adapter
     *
     * @var \Cube\Db\Adapter\AbstractAdapter
     */
    protected $_adapter;

    /**
     * table column names derived from
     * \Cube\Db\Adapter\AbstractAdapter::describeTable()
     *
     * @var array
     */
    protected $_cols = null;

    /**
     *
     * primary key column(s)
     * A compound key should be declared as an array
     *
     * @var string|array
     */
    protected $_primary = null;

    /**
     *
     * information provided by the adapter's describeTable() method
     *
     * @var array
     */
    protected $_metadata = array();

    /**
     *
     * class name for row
     *
     * @var string
     */
    protected $_rowClass = '\Cube\Db\Table\Row';

    /**
     *
     * class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Cube\Db\Table\Rowset';

    /**
     * Associative array map of declarative referential integrity rules.
     * This array has one entry per foreign key in the current table.
     * Each key is a mnemonic name for one reference rule.
     *
     * Each value is also an associative array, with the following keys:
     * - columns       = array of names of column(s) in the child table.
     * - refTableClass = class name of the parent table.
     * - refColumns    = array of names of column(s) in the parent table,
     *                   in the same order as those in the 'columns' entry.
     * - onDelete      = "cascade" means that a delete in the parent table also
     *                   causes a delete of referencing rows in the child table.
     * - onUpdate      = "cascade" means that an update of primary key values in
     *                   the parent table also causes an update of referencing
     *                   rows in the child table.
     *
     * @var array
     */
    protected $_referenceMap = array();

    /**
     * Simple array of class names of tables that are "children" of the current
     * table, in other words tables that contain a foreign key to this one.
     * Array elements are not table names; they are class names of classes that
     * extend Zend_Db_Table_Abstract.
     *
     * @var array
     */
    protected $_dependentTables = array();

    /**
     *
     * cache object
     *
     * @var \Cube\Cache|false       false if caching is disabled in the application
     */
    protected $_cache = false;

    /**
     *
     * class constructor
     *
     * @param \Cube\Db\Adapter\AbstractAdapter $adapter
     *
     * @throws \RuntimeException
     */
    public function __construct(AbstractAdapter $adapter = null)
    {
        $bootstrap = Front::getInstance()->getBootstrap();

        $this->_cache = $bootstrap->getResource('cache');

        if ($adapter === null) {
            $adapter = $bootstrap->getResource('db');
        }

        if (!$adapter instanceof AbstractAdapter) {
            throw new \RuntimeException("Could not create table. 
                The database adapter must be an instance of \Cube\Db\Adapter\AbstractAdapter");
        }

        if (empty($this->_name)) {
            $this->_name = strtolower(get_class());
        }

        $adapterConfig = $adapter->getConfig();

        if (isset($adapterConfig['prefix'])) {
            $this->_prefix = $adapterConfig['prefix'];
        }

        $this->setAdapter($adapter);
    }

    /**
     *
     * get table name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * get table prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     *
     * set table prefix
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix = null)
    {
        $this->_prefix = $prefix;

        return $this;
    }

    /**
     *
     * get database adapter
     *
     * @return \Cube\Db\Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     *
     * set database adapter
     *
     * @param \Cube\Db\Adapter\AbstractAdapter $adapter
     *
     * @return \Cube\Db\Table\AbstractTable
     * @throws \RuntimeException
     */
    public function setAdapter($adapter)
    {
        if (!$adapter instanceof AbstractAdapter) {
            throw new \RuntimeException("Could not create table. 
                The database adapter must be an instance of \Cube\Db\Adapter\AbstractAdapter");
        }

        $this->_adapter = $adapter;

        return $this;
    }

    /**
     *
     * get row object class
     *
     * @return string
     */
    public function getRowClass()
    {
        return $this->_rowClass;
    }

    /**
     *
     * set row object class
     *
     * @param string $rowClass
     *
     * @return $this
     */
    public function setRowClass($rowClass)
    {
        $this->_rowClass = (string)$rowClass;

        return $this;
    }

    /**
     *
     * get rowset object class
     *
     * @return string
     */
    public function getRowsetClass()
    {
        return $this->_rowsetClass;
    }

    /**
     *
     * set rowset object class
     *
     * @param string $rowsetClass
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function setRowsetClass($rowsetClass)
    {
        $this->_rowsetClass = (string)$rowsetClass;

        return $this;
    }

    /**
     *
     * get the reference between the table and a requested table
     *
     * @param string $refTableClass
     * @param string $ruleKey
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getReference($refTableClass, $ruleKey = null)
    {
        if ($ruleKey !== null) {
            if (!isset($this->_referenceMap[$ruleKey])) {
                throw new \RuntimeException(
                    sprintf("A reference rule with the name '%s' does not exist in the definition of '%s'.", $ruleKey,
                        get_class($this)));
            }
            if ($this->_referenceMap[$ruleKey][self::REF_TABLE_CLASS] != $refTableClass) {
                throw new \RuntimeException(
                    sprintf("The reference rule '%s' does not reference the table '%s'.", $ruleKey, $refTableClass));
            }

            return $this->_referenceMap[$ruleKey];
        }


        foreach ($this->_referenceMap as $reference) {
            if ($reference[self::REF_TABLE_CLASS] == $refTableClass) {
                return $reference;
            }
        }

        throw new \RuntimeException(
            sprintf("There is no reference from table '%s' to table '%s'.", get_class($this), $refTableClass));
    }

    /**
     *
     * add a reference to the reference map of the table
     *
     * @param string $ruleKey
     * @param mixed  $columns
     * @param string $refTableClass
     * @param mixed  $refColumns
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function setReference($ruleKey, $columns, $refTableClass, $refColumns)
    {
        $reference = array(self::COLUMNS         => (array)$columns,
                           self::REF_TABLE_CLASS => $refTableClass,
                           self::REF_COLUMNS     => (array)$refColumns);


        $this->_referenceMap[$ruleKey] = $reference;

        return $this;
    }

    /**
     *
     * set the reference map of the table
     *
     * @param array $referenceMap
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function setReferenceMap(array $referenceMap)
    {
        $this->_referenceMap = $referenceMap;

        return $this;
    }

    /**
     *
     * get dependent tables
     *
     * @return array
     */
    public function getDependentTables()
    {
        return $this->_dependentTables;
    }

    /**
     *
     * set dependent tables
     *
     * @param array $dependentTables
     *
     * @return \Cube\Db\Table\AbstractTable
     */
    public function setDependentTables(array $dependentTables)
    {
        $this->_dependentTables = $dependentTables;

        return $this;
    }

    /**
     *
     * create an instance of the select object
     *
     * @param  array|string|\Cube\Db\Expr $cols The columns to select from this table.
     *
     * @return \Cube\Db\Select
     */
    public function select($cols = '*')
    {

        $select = new Select($this->_adapter);
        $select->setPrefix($this->getPrefix())
            ->from($this->_name, $cols);

        return $select;
    }

    /**
     *
     * Inserts a table row with specified data.
     *
     * @param array $data Column-value pairs.
     *
     * @return int the id of the inserted column.
     */
    public function insert(array $data)
    {
        $this->_adapter->insert($this->_prefix . $this->_name, $data);

        return $this->lastInsertId();
    }

    /**
     *
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  array $data  Column-value pairs.
     * @param  mixed $where UPDATE WHERE clause(s).
     *
     * @return int          The number of affected rows.
     */
    public function update(array $data, $where)
    {
        return $this->_adapter->update($this->_prefix . $this->_name, $data, $where);
    }

    /**
     *
     * delete table rows based on a WHERE clause.
     *
     * @param  mixed $where DELETE WHERE clause(s).
     *
     * @return int          The number of affected rows.
     */
    public function delete($where)
    {
        return $this->_adapter->delete($this->_prefix . $this->_name, $where);
    }

    /**
     *
     * fetches all matched rows
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     *
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if (!$where instanceof Select) {
            $select = $this->select()
                ->where($where)
                ->order($order)
                ->limit($count, $offset);
        }
        else {
            $select = $where;
        }


        $cachedData = false;
        $cacheFile = null;
        $rows = null;

        $cacheQueries = $this->_getCache('cacheQueries');
        if ($cacheQueries !== false) {
            $cacheFile = md5($select->assemble());
        }

        if ($cacheQueries !== false) {
            if (($data = $this->_cache->read($cacheFile)) !== false) {
                $rows = $data;
                $cachedData = true;
            }
        }

        if ($cachedData === false) {
            $stmt = $this->_adapter->query($select);
            $rows = $stmt->fetchAll(Db::FETCH_ASSOC);

            if ($cacheQueries !== false) {
                $this->_cache->write($cacheFile, $rows);
            }
        }

        $data = array(
            'table' => $this,
            'data'  => $rows,
        );

        return new $this->_rowsetClass($data);
    }

    /**
     *
     * fetch a single matched row from a result set
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $offset
     *
     * @return \Cube\Db\Table\Row\AbstractRow|null
     */
    public function fetchRow($where = null, $order = null, $offset = null)
    {
//        $row = $this->fetchAll($where, $order, 1, $offset)->toArray();
//
//        $data = array(
//            'table' => $this,
//            'data'  => (isset($row[0])) ? $row[0] : null,
//        );
//
//        return new $this->_rowClass($data);
        return $this->fetchAll($where, $order, 1, $offset)->getRow(0);
    }

    /**
     *
     * get the id resulted from an insert operation
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->_adapter->lastInsertId();
    }

    /**
     *
     * returns table information
     *
     * @param  string $key specific info part to return
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function info($key = null)
    {
        $this->_getPrimary();

        $info = array(
            self::NAME             => $this->_name,
            self::COLS             => $this->_getCols(),
            self::PRIMARY          => (array)$this->_primary,
            self::METADATA         => $this->_metadata,
            self::REFERENCE_MAP    => $this->_referenceMap,
            self::DEPENDENT_TABLES => $this->_dependentTables,
        );

        if ($key === null) {
            return $info;
        }

        if (!array_key_exists($key, $info)) {
            throw new \InvalidArgumentException(
                sprintf("There is no table information for the key '%s'.", $key));
        }

        return $info[$key];
    }

    /**
     *
     * get table metadata
     * use cache if caching is enabled
     *
     * @return array
     */
    protected function _getMetadata()
    {
        if (!count($this->_metadata)) {
            $cachedData = false;
            $cacheMetadata = $this->_getCache('cacheMetadata');
            $cacheFile = null;

            if ($cacheMetadata !== false) {
                $cacheFile = md5("DESCRIBE " . $this->_prefix . $this->_name);
            }

            if ($cacheMetadata !== false) {
                if (($data = $this->_cache->read($cacheFile)) !== false) {
                    $this->_metadata = $data;
                    $cachedData = true;
                }
            }

            if ($cachedData === false) {
                $this->_metadata = $this->_adapter->describeTable($this->_prefix . $this->_name);

                if ($cacheMetadata !== false) {
                    $this->_cache->write($cacheFile, $this->_metadata);
                }
            }
        }

        return $this->_metadata;
    }

    /**
     *
     * get cache
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function _getCache($key = null)
    {
        if ($this->_cache !== false) {
            if ($key === null) {
                return $this->_cache;
            }
            else {
                $methodName = 'get' . ucfirst($key);

                if (method_exists($this->_cache, $methodName)) {
                    return $this->_cache->$methodName();
                }
            }
        }

        return false;
    }

    /**
     *
     * get table columns
     *
     * @return array
     */
    protected function _getCols()
    {
        if ($this->_cols === null) {
            $this->_cols = array_keys(
                $this->_getMetadata());
        }

        return $this->_cols;
    }

    /**
     *
     * get primary key(s)
     *
     * @throws \RuntimeException
     * @return array
     */
    protected function _getPrimary()
    {
        if (!$this->_primary) {
            $this->_getMetadata();
            $this->_primary = array();
            foreach ($this->_metadata as $col) {
                if ($col['PRIMARY']) {
                    $this->_primary[$col['PRIMARY_POSITION']] = $col['COLUMN_NAME'];
                }
            }
        }
        else if (!is_array($this->_primary)) {
            $this->_primary = array(1 => $this->_primary);
        }
        else if (isset($this->_primary[0])) {
            array_unshift($this->_primary, null);
            unset($this->_primary[0]);
        }

        $cols = $this->_getCols();
        if (!array_intersect((array)$this->_primary, $cols) == (array)$this->_primary) {
            throw new \RuntimeException(
                sprintf("Invalid primary key column(s): %s.", implode(',', (array)$this->_primary)));
        }

        return $this->_primary;
    }

}

