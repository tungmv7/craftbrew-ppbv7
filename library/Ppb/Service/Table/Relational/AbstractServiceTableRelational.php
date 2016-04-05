<?php

/**
 *
 * PHP Pro Bid $Id$ izFniGuTYYgvSTMRkxOADFvrdtUdSmJmMzmNTBopws4f70X0uiOoODO6GkTPFRYDqIVXxA9paPZgsPph64lcNQ==
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.5
 */
/**
 * abstract service class for data tables that have a relational structure (one to many)
 */

namespace Ppb\Service\Table\Relational;

use Ppb\Service\Table\AbstractServiceTable;

abstract class AbstractServiceTableRelational extends AbstractServiceTable
{

    const NAME_SEPARATOR = ' :: ';

    /**
     *
     * locations table navigation object
     *
     * @var \Cube\Navigation
     */
    protected $_data;

    /**
     *
     * parent id field
     *
     * @var integer
     */
    protected $_parentId;

    /**
     *
     * column to check against when adding rows
     *
     * @var string
     */
    protected $_mainColumn = 'name';

    /**
     *
     * get parent id
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->_parentId;
    }

    /**
     *
     * set parent id
     *
     * @param integer $parentId
     *
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->_parentId = (int)$parentId;

        return $this;
    }

    /**
     *
     * get locations table data
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
     * save data in the table (update if an id exists or insert otherwise)
     *
     *
     * @param array $data
     *
     * @return \Ppb\Service\Table\AbstractServiceTable
     * @throws \InvalidArgumentException
     */
    public function save(array $data)
    {
        if (!empty($data['parent_id'])) {
            $parentId = (string)$data['parent_id'];
            unset($data['parent_id']);

            foreach ($data['id'] as $key => $value) {
                $data['parent_id'][$key] = $parentId;
            }
        }

        if (!empty($data['user_id'])) {
            $userId = (string)$data['user_id'];
            unset($data['user_id']);

            foreach ($data['id'] as $key => $value) {
                $data['user_id'][$key] = $userId;
            }
        }

        return parent::save($data);
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
     * @return \Cube\Db\Table\Rowset\AbstractRowset
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
     * get multi options array
     * translation sentences are inserted dynamically in the language array
     *
     * @param string|\Cube\Db\Select $where    SQL where clause, or a select object
     * @param string|array           $order    custom results ordering
     * @param bool|string            $default  whether to add a default value field in the data
     * @param bool                   $fullName display full branch name (with parents)
     *
     * @return array
     */
    public function getMultiOptions($where = null, $order = null, $default = false, $fullName = false)
    {
        $data = array();

        $translate = $this->getTranslate();

        if ($default !== false) {
            $data[] = $default;
        }

        $rows = $this->_table->fetchAll($where, $order);

        foreach ($rows as $row) {
            $categoryName = null;

            if ($fullName === true) {
                $parts = array();
                if (!empty($fullName)) {
                    $parts = explode(self::NAME_SEPARATOR, $row['full_name']);
                }

                foreach ((array) $parts as $key => $part) {
                    $parts[$key] = $translate->_($part);
                }

                $categoryName = implode(self::NAME_SEPARATOR, $parts);
            }

            if (empty($categoryName)) {
                $categoryName = $translate->_($row['name']);
            }

            $data[(int)$row['id']] = $categoryName;
        }

        return $data;
    }

    /**
     *
     * get breadcrumbs array
     * fetch directly from database
     * 7.2 - added a fix which was causing this method to loop indefinitely if a category that was to be counted didn't exist
     *
     * @param $id
     *
     * @return array
     */
    public function getBreadcrumbs($id)
    {
        $breadcrumbs = array();

        if ($id) {
            $translate = $this->getTranslate();

            do {
                $row = $this->findBy('id', $id);

                $id = 0;
                if (count($row) > 0) {
                    $breadcrumbs[$row['id']] = $translate->_($row['name']);
                    $id = $row['parent_id'];
                }
            } while ($id > 0);
        }

        return array_reverse($breadcrumbs, true);
    }

    /**
     *
     * get children array
     * fetch directly from database
     *
     * @param int  $id parent id
     * @param bool $root
     *
     * @return array
     */
    public function getChildren($id, $root = false)
    {
        $children = array();

        if ($id) {
            $translate = $this->getTranslate();
            $ids = array($id);

            if ($root) {
                $row = $this->findBy('id', $id);
                $children[$row['id']] = $translate->_($row['name']);
            }

            do {
                if ($ids) {
                    $select = $this->getTable()->select()
                        ->where('parent_id IN (?)', $ids);

                    $categories = $this->fetchAll($select);

                    $ids = array();
                    foreach ($categories as $row) {
                        $ids[] = $row['id'];
                        $children[$row['id']] = $translate->_($row['name']);
                    }
                }
            } while (count($ids) > 0);
        }

        return $children;
    }

    /**
     *
     * get the root object of a certain id
     *
     * @param $id
     *
     * @return \Cube\Db\Table\Row\AbstractRow
     */
    public function getRoot($id)
    {
        do {
            $row = $this->findBy('id', $id);
            $id = (!empty($row['parent_id'])) ? $row['parent_id'] : null;
        } while ($id !== null);

        return $row;
    }

    /**
     *
     * get full branch name, including parents
     *
     * @param int    $id
     * @param string $separator
     *
     * @return string|null
     */
    public function getFullName($id, $separator = null)
    {
        if ($separator === null) {
            $separator = self::NAME_SEPARATOR;
        }

        $name = implode($separator, array_values($this->getBreadcrumbs($id)));

        return (!empty($name)) ? $name : null;
    }

    /**
     *
     * the method will create a navigation tree
     *
     * @param $list
     * @param $parent
     *
     * @return array
     */
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
     * set table data.
     * This data will be used for traversing the navigation tree
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param array|\Traversable     $data  Optional. Custom categories data
     *
     * @return $this
     */
    abstract public function setData($where = null, array $data = null);

}
