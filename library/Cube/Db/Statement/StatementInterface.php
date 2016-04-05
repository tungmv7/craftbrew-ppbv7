<?php

/**
 *
 * Cube Framework $Id$ JWU5W01As1e9ibztcTsWVoMpMUSURTrBjfok/4MbEUA=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * db statement interface
 */

namespace Cube\Db\Statement;

interface StatementInterface
{

    /**
     *
     * bind a column to a php variable
     *
     * @param string $column name or position of the column in the result set
     * @param mixed  $param  reference to the php variable to which the column will be bound
     * @param int    $type   data type of the parameter
     *
     * @return bool
     */
    public function bindColumn($column, &$param, $type = null);

    /**
     *
     * binds a parameter to the specified variable name
     *
     * @param int|string $parameter parameter identifier
     * @param mixed      $variable  name of the PHP variable to bind to the SQL statement parameter
     * @param int        $type      data type of the parameter
     * @param int        $length    length of the data type
     * @param mixed      $options   other driver options
     *
     * @return bool
     */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null);

    /**
     *
     * binds a value to a parameter
     *
     * @param int|string $parameter parameter identifier
     * @param mixed      $value     the value to bind to the parameter
     * @param string     $type      data type of the parameter
     *
     * @return bool
     */
    public function bindValue($parameter, &$value, $type = null);

    /**
     *
     * closes the cursor, allowing the statement to be executed again
     *
     * @return bool
     */
    public function closeCursor();

    /**
     *
     * returns the number of columns in the result set
     *
     * @return int
     */
    public function columnCount();

    /**
     *
     * fetch the error code associated with the last operation on the statement handle
     *
     * @return string
     */
    public function errorCode();

    /**
     *
     * fetch extended error information associated with the last operation on the statement handle
     *
     * @return array
     */
    public function errorInfo();

    /**
     *
     * execute a prepared statement
     *
     * @param array $params array of values with as many elements as there are bound parameters in the SQL statement being executed
     *
     * @return bool
     */
    public function execute(array $params = array());

    /**
     *
     * fetch the next row from the result set
     *
     * @param int $style  fetch mode
     * @param int $cursor scrollable cursor
     * @param int $offset number of cursors
     *
     * @return mixed
     */
    public function fetch($style = null, $cursor = null, $offset = null);

    /**
     *
     * returns an array containing all of the result set rows
     *
     * @param int $style  fetch mode
     * @param int $column column number, if fetch mode is by column
     *
     * @return array        collection of rows, each in a format by fetch mode
     */
    public function fetchAll($style = null, $column = null);

    /**
     *
     * returns a single column from the next row of a result set
     *
     * @param int $column column number from the fetch row or the first column if no column is set
     *
     * @return string
     */
    public function fetchColumn($column = null);

    /**
     *
     * fetches the next row and returns it as an object
     *
     * @param string $class  name of the class to create
     * @param array  $config constructor arguments to add to the class
     *
     * @return mixed            one object instance of the specified class
     */
    public function fetchObject($class = 'stdClass', array $config = array());

    /**
     *
     * retrieve a statement attribute
     *
     * @param string $attribute the attribute name
     */
    public function getAttribute($attribute);

    /**
     *
     * advances to the next rowset in a multi-rowset statement handle
     *
     * @return bool
     */
    public function nextRowset();

    /**
     *
     * returns the number of rows affected by the last SQL statement
     *
     * @return int     the number of rows affected
     */
    public function rowCount();

    /**
     *
     * set a statement attribute
     *
     * @param string $key   attribute name
     * @param mixed  $value attribute value
     *
     * @return bool
     */
    public function setAttribute($key, $value);

    /**
     *
     * set the default fetch mode for this statement.
     *
     * @param int $mode the fetch mode
     *
     * @return bool
     */
    public function setFetchMode($mode);
}

