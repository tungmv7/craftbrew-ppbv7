<?php

/**
 * 
 * Cube Framework $Id$ ubHVd0wc60f/ImbNGtCwktU6Ax3T4psxgNhl0KAZvTk= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * navigation page uri class
 */

namespace Cube\Navigation\Page;

use Cube\Controller\Front;

class Uri extends AbstractPage
{

    /**
     *
     * uri address
     * 
     * @var string
     */
    protected $_uri;

    /**
     * 
     * get uri
     * 
     * @return string
     */
    public function getParams()
    {
        return $this->_uri;
    }

    /**
     * 
     * get uri
     * 
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * 
     * set uri
     * 
     * @param string $uri
     * @return \Cube\Navigation\Page\Uri
     * @throws \InvalidArgumentException
     */
    public function setUri($uri)
    {
        if (!is_string($uri) && $uri !== null) {
            throw new \InvalidArgumentException(sprintf(
                            "'uri' must be a string or null, %s given.", gettype($uri)));
        }

        $this->_uri = $uri;

        return $this;
    }

    /**
     * 
     * check if a page is active, based on the request uri
     * 
     * @param bool $recursive    check in subpages as well, and if a subpage is active, return the current page as active
     * @return bool              returns active status
     */
    public function isActive($recursive = false)
    {
        if (!$this->_active) {
            $frontController = Front::getInstance();
            $request = $frontController->getRequest();
            
            if ($request->matchRequestUri($this->_uri, false)) {
                $this->_active = true;
                return true;
            }
            
        }

        return parent::isActive($recursive);
    }

}

