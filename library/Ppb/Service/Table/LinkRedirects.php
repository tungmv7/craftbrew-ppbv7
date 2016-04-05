<?php

/**
 *
 * PHP Pro Bid $Id$ yREXlQQAGkt+WsJYQ8DCFjLSZzyMmmEEd4TJMmFE2XA=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * link redirects table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\LinkRedirects as LinkRedirectsTable,
    Cube\Db\Table\AbstractTable;

class LinkRedirects extends AbstractServiceTable
{

    /**
     *
     * number of insert rows that appear at the bottom of a table form
     *
     * @var integer
     */
    protected $_insertRows = 3;

    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new LinkRedirectsTable());
    }

    /**
     *
     * get all table columns needed to generate the
     * link redirects management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => $this->_('Original Link'),
                'class'      => 'size-large',
                'element_id' => 'old_link',

            ),
            array(
                'label'      => $this->_('New Link'),
                'element_id' => 'new_link',
            ),
            array(
                'label'      => $this->_('Redirect Code'),
                'class'      => 'size-mini',
                'element_id' => 'redirect_code',
            ),
            array(
                'label'      => $this->_('Order ID'),
                'class'      => 'size-mini',
                'element_id' => 'order_id',
            ),
            array(
                'label'      => $this->_('Delete'),
                'class'      => 'size-mini',
                'element_id' => array(
                    'id', 'delete'
                ),
            ),
        );
    }

    /**
     *
     * get all form elements that are needed to generate the
     * link redirects management table in the admin area
     *
     * @return array
     */
    public function getElements()
    {
        return array(
            array(
                'id'      => 'id',
                'element' => 'hidden',
            ),
            array(
                'id'         => 'old_link',
                'element'    => 'text',
                'attributes' => array(
                    'class'       => 'form-control input-block-level',
                    'placeholder' => $this->_('String / regex format'),
                ),
            ),
            array(
                'id'         => 'new_link',
                'element'    => 'text',
                'attributes' => array(
                    'class'       => 'form-control input-medium',
                    'placeholder' => $this->_('String / sprintf format'),
                ),
            ),
            array(
                'id'         => 'redirect_code',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'         => 'order_id',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'id'      => 'delete',
                'element' => 'checkbox',
            ),
        );
    }

    /**
     *
     * save data in the table (update if an id exists or insert otherwise)
     *
     * TODO: PROBLEM WITH "max_input_vars" php setting for big arrays of data
     *
     * @param array $data
     *
     * @return \Ppb\Service\Table\AbstractServiceTable
     * @throws \InvalidArgumentException
     */
    public function save(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $columns = array_keys($data);

        $tableColumns = array_flip(array_values($this->getTable()->info(AbstractTable::COLS)));

        foreach ($data['id'] as $key => $value) {
            $row = $this->_table->fetchRow("id='{$value}'");

            $input = array();
            foreach ($columns as $column) {
                if (isset($data[$column][$key]) && array_key_exists($column, $tableColumns)) {
                    $input[$column] = $data[$column][$key];
                }
            }

            $input = $this->_prepareSaveData($input);

            if (count($row) > 0) {
                $this->_table->update($input, "id='{$value}'");
            }
            else if (count(array_filter($input)) > 0 && !empty($input[$this->getMainColumn($columns)])) {
                $this->_table->insert($input);
            }
        }

        return $this;
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
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($order === null) {
            $order = 'order_id ASC, id ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }
}

