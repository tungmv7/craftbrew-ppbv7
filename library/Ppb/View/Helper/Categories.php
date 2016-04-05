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
 * categories service view helper class
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Ppb\Service\Table\Relational\Categories as CategoriesService;

class Categories extends AbstractHelper
{

    /**
     *
     * categories table service
     * 
     * @var \Ppb\Service\Table\Relational\Categories
     */
    protected $_categories;
    
    /**
     *
     * data resulted from a previous fetch operation
     * 
     * @var array
     */
    protected $_data = array();

    public function __construct()
    {
        $this->setCategories();
    }

    /**
     * 
     * get categories table service
     * 
     * @return \Ppb\Service\Table\Relational\Categories
     */
    public function getCategories()
    {
        return $this->_categories;
    }

    /**
     * 
     * set categories table service
     * 
     * @param \Ppb\Service\Table\Relational\Categories $categories
     * @return $this
     */
    public function setCategories(CategoriesService $categories = null)
    {
        if (!$categories instanceof CategoriesService) {
            $categories = new CategoriesService();
        }

        $this->_categories = $categories;

        return $this;
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * get all categories having a certain parent id
     *
     * @param string|\Cube\Db\Select $where SQL where clause, or a select object
     * @return array
     */
    public function categories($where = null)
    {
        if ($where === null) {
            return $this;
        }
        
        $this->_data = $this->getCategories()->fetchAll($where);
        
        return $this->_data;
    }

}

