<?php

/**
 *
 * Cube Framework $Id$ B7k0EV6Uavc4GfLKIHg3rUWsKkPHDg1XBpW3+Qh5XkY=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\Navigation;

use Cube\Config\AbstractConfig;

abstract class AbstractContainer implements \RecursiveIterator
{

    /**
     *
     * will hold the navigation array
     *
     * @var array
     */
    protected $_pages = array();

    /**
     *
     * get the pages container
     *
     * @return array
     */
    public function getPages()
    {
        return $this->_pages;
    }

    /**
     *
     * add new pages to the pages array, accepts an array or an object of type Config
     *
     * @param \Cube\Config\AbstractConfig|array $pages
     *
     * @return \Cube\Navigation\AbstractContainer
     * @throws \InvalidArgumentException
     */
    public function addPages($pages)
    {

        if (!is_array($pages) && !($pages instanceof AbstractConfig)) {
            throw new \InvalidArgumentException("The navigation object requires an array or an object of type \Cube\Config in order to be created.");
        }
        else {
            // need to test as i'm not sure it works as intended
            if ($pages instanceof AbstractConfig) {
                $pages = $pages->getData();
            }

            foreach ($pages as $page) {
                $this->addPage($page);
            }
        }

        return $this;
    }

    /**
     *
     * set pages array
     *
     * @param \Cube\Config\AbstractConfig|array $pages
     *
     * @return \Cube\Navigation\AbstractContainer
     */
    public function setPages($pages)
    {
        $this->removePages();

        $this->addPages($pages);

        return $this;
    }

    /**
     *
     * reset pages array
     *
     * @return \Cube\Navigation\AbstractContainer
     */
    public function removePages()
    {
        $this->_pages = array();

        return $this;
    }

    /**
     * Adds a page to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Page\AbstractPage::setParent()}.
     *
     * @param  Page\AbstractPage|array|\Traversable $page page to add
     *
     * @return AbstractContainer fluent interface, returns self
     * @throws \InvalidArgumentException if page is invalid
     */
    public function addPage($page)
    {
        if ($page === $this) {
            throw new \InvalidArgumentException('A page cannot have itself as a parent');
        }

        if (!$page instanceof Page\AbstractPage) {
            if (!is_array($page) && !$page instanceof \Traversable) {
                throw new \InvalidArgumentException(sprintf(
                    "'%s' must be an instance of Cube\Navigation\Page\AbstractPage, or an array", $page));
            }

            $page = Page\AbstractPage::factory($page);
        }

        if ($page) {
            $this->_pages[] = $page;

            $page->setParent($this);
        }

        return $this;
    }

    /**
     * get the first page matching a property
     *
     * @param string $property property name to search against
     * @param mixed  $value    property value
     * @param bool   $exact    whether to search for exact values only
     *
     * @return \Cube\Navigation\Page\AbstractPage|null
     */
    public function findOneBy($property, $value, $exact = true)
    {
        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($iterator as $page) {
            $pageName = $page->get($property);

            if ($exact === true) {
                if ($pageName == $value) {
                    return $page;
                }
            }
            else {
                if (stristr($pageName, $value)) {
                    return $page;
                }
            }
        }

        return null;
    }

    /**
     *
     * get all pages matching a set property
     *
     * @param string $property property name to search against
     * @param mixed  $value    property value
     * @param bool   $exact    whether to search for exact values only
     *
     * @return array
     */
    public function findAllBy($property, $value, $exact = true)
    {
        $result = array();

        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);

        /** @var \Cube\Navigation\Page\AbstractPage $page */
        foreach ($iterator as $page) {
            $pageName = $page->get($property);

            if ($exact === true) {
                if ($pageName == $value) {
                    $result[] = $page;
                }
            }
            else {
                if (stristr($pageName, $value)) {
                    $result[] = $page;
                }
            }
        }

        return $result;
    }

    /**
     *
     * checks if the current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return current($this->_pages) !== false;
    }

    /**
     *
     * checks if the current container has children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return (count($this->_pages) > 0) ? true : false;
    }

    public function next()
    {
        next($this->_pages);
    }

    /**
     *
     * return the current container
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_pages);
    }

    /**
     *
     * returns an iterator for the current element
     *
     * @return mixed
     */
    public function getChildren()
    {
        $key = key($this->_pages);

        if (isset($this->_pages[$key])) {
            return $this->_pages[$key];
        }

        return null;
    }

    public function rewind()
    {
        reset($this->_pages);
    }

    /**
     *
     * return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->_pages);
    }

}

