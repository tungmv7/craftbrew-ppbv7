<?php

/**
 *
 * Cube Framework $Id$ A6xjaTZuJYefk2WJyilNGP1RZM4JGESe0mR8uEx+RmI=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.0
 */
/**
 * db statement abstract class
 */

namespace Cube\Db\Statement;

use Cube\Db,
    Cube\Db\Adapter\AbstractAdapter;

abstract class AbstractStatement implements StatementInterface
{

    /**
     *
     * driver level statement resource
     *
     * @var resource|object
     */
    protected $_stmt = null;

    /**
     *
     * database adapter
     *
     * @var \Cube\Db\Adapter\AbstractAdapter
     */
    protected $_adapter = null;

    /**
     *
     * The current fetch mode.
     *
     * @var integer
     */
    protected $_fetchMode = Db::FETCH_ASSOC;

    /**
     *
     * statement attributes
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     *
     * column result bindings
     *
     * @var array
     */
    protected $_bindColumn = array();

    /**
     *
     * query parameter bindings
     *
     * @var array
     */
    protected $_bindParam = array();

    /**
     *
     * class constructor
     *
     * @param \Cube\Db\Adapter\AbstractAdapter $adapter
     * @param mixed                            $sql a string or \Cube\Db\Select
     */
    public function __construct(AbstractAdapter $adapter, $sql)
    {
        $this->_adapter = $adapter;

        if ($sql instanceof Db\Select) {
            $sql = $sql->assemble();
        }

        $this->_prepare($sql);
    }

    /**
     *
     * the method will be implemented at the driver level
     *
     * @param mixed $sql
     *
     * @return void
     */
    protected function _prepare($sql)
    {
        return;
    }

}

