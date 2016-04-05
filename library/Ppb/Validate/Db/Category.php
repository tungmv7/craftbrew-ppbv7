<?php

/**
 *
 * PHP Pro Bid $Id$ 32ivxrCmaKvWJeQ6YMcg0BGdwkDV4COPAdp9wQwJASA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * category selection validator
 * only leaf nodes are valid selections
 */

namespace Ppb\Validate\Db;

use Cube\Validate\Db\RecordExists,
    Ppb\Db\Table\Categories as CategoriesTable;

class Category extends RecordExists
{

    protected $_message = "'%s': the value '%value%' is invalid.";

    /**
     *
     * class constructor
     *
     * add the table and the field to compare against
     *
     * @param array $data
     *              Supported keys:
     *              'table'   -> table - fully qualified namespace;
     *              'field'   -> table field
     *              'exclude' -> where clause or field/value pair to exclude from the query
     */
    public function __construct(array $data = null)
    {
        if (!array_key_exists('table', $data)) {
            $data['table'] = new CategoriesTable();
        }

        if (!array_key_exists('field', $data)) {
            $data['field'] = 'id';
        }

        parent::__construct($data);
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

            $tableName = $this->_table->getPrefix() . $this->_table->getName();

            $select = $this->_table->select()
                ->columns("(SELECT count(*)
                    FROM " . $adapter->quoteTableAs($tableName) . "
                    WHERE " . $adapter->quoteIdentifier('parent_id') . " = '" . strval($this->_value) . "') AS nb_rows")
                ->where($adapter->quoteIdentifier($this->_field) . ' = ?', strval($this->_value))
                ->having('nb_rows = 0');

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

