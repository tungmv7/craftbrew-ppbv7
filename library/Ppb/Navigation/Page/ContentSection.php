<?php

/**
 *
 * PHP Pro Bid $Id$ bbkDUj+LKTo+DKydFNBa5NbNk1krnWr7efou9FC1Axw=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2014 Online Ventures Software LTD & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.0
 */
/**
 * content section page class - used by location navigation container
 */

namespace Ppb\Navigation\Page;

use Cube\Navigation\Page\AbstractPage,
    Cube\Controller\Front;

class ContentSection extends AbstractPage
{
    /**
     *
     * active section id
     *
     * @var int
     */
    protected $_sectionId;

    /**
     *
     * sluggable value
     *
     * @var string
     */
    protected $_slug;

    /**
     *
     * set active section id
     *
     * @param int $sectionId
     *
     * @return $this
     */
    public function setSectionId($sectionId)
    {
        $this->_sectionId = $sectionId;

        return $this;
    }

    /**
     *
     * get active section id
     *
     * @return int
     */
    public function getSectionId()
    {
        if (!$this->_sectionId) {
            $this->setSectionId(
                Front::getInstance()->getRequest()->getParam('id'));
        }

        return $this->_sectionId;
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
     * check if a section is active
     *
     * @param bool $recursive check in sub-sections as well, and if a sub-section is active, return the current page as active
     *
     * @return bool              returns active status
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            if ($this->getSectionId() == $this->_id) {
                $this->_active = true;

                return true;
            }
        }

        return parent::isActive($recursive);
    }
}

