<?php

/**
 *
 * Ported from Zend Framework
 *
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Cube\Db\Adapter;

use Cube\Db,
    Cube\Db\Select,
    Cube\Config\AbstractConfig,
    Cube\Debug;

abstract class AbstractAdapter
{

    /**
     * User-provided configuration
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Fetch mode
     *
     * @var integer
     */
    protected $_fetchMode = Db::FETCH_ASSOC;

    /**
     * Database connection
     *
     * @var object|resource|null
     */
    protected $_connection = null;

    /**
     * Keys are UPPERCASE SQL data types or the constants
     * \Cube\Db::INT_TYPE, \Cube\Db::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = float or decimal
     *
     * @var array Associative array of data types to values 0 or 1
     */
    protected $_numericDataTypes = array(
        Db::INT_TYPE   => Db::INT_TYPE,
        Db::FLOAT_TYPE => Db::FLOAT_TYPE,
    );

    /**
     * Constructor.
     *
     * $config is an array of key/value pairs or an instance of \Cube\ConfigAbstract
     * containing configuration options.  These options are common to most adapters:
     *
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * @param  array|\Cube\Config\AbstractConfig $config An array or instance of \Cube\ConfigAbstract having configuration data
     *
     * @throws \RuntimeException
     */
    public function __construct($config)
    {
        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            if ($config instanceof AbstractConfig) {
                $config = $config->getData();
            }
            else {
                throw new \RuntimeException('Adapter parameters must be in an array or a \Cube\ConfigAbstract object');
            }
        }

        $this->_checkRequiredOptions($config);

        if (!isset($config['charset'])) {
            $config['charset'] = null;
        }

        $this->_config = array_merge($this->_config, $config);
    }

    /**
     *
     * Check for config options that are mandatory.
     * Throw exceptions if any are missing.
     *
     * @param array $config
     *
     * @throws \RuntimeException
     */
    protected function _checkRequiredOptions(array $config)
    {
        // we need at least a dbname
        if (!array_key_exists('dbname', $config)) {
            throw new \RuntimeException("Configuration array must have a key 
                for 'dbname' that names the database instance");
        }

        if (!array_key_exists('password', $config)) {
            throw new \RuntimeException("Configuration array must have a key 
                for 'password' for login credentials");
        }

        if (!array_key_exists('username', $config)) {
            throw new \RuntimeException("Configuration array must have a key 
                for 'username' for login credentials");
        }
    }

    /**
     *
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return object|resource|null
     */
    public function getConnection()
    {
        $this->_connect();

        return $this->_connection;
    }

    /**
     *
     * Returns the configuration variables in this adapter.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     *
     * Prepares and executes an SQL statement with bound data.
     *
     * @param mixed $sql    the SQL statement with placeholders.
     *                      May be a string or \Cube\Db\Select
     * @param mixed $bind   an array of data to bind to the placeholders.
     *
     * @return \Cube\Db\Statement\StatementInterface
     */
    public function query($sql, $bind = array())
    {
        $this->_connect();

        if ($sql instanceof Select) {
            if (empty($bind)) {
                $bind = $sql->getBind();
            }

            $sql = $sql->assemble();
        }

        if (!is_array($bind)) {
            $bind = array($bind);
        }

        // query profiler - start 
        $debugCount = Debug::getSqlCount();
        Debug::addSqlQuery($sql, $debugCount);

        // prepare and execute the statement with profiling
        $stmt = $this->prepare($sql);
        $stmt->execute($bind);


        // query profiler - end
        Debug::addSqlQuery($sql, $debugCount);
        Debug::addSqlCount();

        $stmt->setFetchMode($this->_fetchMode);


        return $stmt;
    }

    /**
     *
     * leave autocommit mode and begin a transaction.
     *
     * @return \Cube\Db\Adapter\AbstractAdapter
     */
    public function beginTransaction()
    {
        $this->_connect();
        $this->_beginTransaction();

        return $this;
    }

    /**
     * Commit a transaction and return to autocommit mode.
     *
     * @return \Cube\Db\Adapter\AbstractAdapter
     */
    public function commit()
    {
        $this->_connect();
        $this->_commit();

        return $this;
    }

    /**
     * Roll back a transaction and return to autocommit mode.
     *
     * @return \Cube\Db\Adapter\AbstractAdapter
     */
    public function rollBack()
    {
        $this->_connect();
        $this->_rollBack();

        return $this;
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind  Column-value pairs.
     *
     * @return int The number of affected rows.
     * @throws \RuntimeException
     */
    public function insert($table, array $bind)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col, true);
            if ($val instanceof Db\Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            }
            else {
                if ($this->supportsParameters('positional')) {
                    $vals[] = '?';
                }
                else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col' . $i] = $val;
                        $vals[] = ':col' . $i;
                        $i++;
                    }
                    else {
                        throw new \RuntimeException(
                            sprintf("%s doesn't support positional or named binding", get_class($this)));
                    }
                }
            }
        }

        // build the statement
        $sql = "INSERT INTO "
            . $this->quoteIdentifier($table, true)
            . ' (' . implode(', ', $cols) . ') '
            . 'VALUES (' . implode(', ', $vals) . ')';

        // execute the statement and return the number of affected rows
        if ($this->supportsParameters('positional')) {
            $bind = array_values($bind);
        }

        $stmt = $this->query($sql, $bind);
        $result = $stmt->rowCount();

        return $result;
    }

    /**
     *
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  array $bind  Column-value pairs.
     * @param  mixed $where UPDATE WHERE clause(s).
     *
     * @return int          The number of affected rows.
     * @throws \RuntimeException
     */
    public function update($table, array $bind, $where = '')
    {
        /**
         * Build "col = ?" pairs for the statement,
         * except for \Cube\Db\Expr which is treated literally.
         */
        $set = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            if ($val instanceof Db\Expr) {
                $val = $val->__toString();
                unset($bind[$col]);
            }
            else {
                if ($this->supportsParameters('positional')) {
                    $val = '?';
                }
                else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col' . $i] = $val;
                        $val = ':col' . $i;
                        $i++;
                    }
                    else {
                        throw new \RuntimeException(
                            sprintf("%s doesn't support positional or named binding", get_class($this)));
                    }
                }
            }
            $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
        }

        $where = $this->_whereExpr($where);

        /**
         * Build the UPDATE statement
         */
        $sql = "UPDATE "
            . $this->quoteIdentifier($table, true)
            . ' SET ' . implode(', ', $set)
            . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        if ($this->supportsParameters('positional')) {
            $stmt = $this->query($sql, array_values($bind));
        }
        else {
            $stmt = $this->query($sql, $bind);
        }

        $result = $stmt->rowCount();

        return $result;
    }

    /**
     *
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed $table The table to update.
     * @param  mixed $where DELETE WHERE clause(s).
     *
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '')
    {
        $where = $this->_whereExpr($where);

        /**
         * Build the DELETE statement
         */
        $sql = "DELETE FROM "
            . $this->quoteIdentifier($table, true)
            . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt = $this->query($sql);
        $result = $stmt->rowCount();

        return $result;
    }

    /**
     *
     * Convert an array, string, or \Cube\Db\Expr object
     * into a string to put in a WHERE clause.
     *
     * @param mixed $where
     *
     * @return string
     */
    protected function _whereExpr($where)
    {
        if (empty($where)) {
            return $where;
        }
        if (!is_array($where)) {
            $where = array($where);
        }
        foreach ($where as $cond => &$term) {
            // is $cond an int? (i.e. Not a condition)
            if (is_int($cond)) {
                // $term is the full condition
                if ($term instanceof Db\Expr) {
                    $term = $term->__toString();
                }
            }
            else {
                // $cond is the condition with placeholder,
                // and $term is quoted into the condition
                $term = $this->quoteInto($cond, $term);
            }
            $term = '(' . $term . ')';
        }

        $where = implode(' AND ', $where);

        return $where;
    }

    /**
     * Creates and returns a new \Cube\Db\Select object for this adapter.
     *
     * @return \Cube\Db\Select
     */
    public function select()
    {
        return new Select($this);
    }

    /**
     * Get the fetch mode.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return $this->_fetchMode;
    }

    /**
     *
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Cube\Db\Select $sql       An SQL SELECT statement.
     * @param mixed                  $bind      Data to bind into SELECT placeholders.
     * @param mixed                  $fetchMode Override current fetch mode.
     *
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetchAll($fetchMode);

        return $result;
    }

    /**
     *
     * Fetches the first row of the SQL result.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|\Cube\Db\Select $sql       An SQL SELECT statement.
     * @param mixed                  $bind      Data to bind into SELECT placeholders.
     * @param mixed                  $fetchMode Override current fetch mode.
     *
     * @return array
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetch($fetchMode);

        return $result;
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     *
     * @param string|\Cube\Db\Select $sql  An SQL SELECT statement.
     * @param mixed                  $bind Data to bind into SELECT placeholders.
     *
     * @return string
     */
    public function fetchOne($sql, $bind = array())
    {
        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetchColumn(0);

        return $result;
    }

    /**
     * Quote a raw string.
     *
     * @param string $value Raw string
     *
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        if (is_int($value)) {
            return $value;
        }
        elseif (is_float($value)) {
            return sprintf('%F', $value);
        }

        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string.
     *
     * @param mixed $value The value to quote.
     * @param mixed $type  OPTIONAL the SQL datatype name, or constant, or null.
     *
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null)
    {
        $this->_connect();

        if ($value instanceof Select) {
            return '(' . $value->assemble() . ')';
        }

        if ($value instanceof Db\Expr) {
            return $value->__toString();
        }

        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val, $type);
            }

            return implode(', ', $value);
        }

        if ($type !== null && array_key_exists($type = strtoupper($type), $this->_numericDataTypes)) {
            $quotedValue = '0';
            switch ($this->_numericDataTypes[$type]) {
                case Db::INT_TYPE: // 32-bit integer
                    $quotedValue = (string)intval($value);
                    break;
                case Db::FLOAT_TYPE: // float or decimal
                    $quotedValue = sprintf('%F', $value);
            }

            return $quotedValue;
        }

        return $this->_quote($value);
    }

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example:
     *
     * <code>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </code>
     *
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     *
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        }
        else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }

            return $text;
        }
    }

    /**
     * Quotes an identifier.
     *
     * Accepts a string representing a qualified identifier. For Example:
     * <code>
     * $adapter->quoteIdentifier('myschema.mytable')
     * </code>
     * Returns: "myschema"."mytable"
     *
     * Or, an array of one or more identifiers that may form a qualified identifier:
     * <code>
     * $adapter->quoteIdentifier(array('myschema','my.table'))
     * </code>
     * Returns: "myschema"."my.table"
     *
     * The actual quote character surrounding the identifiers may vary depending on
     * the adapter.
     *
     * @param string|array|\Cube\Db\Expr $ident The identifier.
     * @param bool                       $auto
     *
     * @return string The quoted identifier.
     */
    public function quoteIdentifier($ident, $auto = false)
    {
        return $this->_quoteIdentifierAs($ident, null, $auto);
    }

    /**
     * Quote a column identifier and alias.
     *
     * @param string|array|\Cube\Db\Expr $ident The identifier or expression.
     * @param string                     $alias An alias for the column.
     * @param bool                       $auto
     *
     * @return string The quoted identifier and alias.
     */
    public function quoteColumnAs($ident, $alias, $auto = false)
    {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|\Cube\Db\Expr $ident The identifier or expression.
     * @param string                     $alias An alias for the table.
     * @param bool                       $auto  If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     *
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null, $auto = false)
    {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

    /**
     * Quote an identifier and an optional alias.
     *
     * @param string|array|\Cube\Db\Expr $ident The identifier or expression.
     * @param string                     $alias An optional alias.
     * @param bool                       $auto  If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @param string                     $as    The string to add between the identifier/expression and the alias.
     *
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        if ($ident instanceof Db\Expr) {
            $quoted = $ident->__toString();
        }
        elseif ($ident instanceof Select) {
            $quoted = '(' . $ident->assemble() . ')';
        }
        else {
            if (is_string($ident)) {
                $ident = explode('.', $ident);
            }
            if (is_array($ident)) {
                $segments = array();
                foreach ($ident as $segment) {
                    if ($segment instanceof Db\Expr) {
                        $segments[] = $segment->__toString();
                    }
                    else {
                        $segments[] = $this->_quoteIdentifier($segment, $auto);
                    }
                }
                if ($alias !== null && end($ident) == $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            }
            else {
                $quoted = $this->_quoteIdentifier($ident, $auto);
            }
        }
        if ($alias !== null) {
            $quoted .= $as . $this->_quoteIdentifier($alias, $auto);
        }

        return $quoted;
    }

    /**
     * Quote an identifier.
     *
     * @param  string $value The identifier or expression.
     * @param bool    $auto  If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     *
     * @return string        The quoted identifier and alias.
     */
    protected function _quoteIdentifier($value, $auto = false)
    {
        if ($auto === false) {
            $q = $this->getQuoteIdentifierSymbol();

            return ($q . str_replace("$q", "$q$q", $value) . $q);
        }

        return $value;
    }

    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '"';
    }

    /**
     * Return the most recent value from the specified sequence in the database.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @return string
     */
    public function lastSequenceId()
    {
        return null;
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @return string
     */
    public function nextSequenceId()
    {
        return null;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * TABLE_NAME  => string;
     * COLUMN_NAME => string; column name
     * COLUMN_POSITION => number; ordinal position of column in table
     * DATA_TYPE   => string; SQL datatype name of column
     * DEFAULT     => string; default expression of column, null if none
     * NULLABLE    => bool; true if column can have nulls
     * LENGTH      => number; length of CHAR/VARCHAR
     * SCALE       => number; scale of NUMERIC/DECIMAL
     * PRECISION   => number; precision of NUMERIC/DECIMAL
     * UNSIGNED    => bool; unsigned property of an integer type
     * PRIMARY     => bool; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     *
     * @param string $tableName
     *
     * @return array
     */
    abstract public function describeTable($tableName);

    /**
     * Creates a connection to the database.
     *
     * @return void
     */
    abstract protected function _connect();

    /**
     * Test if a connection is active
     *
     * @return bool
     */
    abstract public function isConnected();

    /**
     * Force the connection to close.
     *
     * @return void
     */
    abstract public function closeConnection();

    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param string|\Cube\Db\Select $sql SQL query
     *
     * @return \Cube\Db\Statement\AbstractStatement|\PDOStatement
     */
    abstract public function prepare($sql);

    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * @param string $tableName  OPTIONAL Name of table.
     * @param string $primaryKey OPTIONAL Name of primary key column.
     *
     * @return string
     */
    abstract public function lastInsertId($tableName = null, $primaryKey = null);

    /**
     * Begin a transaction.
     */
    abstract protected function _beginTransaction();

    /**
     * Commit a transaction.
     */
    abstract protected function _commit();

    /**
     * Roll-back a transaction.
     */
    abstract protected function _rollBack();

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     *
     * @return void
     */
    abstract public function setFetchMode($mode);

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param mixed   $sql
     * @param integer $count
     * @param integer $offset
     *
     * @return string
     */
    abstract public function limit($sql, $count, $offset = 0);

    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     *
     * @return bool
     */
    abstract public function supportsParameters($type);
}
