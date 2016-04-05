<?php

/**
 * 
 * PHP Pro Bid $Id$ 32ivxrCmaKvWJeQ6YMcg0BGdwkDV4COPAdp9wQwJASA=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * category page class - used by category navigation container
 */

namespace Ppb\Navigation\Page;

use Cube\Navigation\Page\AbstractPage;

class Category extends AbstractPage
{

    /**
     *
     * active category id
     * 
     * @var integer
     */
    protected $_categoryId;

    /**
     *
     * custom fees flag
     * 
     * @var bool
     */
    protected $_customFees;

    /**
     *
     * sluggable value
     *
     * @var string
     */
    protected $_slug;

    /**
     * 
     * get active category id
     * 
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->_categoryId;
    }

    /**
     * 
     * set active category id
     * 
     * @param integer $categoryId
     * @return \Ppb\Navigation\Page\Category
     */
    public function setCategoryId($categoryId)
    {
        $this->_categoryId = (int) $categoryId;

        return $this;
    }

    /**
     * 
     * get custom fees flag
     * 
     * @return bool
     */
    public function getCustomFees()
    {
        return $this->_customFees;
    }

    /**
     * 
     * set custom fees flag
     * 
     * @param int $customFees
     * @return \Ppb\Navigation\Page\Category
     */
    public function setCustomFees($customFees)
    {
        $this->_customFees = (bool) $customFees;
        
        return $this;
    }

    /**
     *
     * set slug
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->_slug = $slug;

        return $this;
    }

    /**
     *
     * get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     *
     * override get method to use the slug if available for the url
     *
     * @param string $name
     * @return mixed|null|string
     */
    public function get($name)
    {
        if ($name == 'params' && !empty($this->_slug)) {
            return $this->getSlug();
        }

        return parent::get($name);
    }

    /**
     * 
     * check if a page is active, based on the page id
     * 
     * @param bool $recursive    check in sub-pages as well, and if a sub-page is active, return the current page as active
     * @return bool              returns active status
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            if ($this->_id == $this->_categoryId) {
                $this->_active = true;
                return true;
            }
        }

        return parent::isActive($recursive);
    }

}

