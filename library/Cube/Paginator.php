<?php

/**
 *
 * Cube Framework $Id$ mkts8vp2fWCve5z2otNVfwXO5XarvYTm8iJYs+0dQy0=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube;

use Cube\Controller\Front;

/**
 * paginator class
 *
 * the paginator will support foreach because it implements \IteratorAggregate
 * the method that will be called by a foreach function is $paginator->getIterator()
 *
 * Class Paginator
 *
 * @package Cube
 */
class Paginator implements \Countable, \IteratorAggregate
{

    const ITEM_COUNT_PER_PAGE = 20;
    const PAGE_RANGE = 5;
    const SCROLLING_STYLE = 'Sliding';

    /**
     *
     * paginator adapter
     * (will always be of type \Countable)
     *
     * @var \Cube\Paginator\Adapter\AdapterInterface
     */
    protected $_adapter;

    /**
     *
     * number of items in the current page
     *
     * @var integer
     */
    protected $_currentItemCount = null;

    /**
     *
     * current page items
     *
     * @var \Traversable
     */
    protected $_currentItems = null;

    /**
     * current page number
     * (default: 1)
     *
     * @var integer
     */
    protected $_currentPageNumber = 1;

    /**
     *
     * number of items per page
     *
     * @var integer
     */
    protected $_itemCountPerPage = null;

    /**
     * Number of pages
     *
     * @var integer
     */
    protected $_pageCount;

    /**
     *
     * number of page numbers to will be displayed
     *
     * @var integer
     */
    protected $_pageRange = null;

    /**
     *
     * paginator pages
     *
     * @var array
     */
    protected $_pages = null;

    /**
     *
     * scrolling style object
     *
     * @var \Cube\Paginator\ScrollingStyle\ScrollingStyleInterface
     */
    protected $_scrollingStyle = null;

    /**
     *
     * view object
     *
     * @var \Cube\View
     */
    protected $_view = null;

    /**
     *
     * class constructor
     *
     * @param \Cube\Paginator\Adapter\AdapterInterface $adapter
     */
    public function __construct(Paginator\Adapter\AdapterInterface $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     *
     * get pagination adapter
     *
     * @return \Cube\Paginator\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     *
     * set adapter
     *
     * @param \Cube\Paginator\Adapter\AdapterInterface $adapter
     *
     * @return \Cube\Paginator
     */
    public function setAdapter(Paginator\Adapter\AdapterInterface $adapter)
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     *
     * get the number of items for the current page
     *
     * @return integer
     */
    public function getCurrentItemCount()
    {
        if ($this->_currentItemCount === null) {
            $this->_currentItemCount = $this->getItemCount($this->getCurrentItems());
        }

        return $this->_currentItemCount;
    }

    /**
     *
     * get the items corresponding to the current page
     *
     * @return \Traversable
     */
    public function getCurrentItems()
    {
        if ($this->_currentItems === null) {
            $this->_currentItems = $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->_currentItems;
    }

    /**
     *
     * get the number of the current page
     *
     * @return integer
     */
    public function getCurrentPageNumber()
    {
        return $this->normalizePageNumber($this->_currentPageNumber);
    }

    /**
     *
     * set the current page number
     *
     * @param integer $currentPageNumber
     *
     * @return \Cube\Paginator
     */
    public function setCurrentPageNumber($currentPageNumber)
    {
        $this->_currentPageNumber = (integer)$currentPageNumber;
        $this->_currentItems = null;
        $this->_currentItemCount = null;

        return $this;
    }

    /**
     *
     * get the number of items per page
     *
     * @return integer
     */
    public function getItemCountPerPage()
    {
        if ($this->_itemCountPerPage === null) {
            $this->setItemCountPerPage(self::ITEM_COUNT_PER_PAGE);
        }

        return $this->_itemCountPerPage;
    }

    /**
     *
     * set number of items per page
     * (if the value is lower than 1, include all items in the current page)
     *
     * @param integer $itemCountPerPage
     *
     * @return \Cube\Paginator
     */
    public function setItemCountPerPage($itemCountPerPage)
    {
        $this->_itemCountPerPage = (integer)$itemCountPerPage;

        if ($this->_itemCountPerPage < 1) {
            $this->_itemCountPerPage = count($this->getAdapter());
        }
        $this->_pageCount = $this->_calculatePageCount();
        $this->_currentItems = null;
        $this->_currentItemCount = null;

        return $this;
    }

    /**
     *
     * get the number of pages to be displayed
     *
     * @return integer
     */
    public function getPageRange()
    {
        if ($this->_pageRange === null) {
            $this->setPageRange(self::PAGE_RANGE);
        }

        return $this->_pageRange;
    }

    /**
     *
     * set the number of pages to be displayed
     *
     * @param integer $pageRange
     *
     * @return \Cube\Paginator
     */
    public function setPageRange($pageRange)
    {
        $this->_pageRange = (integer)$pageRange;

        return $this;
    }

    /**
     *
     * get all pages from the paginator
     *
     * @return array
     */
    public function getPages()
    {
        if ($this->_pages === null) {
            $this->_pages = $this->_createPages();
        }

        return $this->_pages;
    }

    /**
     *
     * set pages
     *
     * @param array $pages
     *
     * @return $this
     */
    public function setPages($pages)
    {
        $this->_pages = $pages;

        return $this;
    }

    /**
     *
     * get scrolling style object
     *
     * @return \Cube\Paginator\ScrollingStyle\ScrollingStyleInterface
     */
    public function getScrollingStyle()
    {
        if ($this->_scrollingStyle === null) {
            $this->setScrollingStyle(self::SCROLLING_STYLE);
        }

        return $this->_scrollingStyle;
    }

    /**
     *
     * set paginator scrolling style
     *
     * @param mixed $scrollingStyle
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function setScrollingStyle($scrollingStyle)
    {
        if (!$scrollingStyle instanceof Paginator\ScrollingStyle\ScrollingStyleInterface) {
            $className = '\\Cube\\Paginator\\ScrollingStyle\\' . ucfirst($scrollingStyle);

            if (class_exists($className)) {
                $scrollingStyle = new $className();
            }
            else {
                throw new \RuntimeException("Invalid paginator scrolling type class/string given");
            }
        }

        $this->_scrollingStyle = $scrollingStyle;

        return $this;
    }

    /**
     *
     * get the view object
     *
     * @return \Cube\View
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->setView();
        }

        return $this->_view;
    }

    /**
     * set the view object
     *
     * @param \Cube\View $view
     *
     * @return \Cube\Form
     */
    public function setView(View $view = null)
    {
        if (!$view instanceof View) {
            $bootstrap = Front::getInstance()->getBootstrap();
            if ($bootstrap->hasResource('view')) {
                $view = $bootstrap->getResource('view');
            }
            else {
                $view = new View();
            }
        }

        $this->_view = $view;

        return $this;
    }

    /**
     *
     * get the number of pages
     *
     * @return integer
     */
    public function count()
    {
        if ($this->_pageCount === null) {
            $this->_pageCount = $this->_calculatePageCount();
        }

        return $this->_pageCount;
    }

    /**
     *
     * get iterator
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return $this->getCurrentItems();
    }

    /**
     *
     * get an item from a page. The current page is used when no page is specified
     *
     * @param  integer $itemNumber Item number (1 to itemCountPerPage)
     * @param  integer $pageNumber
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getItem($itemNumber, $pageNumber = null)
    {
        if ($pageNumber == null) {
            $pageNumber = $this->getCurrentPageNumber();
        }
        else if ($pageNumber < 0) {
            $pageNumber = ($this->count() + 1) + $pageNumber;
        }

        $page = $this->getItemsByPage($pageNumber);
        $itemCount = $this->getItemCount($page);

        if ($itemCount == 0) {
            throw new \InvalidArgumentException(
                sprintf("Page '%s' does not exist.", $pageNumber));
        }

        if ($itemNumber < 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            throw new \InvalidArgumentException(
                sprintf("Page '%2' does not contain item number '%s'", $pageNumber, $itemNumber));
        }

        return $page[$itemNumber - 1];
    }

    /**
     *
     * get the number of items in a collection
     *
     * @param  mixed $items
     *
     * @return integer
     */
    public function getItemCount($items)
    {
        if (is_array($items) || $items instanceof \Countable) {
            $itemCount = count($items);
        }
        else { // if we have \Traversable
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     *
     * get all page numbers in the requested range
     *
     * @param integer $min
     * @param integer $max
     *
     * @return array
     */
    public function getPagesInRange($min, $max)
    {
        $min = $this->normalizePageNumber($min);
        $max = $this->normalizePageNumber($max);

        $pages = array();

        for ($number = $min; $number <= $max; $number++) {
            $pages[$number] = $number;
        }

        return $pages;
    }

    /**
     * get the items corresponding to a given page number
     *
     * @param int $pageNumber
     *
     * @return \Traversable
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->_adapter->getItems($offset, $this->getItemCountPerPage());

        if (!$items instanceof \Traversable) {
            $items = new \ArrayIterator($items);
        }

        return $items;
    }

    /**
     *
     * set item number in respect to the current page
     *
     * @param  integer $itemNumber
     *
     * @return integer
     */
    public function normalizeItemNumber($itemNumber)
    {
        $itemNumber = (integer)$itemNumber;

        if ($itemNumber < 1) {
            $itemNumber = 1;
        }

        if ($itemNumber > $this->getItemCountPerPage()) {
            $itemNumber = $this->getItemCountPerPage();
        }

        return $itemNumber;
    }

    /**
     *
     * set page number in the range of the paginator
     *
     * @param  integer $pageNumber
     *
     * @return integer
     */
    public function normalizePageNumber($pageNumber)
    {
        $pageNumber = (integer)$pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->count();

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * calculates the page count
     *
     * @return integer
     */
    protected function _calculatePageCount()
    {
        return (integer)ceil($this->getAdapter()->count() / $this->getItemCountPerPage());
    }

    /**
     *
     * creates the pages based on the adapter and the scrolling style
     *
     * @return \stdClass
     */
    protected function _createPages()
    {
        $scrollingStyle = $this->getScrollingStyle();

        $pageCount = $this->count();
        $currentPageNumber = $this->getCurrentPageNumber();

        $pages = new \stdClass();
        $pages->pageCount = $pageCount;
        $pages->itemCountPerPage = $this->getItemCountPerPage();
        $pages->first = 1;
        $pages->current = $currentPageNumber;
        $pages->last = $pageCount;

        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $pages->previous = $currentPageNumber - 1;
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $pages->next = $currentPageNumber + 1;
        }

        // Pages in range
        $pages->pagesInRange = $scrollingStyle->getPages($this);
        $pages->firstPageInRange = min($pages->pagesInRange);
        $pages->lastPageInRange = max($pages->pagesInRange);

        // Item numbers
        if ($this->getCurrentItems() !== null) {
            $pages->currentItemCount = $this->getCurrentItemCount();
            $pages->itemCountPerPage = $this->getItemCountPerPage();
            $pages->totalItemCount = count($this->getAdapter());
            $pages->firstItemNumber = (($currentPageNumber - 1) * $this->getItemCountPerPage()) + 1;
            $pages->lastItemNumber = $pages->firstItemNumber + $pages->currentItemCount - 1;
        }

        return $pages;
    }

    /**
     *
     * renders the paginator
     *
     * @return string
     */
    public function render()
    {
        $view = $this->getView();

        return $view->pagination($this);
    }

    /**
     *
     * serialize the object as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}

