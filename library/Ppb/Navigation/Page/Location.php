<?php

/**
 * 
 * PHP Pro Bid $Id$ k4g/dtFUQha9oLi/g9lIK1+rYg6koo3mWa73crQ17rU=
 * 
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 * 
 * @version     7.0
 */
/**
 * location page class - used by location navigation container
 */

namespace Ppb\Navigation\Page;

use Cube\Navigation\Page\AbstractPage;

class Location extends AbstractPage
{

    /**
     *
     * active location id
     * 
     * @var integer
     */
    protected $_locationId;

    /**
     *
     * iso code field
     *
     * @var string
     */
    protected $_isoCode;

    /**
     * 
     * get active location id
     * 
     * @return integer
     */
    public function getLocationId()
    {
        return $this->_locationId;
    }

    /**
     * 
     * set active location id
     * 
     * @param integer $locationId
     * @return \Ppb\Navigation\Page\Location
     */
    public function setLocationId($locationId)
    {
        $this->_locationId = (int) $locationId;

        return $this;
    }

    /**
     *
     * get iso code field
     *
     * @return bool
     */
    public function getIsoCode()
    {
        return $this->_isoCode;
    }

    /**
     *
     * set iso code field
     *
     * @param int $isoCode
     * @return \Ppb\Navigation\Page\Location
     */
    public function setIsoCode($isoCode)
    {
        $this->_isoCode = (bool) $isoCode;

        return $this;
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
            if ($this->_id == $this->_locationId) {
                $this->_active = true;
                return true;
            }
        }

        return parent::isActive($recursive);
    }

}

