<?php

/**
 *
 * PHP Pro Bid $Id$ 9bUh4HKXtLk6u+owabHpYEI1KiXFZyCAVmmFnrJ9cjI=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */

namespace Install\Model;

use Cube\Db\Adapter\AbstractAdapter,
    Cube\Db\Adapter\PDO\Mysql as DatabaseAdapter;
use Cube\Db;

class Parser
{
    /**
     *
     * array of placeholders to be substituted with valid values in the sql file
     *
     * @var array
     */
    protected $_placeholders = array();

    /**
     *
     * path to the sql file to be parsed
     *
     * @var string
     */
    protected $_filePath;

    /**
     *
     * stop parsing on error
     *
     * @var bool
     */
    protected $_stopOnError = false;

    /**
     *
     * parsing errors
     *
     * @var array
     */
    protected $_errors = array();

    /**
     *
     * parsed queries
     *
     * @var array
     */
    protected $_queries = array();

    /**
     *
     * database adapter
     *
     * @var \Cube\Db\Adapter\AbstractAdapter
     */
    protected $_adapter;
    /**
     *
     * database credentials
     *
     * @var array
     */
    protected $_dbCredentials = array(
        'host'           => null,
        'dbname'         => null,
        'username'       => null,
        'password'       => null,
        'prefix'         => null,
        'charset'        => 'utf8',
        'driver_options' => null,
    );


    /**
     *
     * set placeholders array
     *
     * @param array $placeholders
     *
     * @return $this
     */
    public function setPlaceholders(array $placeholders)
    {
        foreach ($placeholders as $key => $value) {
            $this->addPlaceholder($key, $value);
        }

        return $this;
    }

    /**
     *
     * add single placeholder
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addPlaceholder($key, $value)
    {
        $this->_placeholders[$key] = $value;

        return $this;
    }

    /**
     *
     * get placeholders
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->_placeholders;
    }

    /**
     *
     * clear placeholders array
     *
     * @return $this
     */
    public function clearPlaceholders()
    {
        $this->_placeholders = array();

        return $this;
    }

    /**
     *
     * replace placeholders in query
     *
     * @param $query
     *
     * @return mixed
     */
    public function replacePlaceholdersInQuery($query)
    {
        return str_replace(array_keys($this->_placeholders), array_values($this->_placeholders), $query);
    }

    /**
     *
     * set file path
     *
     * @param string $filePath
     *
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->_filePath = $filePath;

        return $this;
    }

    /**
     *
     * get file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->_filePath;
    }

    /**
     *
     * set stop on error flag
     *
     * @param boolean $stopOnError
     *
     * @return $this
     */
    public function stopOnError($stopOnError = true)
    {
        $this->_stopOnError = (bool)$stopOnError;

        return $this;
    }

    /**
     *
     * get stop on error flag
     *
     * @return boolean
     */
    public function getStopOnError()
    {
        return $this->_stopOnError;
    }

    /**
     *
     * add new parsing error
     *
     * @param $error
     *
     * @return $this
     */
    public function addError($error)
    {
        $this->_errors[] = $error;

        return $this;
    }

    /**
     *
     * get parsing errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     *
     * add query in queries array
     * also replace any placeholders
     *
     * @param string  $query
     * @param boolean $replacePlaceholders
     *
     * @return $this
     */
    public function addQuery($query, $replacePlaceholders = false)
    {
        if ($replacePlaceholders === true) {
            $query = $this->replacePlaceholdersInQuery($query);
        }

        $this->_queries[] = $query;

        return $this;
    }

    /**
     *
     * get queries array
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->_queries;
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
     * @return $this
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
     * set database credentials
     *
     * @param array $dbCredentials
     *
     * @return $this
     */
    public function setDbCredentials($dbCredentials)
    {
        foreach ($dbCredentials as $key => $value) {
            $this->addDbCredential($key, $value);
        }

        return $this;
    }

    /**
     *
     * add database credential
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addDbCredential($key, $value)
    {
        if (!array_key_exists($key, $this->_dbCredentials)) {
            $this->addError(
                sprintf("DB credential not allowed: '%s'", $key));
        }
        else {
            $this->_dbCredentials[$key] = $value;
        }

        return $this;
    }

    /**
     *
     * get database credentials
     *
     * @return array
     */
    public function getDbCredentials()
    {
        return $this->_dbCredentials;
    }


    /**
     *
     * parse sql file and run sql queries if the run flag is set to true
     *
     * @param bool $run
     *
     * @return bool
     */
    public function parse($run = false)
    {
        $return = true;

        if (!file_exists($this->_filePath)) {
            $this->addError(
                sprintf("The file to be parsed, '%s', could not be found.", $this->_filePath)
            );

            return false;
        }

        if ($run) {
            if (!$this->_adapter instanceof DatabaseAdapter) {
                $this->setAdapter(
                    new DatabaseAdapter($this->_dbCredentials));
            }

            if (!$this->_adapter->canConnect()) {
                $this->addError("Could not connect to the database with the credentials you have provided.");

                return false;
            }
        }

        // Temporary variable, used to store current query
        $tmpQuery = '';
        // Read in entire file
        $lines = file($this->_filePath);
        // Loop through each line
        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || substr($line, 0, 1) == '#' || $line == '')
                continue;

            // Add this line to the current segment
            $tmpQuery .= $line;

            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                $query = $this->replacePlaceholdersInQuery($tmpQuery);
                $this->addQuery($query);

                if ($run) {
                    // Perform the query
                    try {
                        $stmt = $this->_adapter->query($query);
                    } catch (\Exception $e) {
                        $this->addError($e->getMessage());

                        if ($this->_stopOnError) {
                            return false;
                        }
                        $return = false;
                    }
                }

                $tmpQuery = '';
            }
        }

        return $return;


    }

}