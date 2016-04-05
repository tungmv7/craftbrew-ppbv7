<?php

/**
 *
 * PHP Pro Bid $Id$ BOlDQIqCKNAafbGqCKLCNrniNlkGfeHFKSJ2QAlYy+o=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */
/**
 * categories table service class
 */

namespace Ppb\Service\Table\Relational;

use Ppb\Db\Table\Categories as CategoriesTable,
    Cube\Navigation,
    Cube\Controller\Front,
    Cube\Db\Select,
    Cube\Db\Expr,
    Cube\View\Helper\Url as UrlViewHelper;

class Categories extends AbstractServiceTableRelational
{

    /**
     *
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setInsertRows(5)
            ->setTable(
                new CategoriesTable());
    }

    /**
     *
     * set categories table data.
     * This data will be used for traversing the categories tree
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @param array|\Traversable     $data  Optional. Custom categories data
     *
     * @return $this
     */
    public function setData($where = null, array $data = null)
    {
        if ($data === null) {
            if ($where === null) {
                $where = $this->_table->select()
                    ->where("enable_auctions = ?", 1);
            }

            $categories = $this->_table->fetchAll($where);
            $data = array();
            foreach ($categories as $row) {
                $data[$row['parent_id']][] = array(
                    'className'  => '\Ppb\Navigation\Page\Category',
                    'id'         => (int)$row['id'],
                    'label'      => $row['name'],
                    'order'      => (int)$row['order_id'],
                    'customFees' => $row['custom_fees'],
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
                'label'      => $this->_('Name'),
                'element_id' => 'name',
                'popup'      => array(
                    'action' => 'category-options',
                ),
                'owner'      => null,
            ),
            array(
                'label'      => $this->_('Custom Fees'),
                'class'      => 'size-tiny',
                'element_id' => 'custom_fees',
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
     * generate full names and slugs selected categories and save them into the table.
     * will be run as a separate function.
     *
     * @param int $parentId
     *
     * @return $this
     */
    public function saveSlugs($parentId = null)
    {
        $data = array();

        $select = $this->getTable()->select();

        if ($parentId > 0) {
            $select->where('parent_id = ?', $parentId);
        }
        else if ($parentId === null) {
            $select->where('parent_id is null');
        }
        else {
            // generate new slugs
            $select->where('slug = ""');
        }

        $categories = $this->fetchAll($select);

        $request = Front::getInstance()->getRequest();

        /** @var \Ppb\Db\Table\Row\Category $category */
        foreach ($categories as $category) {
            $fullName = $request->filterInput(
                $this->getFullName($category['id']));
            $slug = $this->sluggizeCategoryName(str_replace(parent::NAME_SEPARATOR, '_', $fullName), $category['id']);

            $data[] = array(
                'currentFullName' => $category['full_name'],
                'currentSlug'     => $category['slug'],
                'newFullName'     => $fullName,
                'newSlug'         => $slug,
            );


            $category->save(array(
                'full_name' => $fullName,
                'slug'      => $slug,
            ));
        }

        $table = $this->getTable();
        $adapter = $table->getAdapter();
        $tableName = $table->getPrefix() . $table->getName();

        foreach ($data as $row) {
            $statement = $adapter->query("UPDATE " . $tableName . "
                SET
                    `slug` = REPLACE (`slug`, '" . $row['currentSlug'] . "', '" . $row['newSlug'] . "'),
                    `full_name` = REPLACE (`full_name`, '" . $row['currentFullName'] . "', '" . $row['newFullName'] . "')
                ");
            $statement->execute();
        }

        return $this;
    }

    /**
     *
     * save data in the table (update if an id exists or insert otherwise)
     * also generate and save the slugs and full names of the inserted/modified categories
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

        parent::save($data);

        $this->saveSlugs(
            (!empty($data['parent_id'])) ? $data['parent_id'] : null);

        return $this;
    }

    /**
     *
     * return sluggized category name value
     * uses the cleanString method from the Url view helper
     *
     * @param string $categoryName
     * @param int    $categoryId
     *
     * @return string
     */
    public function sluggizeCategoryName($categoryName, $categoryId)
    {
        $duplicate = true;

        do {
            $categorySlug = UrlViewHelper::cleanString($categoryName);

            $select = $this->getTable()
                ->select(array('nb_rows' => new Expr('count(*)')))
                ->where('slug = ?', $categorySlug)
                ->where('id != ?', $categoryId);

            $stmt = $select->query();

            $counter = (integer)$stmt->fetchColumn('nb_rows');

            if ($counter > 0) {
                $categoryName .= '1';
            }
            else {
                $duplicate = false;
            }
        } while ($duplicate === true);

        return $categorySlug;
    }

    /**
     *
     * Returns a multidimensional array containing all categories which are in the same level as the selected cats,
     * all parents, plus a row with the children of the selected category
     *
     * @param int             $id
     * @param \Cube\Db\Select $where select object
     *
     * @return array
     */
    public function getCategoriesSelectData($id, Select $where)
    {
        $result = array();

        $breadcrumbs = array_keys($this->getBreadcrumbs($id));
        array_unshift($breadcrumbs, 0);

        foreach ($breadcrumbs as $key => $id) {
            /** @var \Ppb\Db\Table\Rowset\Categories $rowset */
            if ($id) {
                $where = $this->getTable()->select()
                    ->where('parent_id = ?', $id);
            }
            else {
                $where->where('parent_id is null');
            }

            $rowset = $this->fetchAll($where);

            $child = (!empty($breadcrumbs[$key + 1])) ? $breadcrumbs[$key + 1] : null;

            $values = $this->_formatSelectorData($rowset);

            if (count($values) > 0) {
                $result[] = array(
                    'selected' => $child,
                    'values'   => $values,
                );
            }

        }

        return $result;
    }

    /**
     *
     * reset the counter field for all categories
     *
     * @return $this
     */
    public function resetCounters()
    {
        $this->getTable()->update(array(
            'counter' => '',
        ), '');

        return $this;
    }

    /**
     *
     * format category selector row data for display purposes
     *
     * @param mixed $data
     *
     * @return array
     */
    protected function _formatSelectorData($data)
    {
        $values = array();

        $translate = $this->getTranslate();

        $select = $this->getTable()
            ->select(array('nb_rows' => new Expr('count(*)')));

        /** @var mixed $page */
        foreach ($data as $page) {
            $select->reset(Select::WHERE)
                ->where('parent_id = ?', $page['id']);

            $stmt = $select->query();

            $nbChildren = (integer)$stmt->fetchColumn('nb_rows');

            $values[$page['id']] = $translate->_($page['name'])
            . (($nbChildren > 0) ? ' > ' : '');
        }

        return $values;
    }

}

