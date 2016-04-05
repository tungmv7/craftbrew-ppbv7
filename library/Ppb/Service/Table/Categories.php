<?php

/**
 *
 * PHP Pro Bid $Id$ BOlDQIqCKNAafbGqCKLCNrniNlkGfeHFKSJ2QAlYy+o=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * categories table service class
 */

namespace Ppb\Service\Table;

use Ppb\Db\Table\Categories as CategoriesTable,
        Cube\Navigation,
        Cube\Navigation\Page\AbstractPage;

class Categories extends AbstractServiceTable
{

    const NAME_SEPARATOR = ' :: ';

    /**
     *
     * categories table navigation object
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
                    new CategoriesTable());
    }

    /**
     *
     * get categories table data
     *
     * @return \Cube\Navigation
     */
    public function getData()
    {
        if (empty($this->_data)) {
            $this->setData();
        }

        return $this->_data;
    }

    /**
     *
     * set categories table data.
     * This data will be used for traversing the categories tree
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param array|\Traversable     $data  Optional. Custom categories data
     * @return \Ppb\Service\Table\Categories
     */
    public function setData($where = null, array $data = null)
    {
        if ($data === null) {
            if ($where === null) {
                $where = $this->_table->select('id, name, order_id, parent_id, custom_fees')
                        ->where("enable_auctions = ?", 1);
            }

            $categories = $this->_table->fetchAll($where);

            $data = array();
            foreach ($categories as $row) {
                $data[$row['parent_id']][] = array(
                    'id'         => (int)$row['id'],
                    'label'      => $row['name'],
                    'order'      => (int)$row['order_id'],
                    'customFees' => $row['custom_fees'],
                    'type'       => '\\Ppb\\Navigation\\Page\\Category',
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

    protected function _createTree(&$list, $parent)
    {
        $tree = array();

        foreach ($parent as $row) {
            if (isset($list[$row['id']])) {
                $row['pages'] = $this->_createTree($list, $list[$row['id']]);
            }

            $tree[] = $row;
        }

        return $tree;
    }

    /**
     *
     * get all table columns needed to generate the
     * categories management table in the admin area
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
                'label'      => 'Name',
                'element_id' => 'name',
                'popup'      => array(
                    'action' => 'category-options',
                ),
            ),
            array(
                'label'      => 'Custom Fees',
                'class'      => 'size-tiny',
                'element_id' => 'custom_fees',
                'parent_id'  => 0,
            ),
            array(
                'label'      => 'Order ID',
                'class'      => 'size-mini',
                'element_id' => 'order_id',
            ),
            array(
                'label'      => 'Delete',
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
     * categories management table in the admin area
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
                'id'           => 'custom_fees',
                'element'      => 'checkbox',
                'multiOptions' => array(
                    1 => null,
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
                'id'           => 'delete',
                'element'      => 'checkbox',
                'multiOptions' => array(
                    1 => null,
                ),
            ),
        );
    }

    /**
     *
     * get categories
     * to be used for the categories selector
     *
     * @param string|\Cube\Db\Select $where            SQL where clause, or a select object
     * @param string|array           $order
     * @param boolean                $defaultValue     whether to add a default value field in the data
     * @param boolean                $fullCategoryName display full category name
     * @return array
     */
    public function getMultiOptions($where = null, $order = null, $defaultValue = true, $fullCategoryName = false)
    {
        $data = array();

        if ($defaultValue === true) {
            $data[] = 'Default';
        }

        $rows = $this->_table->fetchAll($where, $order);

        foreach ($rows as $row) {
            $data[(int)$row['id']] = ($fullCategoryName === true) ?
                    $this->getFullCategoryName($row['id']) : $row['name'];
        }

        return $data;
    }

    /**
     *
     * fetches all matched rows
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param string|array           $order
     * @param int                    $count
     * @param int                    $offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if ($where === null) {
            $where = 'parent_id IS NULL';
        }
        if ($order === null) {
            $order = 'order_id ASC, name ASC';
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * save data in the table (update if an id exists or insert otherwise)
     *
     *
     * @param array $data
     * @return \Ppb\Service\Table\AbstractServiceTable
     * @throws \InvalidArgumentException
     */
    public function save(array $data)
    {
        if (isset($data['parent_id'])) {
            $parentId = (string)$data['parent_id'];
            unset($data['parent_id']);

            foreach ($data['id'] as $key => $value) {
                $data['parent_id'][$key] = $parentId;
            }
        }

        return parent::save($data);
    }

    /**
     *
     * get category breadcrumbs array
     *
     * @param int $id
     * @return array
     */
    public function getBreadcrumbs($id)
    {
        $breadcrumbs = array();
        $page = $this->getData()->findOneBy('id', $id);

        if ($page instanceof AbstractPage) {

            $breadcrumbs[(int)$page->id] = $page->label;

            while (($parent = $page->getParent()) instanceof AbstractPage) {
                $breadcrumbs[(int)$parent->id] = $parent->label;
                $page = $parent;
            }
        }

        return array_reverse($breadcrumbs, true);
    }

    /**
     *
     * get the main category object of a certain category id
     *
     * @param int $id
     * @return \Ppb\Navigation\Page\Category
     */
    public function getMainCategory($id)
    {

        $page = $this->getData()->findOneBy('id', $id);

        if ($page instanceof AbstractPage) {
            while (($parent = $page->getParent()) instanceof AbstractPage) {
                $page = $parent;
            }
        }

        return $page;
    }

    /**
     *
     * Returns a multidimensional array containing all categories which are in the same level as the selected cats,
     * all parents, plus a row with the children of the selected category
     *
     * @param int $id
     * @return array
     */
    public function getCategoriesSelectData($id)
    {
        $result = array();
        $page = null;

        if ($id) {
            $page = $this->getData()->findOneBy('id', $id);
        }

        if (!$page instanceof AbstractPage) {
            $page = $this->getData();
        }

        if ($page->hasChildren()) {
            $result[] = array(
                'selected' => null,
                'values'   => $this->_formatSelectorData(
                            $page->getPages()),
            );
        }

        while ($page instanceof AbstractPage) {
            $result[] = array(
                'selected' => $page->id,
                'values'   => $this->_formatSelectorData(
                            $page->getParent()->getPages()),
            );

            $page = $page->getParent();
        }

        return array_reverse($result);
    }

    /**
     *
     * get full category name, including parents
     *
     * @param integer $id
     * @return string|null
     */
    public function getFullCategoryName($id)
    {
        $name = implode(self::NAME_SEPARATOR, array_values($this->getBreadcrumbs($id)));

        return (!empty($name)) ? $name : null;
    }

    /**
     *
     * format category selector row data for display purposes
     *
     * @param array $container
     * @return string
     */
    protected function _formatSelectorData(array $container)
    {
        $values = array();

        foreach ($container as $page) {
            $values[(int)$page->id] = $page->label
                                      . (($page->hasChildren()) ? ' > ' : '');
        }

        return $values;
    }

}

