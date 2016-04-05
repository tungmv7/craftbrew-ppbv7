<?php

/**
 *
 * PHP Pro Bid $Id$ ZB7xGbsLBEAGrVlLMBgUhklJB5iuHaC8qlAsWEwmAEE=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * content sections table service class
 */

namespace Ppb\Service\Table\Relational;

use Cube\Db\Select,
    Ppb\Db\Table\ContentSections as ContentSectionsTable,
    Cube\Navigation;

class ContentSections extends AbstractServiceTableRelational
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setInsertRows(3)
            ->setTable(
                new ContentSectionsTable());
    }

    /**
     *
     * set content sections table data.
     * This data will be used for traversing the content sections tree
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param array|\Traversable     $data  Optional. Custom categories data
     *
     * @return $this
     */
    public function setData($where = null, array $data = null)
    {
        if ($data === null) {
            $sections = $this->_table->fetchAll($where, array('parent_id ASC', 'order_id ASC'));

            $data = array();

            foreach ($sections as $row) {
                $data[$row['parent_id']][] = array(
                    'className'        => '\Ppb\Navigation\Page\ContentSection',
                    'id'               => $row['id'],
                    'label'            => $row['name'],
                    'slug'             => $row['slug'],
                    'meta_title'       => $row['meta_title'],
                    'meta_description' => $row['meta_description'],
                    'params'           => array(
                        'id' => $row['id']
                    ),
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
     * content sections management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = array(
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
                'popup'      => array(
                    'action' => 'content-section-options',
                ),

            ),
            array(
                'label'      => $this->_('Route'),
                'class'      => 'size-small',
                'element_id' => 'slug',
            ),
            array(
                'label'      => $this->_('Menu ID'),
                'class'      => 'size-mini',
                'element_id' => 'menu_id',
                'parent_id'  => 0,
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


        if ($this->_parentId) {
            foreach ($columns as $key => $column) {
                if (array_key_exists('parent_id', $column)) {
                    if ($column['parent_id'] != $this->_parentId) {
                        unset($columns[$key]);
                    }
                }
            }
        }

        return $columns;
    }

    /**
     *
     * get all form elements that are needed to generate the
     * content sections management table in the admin area
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
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'id'         => 'slug',
                'element'    => 'text',
                'attributes' => array(
                    'class' => 'form-control input-default',
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
                'id'         => 'menu_id',
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
     * get content sections multi options array
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
            $select = $this->_table->select();

            if ($where !== null) {
                if (is_array($where)) {
                    $select->where("id IN (?)", $where);
                }
                else {
                    $select->where("parent_id = ?", $where);
                }
            }

            $where = $select;
        }

        $data = parent::getMultiOptions($where, $order, $default, $fullName);

        asort($data);

        return $data;

    }

}

