<?php

/**
 *
 * PHP Pro Bid $Id$ DiFLsX6dxxi9VqyWgpy/iUrUj1z7h5qRiwsqEth5maQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * custom fields table service class
 *
 * IMPORTANT:
 * search custom fields by multiple categories:
 * select * from custom_fields where category_ids REGEXP '"x"|"y"|"z"';
 */

namespace Ppb\Service;

use Ppb\Db\Table;

class CustomFields extends AbstractService
{

    /**
     *
     * allowed custom field types
     *
     * @var array
     */
    protected $_customFieldTypes = array(
        'user', 'item');

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(
            new Table\CustomFields());
    }

    /**
     *
     * get allowed custom field types
     *
     * @return array
     */
    public function getCustomFieldTypes()
    {
        return $this->_customFieldTypes;
    }

    /**
     *
     * save custom field object in the custom fields table
     *
     * @param array $data
     * @return \Ppb\Service\CustomFields
     */
    public function save($data)
    {

        $row = null;


        $data = $this->_prepareSaveData($data);

        if (array_key_exists('id', $data)) {
            $select = $this->_table->select()
                    ->where("id = ?", $data['id']);

            unset($data['id']);

            $row = $this->_table->fetchRow($select);
        }

        if (count($row) > 0) {
            $this->_table->update($data, "id='{$row['id']}'");
        }
        else {
            $this->_table->insert($data);
        }

        return $this;
    }

    /**
     *
     * delete a custom field from the table
     *
     * @param integer $id the id of the custom field
     * @return integer     returns the number of affected rows
     */
    public function delete($id)
    {
        $where = $this->_table->getAdapter()->quoteInto('id = ?', $id);

        return $this->_table->delete($where);
    }

    /**
     *
     * get certain custom fields based on a set of queries
     *
     * @param array $data  the search data used to return the requested fields
     * @param mixed $order order by field(s)
     * @return \Cube\Db\Table\Rowset\AbstractRowset
     */
    public function getFields(array $data = null, $order = null)
    {
        $select = $this->_table->select();

        foreach ((array)$data as $key => $value) {
            if ($key === 'category_ids') {
                $select->where("category_ids REGEXP '\"" . implode('"|"',
                        array_unique($value)) . "\"' OR category_ids = ''");
            }
            else {
                $select->where("{$key} = ?", $value);
            }
        }

        if ($order === null) {
            $order = array('active DESC', 'order_id ASC');
        }

        $select->order($order);

        return $this->fetchAll($select);
    }

    /**
     *
     * save custom fields settings (order etc)
     *
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function saveBrowseSettings(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("The form must use an element with the name 'id'.");
        }

        $columns = array_keys($data);

        foreach ((array)$data['id'] as $key => $value) {
            $row = $this->_table->fetchRow("id='{$value}'");

            $input = array();
            foreach ($columns as $column) {
                if (is_array($data[$column]) && isset($data[$column][$key])) {
                    $input[$column] = $data[$column][$key];
                }
            }

            $input = parent::_prepareSaveData($input);

            if (count($row) > 0) {
                $this->_table->update($input, "id='{$value}'");
            }
        }

        return $this;
    }
}

