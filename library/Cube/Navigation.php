<?php

/**
 * 
 * Cube Framework $Id$ PFL7mdv7MVBIarBqdAE/L7FJWT6n8dBWvhnPdrbXuhc= 
 * 
 * @link        http://codecu.be/framework
 * @copyright   Copyright (c) 2014 CodeCube SRL
 * @license     http://codecu.be/framework/license Commercial License
 * 
 * @version     1.0
 */
/**
 * navigation menus generator class
 */

namespace Cube;

class Navigation extends Navigation\AbstractContainer
{

    /**
     *
     * the path for the navigation view file(s)
     * 
     * @var string
     */
    protected $_path;

    /**
     * 
     * class constructor
     * 
     * @param mixed $pages      the navigation data (array or object of type Cube\Config)
     */
    public function __construct($pages = null)
    {
        if ($pages !== null) {
            $this->addPages($pages);
        }
    }

    /**
     * 
     * get navigation partials path
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * 
     * set navigation partials path
     * 
     * @param string $path
     * @return \Cube\Navigation
     */
    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

}

