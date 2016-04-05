<?php

/**
 *
 * Cube Framework $Id$ 4Q3mpN4FcemnCF4XvK5GcanyzWk1/2No7JO+d6QAgP8=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.7
 */
/**
 * db select pagination adapter
 */

namespace Cube\Paginator\Adapter;

use Cube\Db\Select,
    Cube\Db\Expr;

class DbSelect implements AdapterInterface
{

    /**
     *
     * the select object
     *
     * @var \Cube\Db\Select
     */
    protected $_select;

    /**
     *
     * total number of rows
     *
     * @var integer
     */
    protected $_count = null;

    /**
     *
     * class constructor
     *
     * @param \Cube\Db\Select $select   the select object
     */
    public function __construct(Select $select)
    {
        $this->_select = $select;
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
        $this->_select->limit($itemCountPerPage, $offset);

        return $this->_select->query()->fetchAll();
    }

    /**
     *
     * return the number of rows in the result set
     * TODO: refactor for UNION and HAVING as this solution is slower
     *
     * @return integer
     */
    public function count()
    {
        if ($this->_count === null) {
            $select = clone $this->_select;

            $union = $select->getPart(Select::UNION);
            $having = $select->getPart(Select::HAVING);

            if (count($union) > 0 || count($having) > 0) {
                $stmt = $select->query();
                $this->_count = count($stmt->fetchAll());
            }
            else {
                $select->reset(Select::COLUMNS)
                    ->reset(Select::ORDER);

                $select->columns(array('nb_rows' => new Expr('count(*)')));

                $stmt = $select->query();

                if ($select->getPart(Select::GROUP)) {
                    $this->_count = count($stmt->fetchAll());
                }
                else {
                    $this->_count = (integer) $stmt->fetchColumn('nb_rows');
                }
            }
        }

        return $this->_count;
    }

}

