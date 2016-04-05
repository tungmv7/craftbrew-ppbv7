<?php

/**
 *
 * Cube Framework $Id$ IQbgSuRICdAseA1KEKKb33BkfZ7le0nnahNdPn0Fwng=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.5
 */
/**
 * pdo statement class = proxy to \PDOStatement
 */

namespace Cube\Db\Statement;

use Cube\Exception;

class Pdo extends AbstractStatement
{
    /**
     *
     * statement resource
     *
     * @var \PDOStatement
     */
    protected $_stmt = null;
    /**
     *
     * fetch mode
     *
     * @var int
     */
    protected $_fetchMode = \PDO::FETCH_ASSOC;

    /**
     *
     * prepare a string SQL statement and create a statement object.
     *
     * @param string $sql
     *
     * @return void
     * @throws \Cube\Exception
     */
    protected function _prepare($sql)
    {
        try {
            $this->_stmt = $this->_adapter->getConnection()->prepare($sql);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * bind a column to a php variable
     *
     * @param string $column name or position of the column in the result set
     * @param mixed  $param  reference to the php variable to which the column will be bound
     * @param int    $type   data type of the parameter
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function bindColumn($column, &$param, $type = null)
    {
        $this->_bindColumn[$column] = &$param;

        try {
            return $this->_stmt->bindColumn($column, $param, $type);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

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
     * @throws \InvalidArgumentException
     * @throws \Cube\Exception
     * @return bool
     */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        if (!is_int($parameter) && !is_string($parameter)) {
            throw new \InvalidArgumentException('Invalid bind-variable position');
        }

        $this->_bindParam[$parameter] = &$variable;

        try {
            if ($type === null) {
                if (is_bool($variable)) {
                    $type = \PDO::PARAM_BOOL;
                }
                elseif ($variable === null) {
                    $type = \PDO::PARAM_NULL;
                }
                elseif (is_integer($variable)) {
                    $type = \PDO::PARAM_INT;
                }
                else {
                    $type = \PDO::PARAM_STR;
                }
            }

            return $this->_stmt->bindParam($parameter, $variable, $type, $length, $options);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * binds a value to a parameter
     *
     * @param int|string $parameter parameter identifier
     * @param mixed      $value     the value to bind to the parameter
     * @param string     $type      data type of the parameter
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function bindValue($parameter, &$value, $type = null)
    {
        if (is_string($parameter) && $parameter[0] != ':') {
            $parameter = ":$parameter";
        }

        $this->_bindParam[$parameter] = $value;

        try {
            return $this->_stmt->bindValue($parameter, $value, $type);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * closes the cursor, allowing the statement to be executed again
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function closeCursor()
    {
        try {
            return $this->_stmt->closeCursor();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * returns the number of columns in the result set
     *
     * @return int
     * @throws \Cube\Exception
     */
    public function columnCount()
    {
        try {
            return $this->_stmt->columnCount();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * fetch the error code associated with the last operation on the statement handle
     *
     * @return string
     * @throws \Cube\Exception
     */
    public function errorCode()
    {
        try {
            return $this->_stmt->errorCode();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * fetch extended error information associated with the last operation on the statement handle
     *
     * @return array
     * @throws \Cube\Exception
     */
    public function errorInfo()
    {
        try {
            return $this->_stmt->errorCode();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * execute a prepared statement
     *
     * @param array $params array of values with as many elements as there are bound parameters in the SQL statement being executed
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function execute(array $params = null)
    {
        try {
            return $this->_stmt->execute($params);
        } catch (\PDOException $e) {
            $errorMessage = $e->getMessage() . ' [Query]: ' . $this->_stmt->queryString;
            throw new Exception($errorMessage, $e->getCode());
        }
    }

    /**
     *
     * fetch the next row from the result set
     *
     * @param int $style  fetch mode
     * @param int $cursor scrollable cursor
     * @param int $offset number of cursors
     *
     * @return mixed
     * @throws \Cube\Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if ($style === null) {
            $style = $this->_fetchMode;
        }

        try {
            return $this->_stmt->fetch($style, $cursor, $offset);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * returns an array containing all of the result set rows
     *
     * @param int $style  fetch mode
     * @param int $column column number, if fetch mode is by column
     *
     * @return array        collection of rows, each in a format by fetch mode
     * @throws \Cube\Exception
     */
    public function fetchAll($style = null, $column = null)
    {
        if ($style === null) {
            $style = $this->_fetchMode;
        }

        try {
            if ($style === \PDO::FETCH_COLUMN) {
                return $this->_stmt->fetchAll($style, (int)$column);
            }
            else {
                return $this->_stmt->fetchAll($style);
            }
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * returns a single column from the next row of a result set
     *
     * @param int $column column number from the fetch row or the first column if no column is set
     *
     * @return string
     * @throws \Cube\Exception
     */
    public function fetchColumn($column = null)
    {
        try {
            return $this->_stmt->fetchColumn((int)$column);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * fetches the next row and returns it as an object
     *
     * @param string $class  name of the class to create
     * @param array  $config constructor arguments to add to the class
     *
     * @return mixed            one object instance of the specified class
     * @throws \Cube\Exception
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        try {
            return $this->_stmt->fetchObject($class, $config);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * retrieve a statement attribute
     *
     * @param string $attribute the attribute name
     *
     * @return mixed
     * @throws \Cube\Exception
     */
    public function getAttribute($attribute)
    {
        try {
            return $this->_stmt->getAttribute($attribute);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * advances to the next rowset in a multi-rowset statement handle
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function nextRowset()
    {
        try {
            return $this->_stmt->nextRowset();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * returns the number of rows affected by the last SQL statement
     *
     * @return int     the number of rows affected
     * @throws \Cube\Exception
     */
    public function rowCount()
    {
        try {
            return $this->_stmt->rowCount();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * set a statement attribute
     *
     * @param string $key   attribute name
     * @param mixed  $value attribute value
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;

        try {
            return $this->_stmt->setAttribute($key, $value);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     * set the default fetch mode for this statement.
     *
     * @param int $mode the fetch mode
     *
     * @return bool
     * @throws \Cube\Exception
     */
    public function setFetchMode($mode)
    {
        try {
            return $this->_stmt->setFetchMode($mode);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}

