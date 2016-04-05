<?php

/**
 *
 * Cube Framework $Id$ PvTj8FdA0U32zt5qoqeiv1u04WcaDR7wAxUw0G1lT30=
 *
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2015 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 *
 * @version     1.4
 */

namespace Cube\View\Helper;

use Cube\Paginator;

/**
 * pagination view helper
 *
 * Class Pagination
 *
 * @package Cube\View\Helper
 */
class Pagination extends AbstractHelper
{

    /**
     *
     * paginator object
     *
     * @var \Cube\Paginator
     */
    protected $_paginator;

    /**
     *
     * path where to search for navigation view partials
     *
     * @var string
     */
    protected $_path;

    /**
     *
     * get the paginator object
     *
     * @return \Cube\Paginator
     * @throws \InvalidArgumentException
     */
    public function getPaginator()
    {
        if (!($this->_paginator instanceof Paginator)) {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Paginator.", $this->_paginator));
        }

        return $this->_paginator;
    }

    /**
     *
     * set the paginator object
     *
     * @param \Cube\Paginator $paginator
     *
     * @return \Cube\View\Helper\Pagination
     * @throws \InvalidArgumentException
     */
    public function setPaginator($paginator)
    {
        if ($paginator instanceof Paginator) {
            $this->_paginator = $paginator;
        }
        else {
            throw new \InvalidArgumentException(
                sprintf("'%s' must be of type \Cube\Paginator.", $this->_paginator));
        }

        return $this;
    }

    /**
     *
     * get paginator partials path
     *
     * @return string
     */
    public function getPath()
    {
        if (empty($this->_path)) {
            $this->_path = $this->getView()->getViewsPath();
        }

        return $this->_path;
    }

    /**
     *
     * set paginator partials path
     *
     * @param string $path
     *
     * @return \Cube\View\Helper\Pagination
     * @throws \InvalidArgumentException
     */
    public function setPath($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(
                sprintf("The navigation files path (%s) does not exist.", $path));
        }

        $this->_path = $path;

        return $this;
    }

    /**
     *
     * renders pagination helper
     *
     * @param \Cube\Paginator $paginator
     * @param mixed           $scrollingStyle
     * @param string          $partial
     *
     * @return $this|string
     */
    public function pagination(Paginator $paginator = null, $scrollingStyle = null, $partial = null)
    {
        if ($paginator === null) {
            return $this;
        }
        else {
            $this->setPaginator($paginator);
            $this->setPartial($partial);
        }

        if ($scrollingStyle !== null) {
            $this->getPaginator()->setScrollingStyle($scrollingStyle);
        }

        $view = $this->getView();
        $view->paginator = $paginator;

        $view->setVariables(
            get_object_vars($paginator->getPages()));

        return $view->process(
            $this->getPartial(), true);
    }

}

