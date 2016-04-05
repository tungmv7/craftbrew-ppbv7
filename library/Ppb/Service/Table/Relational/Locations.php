<?php

/**
 *
 * PHP Pro Bid $Id$ gnZ+fYbyUp2Dt3nc5pWhtjkKScHsYYGu7yrmf7e5QFE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * locations table service class
 * TODO: add children like on the categories service
 */

namespace Ppb\Service\Table\Relational;

use Cube\Db\Select,
    Ppb\Db\Table\Locations as LocationsTable,
    Cube\Navigation;

class Locations extends AbstractServiceTableRelational
{

    /**
     *
     * locations table navigation object
     *
     * @var \Cube\Navigation
     */
    protected $_data;

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setInsertRows(3)
            ->setTable(
                new LocationsTable());
    }

    /**
     *
     * set locations table data.
     * This data will be used for traversing the locations tree
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param array|\Traversable     $data  Optional. Custom categories data
     *
     * @return $this
     */
    public function setData($where = null, array $data = null)
    {
        if ($data === null) {
            $locations = $this->_table->fetchAll($where);

            $data = array();
            foreach ($locations as $row) {
                $data[$row['parent_id']][] = array(
                    'className' => '\Ppb\Navigation\Page\Location',
                    'id'        => (int)$row['id'],
                    'label'     => $row['name'],
                    'order'     => (int)$row['order_id'],
                    'isoCode'   => $row['iso_code'],
                );
            }

            reset($data);

            $tree = $this->_createTree($data, current($data));

            $this->_data = new Navigation($tree);
        }
        else {
            $this->_data = $data;
        }

        return $this;
    }


    /**
     *
     * get all table columns needed to generate the
     * locations management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            array(
                'label'      => '',
                'class'      => 'size-tiny',
                'element_id' => null,
                'children'   => array(
                    'key'   => 'parent_id',
                    'value' => 'id',
                ),
            ),
            array(
                'label'      => $this->_('Name'),
                'element_id' => 'name',
            ),
            array(
                'label'      => $this->_('ISO Code'),
                'class'      => 'size-mini',
                'element_id' => 'iso_code',
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
     * locations management table in the admin area
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
                'id'         => 'name',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-large',
                ),
            ),
            array(
                'id'         => 'iso_code',
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
     * get locations multi options array
     *
     * @param string|array|\Cube\Db\Select $where    SQL where clause, a select object, or an array of ids
     * @param string|array                 $order
     * @param bool                         $default  whether to add a default value field in the data
     * @param bool                         $fullName display full branch name (with parents)
     *
     * @return array
     */
    public function getMultiOptions($where = null, $order = null, $default = false, $fullName = false)
    {
        if (!$where instanceof Select) {
            $select = $this->_table->select()
                ->order(array('order_id ASC', 'name ASC'));

            if ($where !== null) {
                if (is_array($where)) {
                    $select->where("id IN (?)", $where);
                }
                else {
                    $select->where("parent_id = ?", $where);
                }
            }
            else {
                $select->where('parent_id is null');
            }

            $where = $select;
        }

        return parent::getMultiOptions($where, $order, $default, $fullName);

    }

}

