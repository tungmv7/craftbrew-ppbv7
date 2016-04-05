<?php

/**
 *
 * PHP Pro Bid $Id$ iZ6tTK+AdU8E9xlhFnTKkYujar4YsPCzOxJkSeG7joM=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * members module specific categories table service class
 */

namespace Ppb\Service\Table\Relational\Categories;

use Ppb\Service\Table\Relational\Categories;

class Members extends Categories
{

    /**
     *
     * fields to remove from the parent table form
     *
     * @var array
     */
    private $_skipFields = array('custom_fees');

    /**
     *
     * get all table columns needed to generate the
     * categories management table in the admin area
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = parent::getColumns();

        foreach ($columns as $key => $column) {
            if (in_array($column['element_id'], $this->_skipFields)) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }

}

